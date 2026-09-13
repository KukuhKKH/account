<?php

declare(strict_types=1);

namespace Tests\Unit\User\Services;

use App\Services\Audit\AuditContext;
use App\Services\Audit\AuditContextFactory;
use Hypervel\Http\Request;
use Mockery;
use Tests\TestCase;

/**
 * @internal
 */
class AuditContextFactoryTest extends TestCase
{
    protected AuditContextFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new AuditContextFactory();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testUntrustedRemoteAddressIgnoresForwardedHeaders(): void
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('server')->with('remote_addr')->andReturn('203.0.113.195'); // Public untrusted IP
        $request->shouldReceive('header')->with('user-agent')->andReturn('Mozilla/5.0');
        // Because remote_addr is not in trusted CIDR, it should NOT trust x-forwarded-for or x-real-ip
        $context = $this->factory->fromRequest($request);

        $this->assertSame('203.0.113.195', $context->ipAddress);
        $this->assertSame('Mozilla/5.0', $context->userAgent);
    }

    public function testTrustedProxyWithRealIpHeader(): void
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('server')->with('remote_addr')->andReturn('127.0.0.1'); // Trusted loopback
        $request->shouldReceive('header')->with('x-real-ip')->andReturn('103.10.55.2');
        $request->shouldReceive('header')->with('user-agent')->andReturn('CurlClient');

        $context = $this->factory->fromRequest($request);

        $this->assertSame('103.10.55.2', $context->ipAddress);
        $this->assertSame('CurlClient', $context->userAgent);
    }

    public function testTrustedProxyWithForwardedForChain(): void
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('server')->with('remote_addr')->andReturn('172.20.0.5'); // In 172.16.0.0/12 trusted range
        $request->shouldReceive('header')->with('x-real-ip')->andReturn(null);
        $request->shouldReceive('header')->with('x-forwarded-for')->andReturn(' 114.122.4.99 , 172.20.0.2 ');
        $request->shouldReceive('header')->with('user-agent')->andReturn('Firefox');

        $context = $this->factory->fromRequest($request);

        $this->assertSame('114.122.4.99', $context->ipAddress);
        $this->assertSame('Firefox', $context->userAgent);
    }

    public function testEmptyRemoteAddressFallsBackToRequestIp(): void
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('server')->with('remote_addr')->andReturn(null);
        $request->shouldReceive('ip')->andReturn('10.10.10.99');
        $request->shouldReceive('header')->with('user-agent')->andReturn('   ');

        $context = $this->factory->fromRequest($request);

        $this->assertSame('10.10.10.99', $context->ipAddress);
        $this->assertNull($context->userAgent);
    }

    public function testAuditContextDataClass(): void
    {
        $context = new AuditContext('1.2.3.4', 'CustomAgent');
        $this->assertSame('1.2.3.4', $context->ipAddress);
        $this->assertSame('CustomAgent', $context->userAgent);
    }
}
