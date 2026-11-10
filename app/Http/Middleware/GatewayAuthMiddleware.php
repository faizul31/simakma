<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class GatewayAuthMiddleware
{
    /**
     * Handle an incoming request at API Gateway level.
     *
     * Validasi token Bearer Sanctum ke Auth Service (Port 8001).
     * Jika valid, sematkan identitas user ke atribut request agar diinjeksi
     * sebagai header X-User-* ke downstream microservice.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'status'  => 'error',
                'gateway' => 'API Gateway (Port 8000)',
                'message' => 'Akses ditolak. Token autentikasi (Bearer Token) tidak ditemukan.',
            ], 401);
        }

        $authUrl = env('SERVICE_AUTH_URL', 'http://localhost:8001');

        try {
            $authResponse = Http::timeout(5)
                ->withToken($token)
                ->acceptJson()
                ->get("{$authUrl}/api/me");

            if ($authResponse->status() !== 200) {
                return response()->json([
                    'status'  => 'error',
                    'gateway' => 'API Gateway (Port 8000)',
                    'message' => 'Token tidak valid atau sesi telah kedaluwarsa.',
                ], 401);
            }

            $userData = $authResponse->json('data.user');

            // Simpan identitas user ke attributes request
            $request->attributes->set('gateway_user', $userData);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'gateway' => 'API Gateway (Port 8000)',
                'message' => 'Gagal memvalidasi token ke Auth Service: ' . $e->getMessage(),
            ], 503);
        }

        return $next($request);
    }
}

