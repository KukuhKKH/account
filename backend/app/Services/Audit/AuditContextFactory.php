<?php

declare(strict_types=1);

namespace App\Services\Audit;

use Hypervel\Http\Request;

class AuditContextFactory
{
    /**
     * Default list of trusted CIDRs / IPs (Local loopback, Docker bridge, Private homelab subnets).
     *
     * @var array<int, string>
     */
    protected array $defaultTrustedProxies = [
        '127.0.0.1',
        '::1',
        '10.0.0.0/8',
        '172.16.0.0/12',
        '192.168.0.0/16',
    ];

    /**
     * Create an AuditContext instance from an incoming HTTP Request.
     */
    public function fromRequest(Request $request): AuditContext
    {
        $ipAddress = $this->resolveTrustedClientIp($request);
        $userAgent = $request->header('user-agent');

        return new AuditContext(
            ipAddress: is_string($ipAddress) && trim($ipAddress) !== '' ? trim($ipAddress) : null,
            userAgent: is_string($userAgent) && trim($userAgent) !== '' ? trim($userAgent) : null,
        );
    }

    /**
     * Resolve the authentic client IP address, verifying trusted proxy boundaries.
     */
    protected function resolveTrustedClientIp(Request $request): ?string
    {
        $remoteAddr = $request->server('remote_addr');

        if (! is_string($remoteAddr) || trim($remoteAddr) === '') {
            return $request->ip();
        }

        $remoteAddr = trim($remoteAddr);

        // If the immediate connecting address is NOT a trusted proxy, do not trust forwarded headers
        if (! $this->isTrustedProxy($remoteAddr)) {
            return $remoteAddr;
        }

        // Connecting node is trusted (e.g., Traefik, Docker gateway, local reverse proxy)
        // Check X-Real-IP first
        $realIp = $request->header('x-real-ip');

        if (is_string($realIp) && trim($realIp) !== '' && filter_var(trim($realIp), FILTER_VALIDATE_IP)) {
            return trim($realIp);
        }

        // Check X-Forwarded-For chain
        $forwardedFor = $request->header('x-forwarded-for');

        if (is_string($forwardedFor) && trim($forwardedFor) !== '') {
            $ips = array_map('trim', explode(',', $forwardedFor));

            // Return first non-empty valid IP in the forwarded chain
            foreach ($ips as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $remoteAddr;
    }

    /**
     * Determine if a given IP address belongs to trusted proxies.
     */
    protected function isTrustedProxy(string $ip): bool
    {
        /** @var array<int, string> $configuredProxies */
        $configuredProxies = config('app.trusted_proxies', $this->defaultTrustedProxies);

        foreach ($configuredProxies as $trusted) {
            if ($trusted === '*' || $trusted === $ip) {
                return true;
            }

            if (str_contains($trusted, '/') && $this->ipInCidr($ip, $trusted)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an IPv4 address is within a CIDR range.
     */
    protected function ipInCidr(string $ip, string $cidr): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        [$subnet, $bits] = explode('/', $cidr, 2);

        if (! filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        $ipLong     = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $mask       = -1 << (32 - (int) $bits);
        $subnetMask = $subnetLong & $mask;

        return ($ipLong & $mask) === $subnetMask;
    }
}
