<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class PwaController extends Controller
{
    /**
     * Return web app manifest JSON.
     */
    public function manifest()
    {
        $manifest = [
            'id'               => '/?source=pwa',
            'name'             => 'KPR Beta — Logsheet Teknisi',
            'short_name'       => 'Logsheet',
            'description'      => 'Aplikasi Input Logsheet Harian Listrik & Air KPR Beta untuk Teknisi',
            'start_url'        => '/logsheet/create',
            'scope'            => '/',
            'display'          => 'standalone',
            'orientation'      => 'portrait',
            'background_color' => '#0a0e1a',
            'theme_color'      => '#4f46e5',
            'categories'       => ['utilities', 'productivity', 'business'],
            'icons'            => [
                [
                    'src'     => '/icons/icon-192.png',
                    'sizes'   => '192x192',
                    'type'    => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src'     => '/icons/icon-192.png',
                    'sizes'   => '192x192',
                    'type'    => 'image/png',
                    'purpose' => 'maskable',
                ],
                [
                    'src'     => '/icons/icon-512.png',
                    'sizes'   => '512x512',
                    'type'    => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src'     => '/icons/icon-512.png',
                    'sizes'   => '512x512',
                    'type'    => 'image/png',
                    'purpose' => 'maskable',
                ],
                [
                    'src'     => '/icons/icon.svg',
                    'sizes'   => 'any',
                    'type'    => 'image/svg+xml',
                ],
            ],
            'shortcuts' => [
                [
                    'name'        => 'Input Logsheet Hari Ini',
                    'short_name'  => 'Input Harian',
                    'description' => 'Buka formulir input logsheet listrik dan air',
                    'url'         => '/logsheet/create',
                    'icons'       => [
                        [
                            'src'   => '/icons/icon-192.png',
                            'sizes' => '192x192',
                            'type'  => 'image/png',
                        ],
                    ],
                ],
                [
                    'name'        => 'Riwayat Logsheet',
                    'short_name'  => 'Riwayat',
                    'description' => 'Lihat rekap pencatatan logsheet',
                    'url'         => '/logsheet',
                    'icons'       => [
                        [
                            'src'   => '/icons/icon-192.png',
                            'sizes' => '192x192',
                            'type'  => 'image/png',
                        ],
                    ],
                ],
            ],
        ];

        return response()->json($manifest, 200, [
            'Content-Type' => 'application/manifest+json; charset=utf-8',
        ]);
    }

    /**
     * Stream or generate PNG icon on the fly.
     */
    public function icon(int $size)
    {
        $allowed = [192, 512];
        if (! in_array($size, $allowed, true)) {
            $size = 192;
        }

        $dir = public_path('icons');
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $filePath = $dir . DIRECTORY_SEPARATOR . "icon-{$size}.png";

        if (file_exists($filePath) && filesize($filePath) > 0) {
            return response()->file($filePath, [
                'Content-Type'  => 'image/png',
                'Cache-Control' => 'public, max-age=604800',
            ]);
        }

        // Generate icon using GD
        $img = imagecreatetruecolor($size, $size);
        imagealphablending($img, false);
        imagesavealpha($img, true);

        // Gradient / Dark navy background
        $bg = imagecolorallocate($img, 15, 23, 42); // #0f172a
        imagefilledrectangle($img, 0, 0, $size, $size, $bg);

        // Accent rounded inner circle / border
        $accent = imagecolorallocate($img, 99, 102, 241); // #6366f1
        $amber  = imagecolorallocate($img, 245, 158, 11);  // #f59e0b (electricity)
        $cyan   = imagecolorallocate($img, 6, 182, 212);   // #06b6d4 (water)
        $white  = imagecolorallocate($img, 255, 255, 255);

        // Outer circular ring
        imagesetthickness($img, (int)max(3, $size * 0.025));
        imagearc($img, (int)($size / 2), (int)($size / 2), (int)($size * 0.82), (int)($size * 0.82), 0, 360, $accent);

        // Draw lightning bolt polygon (scaled to size)
        $s = $size / 512;
        $boltPoints = [
            (int)(270 * $s), (int)(120 * $s),
            (int)(190 * $s), (int)(260 * $s),
            (int)(250 * $s), (int)(260 * $s),
            (int)(230 * $s), (int)(380 * $s),
            (int)(330 * $s), (int)(230 * $s),
            (int)(270 * $s), (int)(230 * $s),
        ];
        imagefilledpolygon($img, $boltPoints, $amber);

        // Water droplet circle
        imagefilledellipse($img, (int)(340 * $s), (int)(340 * $s), (int)(44 * $s), (int)(44 * $s), $cyan);

        // Small center hub
        imagefilledellipse($img, (int)(256 * $s), (int)(270 * $s), (int)(16 * $s), (int)(16 * $s), $white);

        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        imagedestroy($img);

        // Cache to disk
        @file_put_contents($filePath, $data);

        return response($data, 200, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    /**
     * Serve Service Worker script.
     */
    public function serviceWorker()
    {
        $swPath = public_path('sw.js');
        if (file_exists($swPath)) {
            return response(file_get_contents($swPath), 200, [
                'Content-Type'           => 'application/javascript; charset=utf-8',
                'Service-Worker-Allowed' => '/',
                'Cache-Control'          => 'no-cache, no-store, must-revalidate',
            ]);
        }

        return response('', 404);
    }

    /**
     * Offline fallback view.
     */
    public function offline()
    {
        return view('pwa.offline');
    }
}
