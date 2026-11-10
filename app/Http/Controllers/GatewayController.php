<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;

class GatewayController extends Controller
{
    /**
     * Pemetaan URL dasar microservices independen.
     */
    protected array $serviceMap;

    public function __construct()
    {
        $this->serviceMap = [
            'auth'        => env('SERVICE_AUTH_URL', 'http://localhost:8001'),
            'kegiatan'    => env('SERVICE_KEGIATAN_URL', 'http://localhost:8002'),
            'pendaftaran' => env('SERVICE_PENDAFTARAN_URL', 'http://localhost:8003'),
            'prestasi'    => env('SERVICE_PRESTASI_URL', 'http://localhost:8004'),
        ];
    }

    /**
     * Reverse Proxy Handler:
     * Meneruskan request dari client ke target microservice yang sesuai.
     *
     * @param Request $request
     * @param string  $service (auth|kegiatan|pendaftaran|prestasi)
     * @param string  $endpoint
     * @return JsonResponse|Response
     */
    public function proxy(Request $request, string $service, string $endpoint = '')
    {
        if (! isset($this->serviceMap[$service])) {
            return response()->json([
                'status'  => 'error',
                'message' => "Microservice target [{$service}] tidak terdaftar di API Gateway.",
            ], 404);
        }

        $baseUrl = rtrim($this->serviceMap[$service], '/');
        $endpointPath = ltrim($endpoint, '/');
        $targetUrl = "{$baseUrl}/api/{$endpointPath}";

        // Teruskan header penting ke downstream service
        $headers = [
            'Accept'       => 'application/json',
            'X-Gateway'    => 'Campus-API-Gateway',
            'X-Client-Ip'  => $request->ip(),
        ];

        if ($request->hasHeader('Authorization')) {
            $headers['Authorization'] = $request->header('Authorization');
        }

        // Injeksi header konteks user jika telah divalidasi oleh Gateway Middleware
        if ($request->attributes->has('gateway_user')) {
            $user = $request->attributes->get('gateway_user');
            $headers['X-User-Id']   = (string) ($user['id'] ?? '');
            $headers['X-User-Role'] = (string) ($user['role'] ?? '');
            $headers['X-User-Nim']  = (string) ($user['nim_nidn'] ?? '');
        }

        $method = strtolower($request->method());

        try {
            $httpRequest = Http::timeout(10)->withHeaders($headers);

            // Penanganan request berjenis Multipart (upload file)
            if ($request->isMethod('post') && count($request->allFiles()) > 0) {
                foreach ($request->allFiles() as $name => $file) {
                    $httpRequest->attach(
                        $name,
                        file_get_contents($file->getRealPath()),
                        $file->getClientOriginalName()
                    );
                }

                // Sertakan non-file fields
                foreach ($request->except(array_keys($request->allFiles())) as $key => $value) {
                    $httpRequest->attach($key, (string) $value);
                }

                $response = $httpRequest->post($targetUrl);
            } else {
                // Penanganan request standar (GET, POST, PUT, DELETE)
                $response = match ($method) {
                    'get'     => $httpRequest->get($targetUrl, $request->query()),
                    'post'    => $httpRequest->post($targetUrl, $request->all()),
                    'put'     => $httpRequest->put($targetUrl, $request->all()),
                    'delete'  => $httpRequest->delete($targetUrl, $request->all()),
                    'patch'   => $httpRequest->patch($targetUrl, $request->all()),
                    default   => $httpRequest->send($method, $targetUrl, ['body' => $request->getContent()]),
                };
            }

            // Kembalikan response asli dari target service ke client
            return response()->json($response->json(), $response->status());

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'status'     => 'error',
                'gateway'    => 'API Gateway (Port 8000)',
                'message'    => "Layanan [{$service}] pada {$targetUrl} tidak dapat dihubungi atau sedang offline.",
                'target_url' => $targetUrl,
            ], 503);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'gateway' => 'API Gateway (Port 8000)',
                'message' => 'Terjadi kesalahan saat meneruskan request: ' . $e->getMessage(),
            ], 500);
        }
    }
}

