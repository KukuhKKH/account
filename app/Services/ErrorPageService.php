<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ErrorPageService
{

    private static array $errorConfigs = [
        403 => [
            'title'        => 'Akses Terbatas.',
            'icon'         => 'ShieldAlert',
            'animation'    => 'pulse-slow',
            'blobColor1'   => 'red',
            'blobColor2'   => 'rose',
            'gradientCSS'  => 'linear-gradient(135deg, rgb(239, 68, 68), rgb(236, 72, 153))',
            'titleColor'   => 'text-red-600 dark:text-red-400',
            'infoIcon'     => 'Lock',
            'infoBgColor'  => 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400',
            'infoBoxColor' => 'bg-slate-100 dark:bg-slate-800/50',
            'message'      => 'Maaf, akun Anda tidak memiliki izin untuk melihat halaman ini. Area ini memerlukan kredensial khusus.',
            'infoText'     => 'IP Anda telah dicatat untuk keperluan audit keamanan BangLipai Secure Portal.',
        ],
        404 => [
            'title'        => 'Halaman Hilang dari Radar.',
            'icon'         => 'FileSearch',
            'animation'    => 'bounce-slow',
            'blobColor1'   => 'blue',
            'blobColor2'   => 'indigo',
            'gradientCSS'  => 'linear-gradient(135deg, rgb(37, 99, 235), rgb(67, 56, 202))',
            'titleColor'   => 'text-blue-600 dark:text-blue-400',
            'infoIcon'     => 'Info',
            'infoBgColor'  => 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400',
            'infoBoxColor' => 'bg-slate-100 dark:bg-slate-800/50',
            'message'      => 'Maaf, jalur yang Anda tempuh buntu. Halaman ini mungkin telah dihapus, dipindahkan, atau memang tidak pernah ada sejak awal.',
            'infoText'     => 'Sistem BangLipai mencatat upaya akses ini. Silakan periksa kembali URL Anda.',
        ],
        419 => [
            'title'        => 'Sesi Berakhir.',
            'icon'         => 'Clock',
            'animation'    => 'pulse-slow',
            'blobColor1'   => 'yellow',
            'blobColor2'   => 'amber',
            'gradientCSS'  => 'linear-gradient(135deg, rgb(234, 179, 8), rgb(217, 119, 6))',
            'titleColor'   => 'text-yellow-600 dark:text-yellow-400',
            'infoIcon'     => 'AlertCircle',
            'infoBgColor'  => 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/40 dark:text-yellow-400',
            'infoBoxColor' => 'bg-yellow-50 dark:bg-yellow-900/20',
            'message'      => 'Sesi Anda telah berakhir karena tidak ada aktivitas dalam waktu yang ditentukan. Silakan login kembali untuk melanjutkan.',
            'infoText'     => 'Ini adalah fitur keamanan untuk melindungi akun Anda dari akses tidak sah.',
        ],
        500 => [
            'title'        => 'Sistem Sedang Lelah.',
            'icon'         => 'ServerCrash',
            'animation'    => 'pulse-slow',
            'blobColor1'   => 'orange',
            'blobColor2'   => 'amber',
            'gradientCSS'  => 'linear-gradient(135deg, rgb(249, 115, 22), rgb(217, 119, 6))',
            'titleColor'   => 'text-orange-600 dark:text-orange-400',
            'infoIcon'     => 'Activity',
            'infoBgColor'  => 'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400',
            'infoBoxColor' => 'bg-amber-50 dark:bg-amber-900/20',
            'message'      => 'Terjadi kesalahan internal pada server kami. Jangan khawatir, tim teknis BangLipai telah menerima laporan ini.',
            'infoText'     => 'Kami sedang berupaya memulihkan layanan secepat mungkin. Terima kasih atas kesabaran Anda.',
        ],
        503 => [
            'title'        => 'Layanan Tidak Tersedia.',
            'icon'         => 'AlertTriangle',
            'animation'    => 'pulse-slow',
            'blobColor1'   => 'purple',
            'blobColor2'   => 'violet',
            'gradientCSS'  => 'linear-gradient(135deg, rgb(168, 85, 247), rgb(124, 58, 248))',
            'titleColor'   => 'text-purple-600 dark:text-purple-400',
            'infoIcon'     => 'Wrench',
            'infoBgColor'  => 'bg-purple-100 text-purple-600 dark:bg-purple-900/40 dark:text-purple-400',
            'infoBoxColor' => 'bg-purple-50 dark:bg-purple-900/20',
            'message'      => 'Sistem sedang dalam pemeliharaan untuk peningkatan layanan. Kami akan kembali segera.',
            'infoText'     => 'Estimasi waktu pemeliharaan: beberapa jam ke depan. Kami menghargai kesabaran Anda.',
        ],
    ];

    public static function render(HttpException|int $error, Request $request, ?string $customMessage = null): JsonResponse|Response|null
    {
        if ($request->expectsJson()) {
            return null;
        }

        $statusCode = $error instanceof HttpException ? $error->getStatusCode() : $error;
        $message    = $customMessage ?? ($error instanceof HttpException ? $error->getMessage() : null);

        $config = self::$errorConfigs[$statusCode] ?? self::getDefaultConfig($statusCode);

        if (!$message) {
            $message = $config['message'];
        }

        return Inertia::render('Errors/Error', array_merge([
            'statusCode' => $statusCode,
            'message'    => $message,
            'debug'      => config('app.debug'),
        ], $config))->toResponse($request)->setStatusCode($statusCode);
    }

    private static function getDefaultConfig(int $statusCode): array
    {
        return [
            'title'        => "HTTP Error $statusCode",
            'icon'         => 'AlertTriangle',
            'animation'    => 'pulse-slow',
            'blobColor1'   => 'red',
            'blobColor2'   => 'rose',
            'gradientCSS'  => 'linear-gradient(135deg, rgb(239, 68, 68), rgb(236, 72, 153))',
            'titleColor'   => 'text-red-600 dark:text-red-400',
            'infoIcon'     => 'AlertTriangle',
            'infoBgColor'  => 'bg-red-100 text-red-600 dark:bg-red-900/40 dark:text-red-400',
            'infoBoxColor' => 'bg-slate-100 dark:bg-slate-800/50',
            'message'      => 'Terjadi kesalahan. Silakan coba lagi.',
            'infoText'     => 'Jika masalah ini berlanjut, silakan hubungi tim support kami.',
        ];
    }

    public static function registerConfig(int $statusCode, array $config): void
    {
        self::$errorConfigs[$statusCode] = $config;
    }
}
