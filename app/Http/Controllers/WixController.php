<?php

namespace App\Http\Controllers;

use App\Models\WixToken;
use App\Models\WixWebhook;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WixController extends Controller
{
    /**
     * Handle Wix app lifecycle webhooks for Site Animation.
     * Validates JWT, logs events, and processes app lifecycle.
     */
    public function handleWixWebhooks(Request $request)
    {
        $body      = file_get_contents('php://input');
        $publicKey = config('services.wix.public_key');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return response()->json(['error' => 'Method not allowed'], 405);
        }

        if (empty($publicKey)) {
            \Log::error('Wix webhook: WIX_PUBLIC_KEY is not configured');
            return response()->json(['error' => 'Server misconfiguration'], 500);
        }

        try {
            $decoded   = JWT::decode($body, new Key($publicKey, 'RS256'));
            $event     = json_decode($decoded->data);
            $eventData = json_decode($event->data ?? '{}');
            $identity  = json_decode($event->identity ?? '{}');
        } catch (Exception $e) {
            \Log::warning('Wix webhook JWT decode failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['type' => 'error', 'message' => $e->getMessage()], 400);
        }

        $instanceId = $event->instanceId ?? null;
        if (!$instanceId) {
            \Log::warning('Wix webhook: missing instanceId', ['event' => $event]);
            return response()->json(['error' => 'Missing instanceId'], 400);
        }

        try {
            $this->logWebhook($event, $eventData, $identity);

            switch ($event->eventType) {
                case 'AppInstalled':
                    $this->handleAppInstalled($instanceId, $eventData);
                    break;

                case 'AppRemoved':
                    $this->handleAppRemoved($instanceId);
                    break;

                case 'PaidPlanPurchased':
                case 'PaidPlanChanged':
                    $this->handlePlanUpgrade($instanceId);
                    break;

                case 'PaidPlanAutoRenewalCancelled':
                    $this->handlePlanDowngrade($instanceId);
                    break;

                case 'SitePropertiesUpdated':
                    break;
            }
        } catch (Exception $e) {
            \Log::error('Wix webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'instanceId' => $instanceId ?? null,
            ]);
            return response()->json(['error' => 'Processing failed'], 500);
        }

        return response('', 200);
    }

    private function logWebhook($event, $eventData, $identity): void
    {
        $webhookData = [
            'type'     => 'Site Animation ' . ($event->eventType ?? 'Unknown'),
            'instance' => $event->instanceId ?? null,
            'content'  => ['identity' => $identity, 'data' => $eventData],
        ];

        if (isset($identity->wixUserId)) {
            $webhookData['user_id'] = $identity->wixUserId;
        }

        if (in_array($event->eventType ?? '', ['AppInstalled', 'SitePropertiesUpdated'])
            && isset($eventData->originInstanceId)) {
            $webhookData['origin_instance'] = $eventData->originInstanceId;
        }

        WixWebhook::create($webhookData);
    }

    private function handleAppInstalled(string $instanceId, $eventData): void
    {
        // Extend with tenant/site provisioning if needed
    }

    private function handleAppRemoved(string $instanceId): void
    {
        // Extend with cleanup if needed
    }

    private function handlePlanUpgrade(string $instanceId): void
    {
        // Extend with plan upgrade logic if needed
    }

    private function handlePlanDowngrade(string $instanceId): void
    {
        // Extend with plan downgrade logic if needed
    }

    // ==================== SITE NOTICE BANNER INTEGRATION ====================

    public function siteNoticeBannerRedirect(Request $request): RedirectResponse|Response
    {
        $token = $request->query('token');

        if ($token) {
            $url = 'https://www.wix.com/installer/install';
            $params = [
                'token' => $token,
                'appId' => config('services.site_notice_banner.app_id'),
                'redirectUrl' => 'https://wixanimationsapi.nextechspires.com/api/sitenoticebannerrd',
                'state' => 'addAppActionProcess',
            ];

            return redirect()->away($url . '?' . http_build_query($params));
        }

        return response('Token was not found', 400);
    }

    public function accessToSiteNoticeBanner(Request $request): RedirectResponse|Response
    {
        $state = $request->query('state');

        if ($state === 'addAppActionProcess') {
            return $this->handleSiteNoticeBannerAppActionProcess($request);
        }

        return $this->handleSiteNoticeBannerDefaultState($request);
    }

    protected function handleSiteNoticeBannerAppActionProcess(Request $request): RedirectResponse|Response
    {
        $instanceId = $request->query('instanceId') ?? $request->query('instance_id');
        $code = $request->query('code');

        // If instanceId missing, try to extract from the OAuth code JWT payload
        if (!$instanceId && $code) {
            $instanceId = $this->extractInstanceIdFromOAuthCode($code);
        }

        if (!$instanceId || !$code) {
            return response('InstanceId or authorization code was not found', 400);
        }

        $response = $this->getSiteNoticeBannerAccessToken($code);
        if ($response && isset($response['access_token'])) {
            $siteData = $this->getAppsData($response['access_token']);
            $fields = [
                'access_token' => $response['access_token'],
                'acc_expires_at' => now(),
                'refresh_token' => $response['refresh_token'],
                'ref_expires_at' => now(),
                'info' => $siteData,
                'app' => 'sitenoticebanner',
            ];

            WixToken::updateOrCreate(
                ['instance' => $instanceId, 'app' => 'sitenoticebanner'],
                $fields
            );

            return redirect()->away('https://www.wix.com/_api/site-apps/v1/site-apps/token-received');
        }

        Log::warning('Site Notice Banner token exchange failed', [
            'response' => $response ?? [],
            'instanceId' => $instanceId,
        ]);

        $errorDetail = is_array($response) ? ($response['error_description'] ?? $response['message'] ?? $response['error'] ?? null) : null;

        return response(
            'Failed to exchange authorization code for tokens.' . ($errorDetail ? " {$errorDetail}" : ' Please try again.'),
            400
        );
    }

    /**
     * Extract instanceId from Wix OAuth code JWT (format: OAUTH2.<jwt>).
     */
    protected function extractInstanceIdFromOAuthCode(string $code): ?string
    {
        $jwt = str_starts_with($code, 'OAUTH2.') ? substr($code, 7) : $code;
        $parts = explode('.', $jwt);
        if (count($parts) < 2) {
            return null;
        }
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        if (!$payload) {
            return null;
        }
        $instanceId = $payload['instanceId'] ?? null;
        if (!$instanceId && isset($payload['data'])) {
            $data = is_string($payload['data']) ? json_decode($payload['data'], true) : $payload['data'];
            $instanceId = $data['instanceId'] ?? null;
        }

        return $instanceId;
    }

    protected function handleSiteNoticeBannerDefaultState(Request $request): Response
    {
        $code = $request->query('code');
        $state = $request->query('state');

        if ($code && $state) {
            $response = $this->getSiteNoticeBannerAccessToken($code);
            if ($response && isset($response['access_token'])) {
                $instanceLlong = explode(',', $state)[0];
                $parts = explode('.', $instanceLlong);
                $data = $parts[1] ?? '';
                $json = json_decode(base64_decode($data), true);

                if ($json) {
                    $instanceId = $json['instanceId'] ?? null;
                    if ($instanceId) {
                        $siteData = $this->getAppsData($response['access_token']);
                        WixToken::updateOrCreate(
                            ['instance' => $instanceId, 'app' => 'sitenoticebanner'],
                            [
                                'access_token' => $response['access_token'],
                                'acc_expires_at' => now(),
                                'refresh_token' => $response['refresh_token'],
                                'ref_expires_at' => now(),
                                'info' => $siteData,
                            ]
                        );
                        return response(
                            "<script>localStorage.setItem('seen', 'true'); window.close();</script>",
                            200
                        )->header('Content-Type', 'text/html');
                    }
                }
            }
        }

        return response('Authorization code or state was not found', 400);
    }

    protected function getSiteNoticeBannerAccessToken(string $code): ?array
    {
        $url = 'https://www.wixapis.com/oauth/access';

        // Use credentials that match the app which issued the code
        $codeAppId = $this->extractAppIdFromOAuthCode($code);
        $wixAppId = config('services.wix.app_id');
        $wixAppSecret = config('services.wix.app_secret');
        $bannerAppId = config('services.site_notice_banner.app_id');
        $bannerAppSecret = config('services.site_notice_banner.app_secret');

        if ($codeAppId === $wixAppId && !empty($wixAppSecret)) {
            $appId = $wixAppId;
            $appSecret = $wixAppSecret;
        } elseif ($codeAppId === $bannerAppId && !empty($bannerAppSecret)) {
            $appId = $bannerAppId;
            $appSecret = $bannerAppSecret;
        } elseif (!empty($bannerAppSecret)) {
            $appId = $bannerAppId;
            $appSecret = $bannerAppSecret;
        } else {
            $appId = $wixAppId;
            $appSecret = $wixAppSecret;
        }

        $data = [
            'grant_type' => 'authorization_code',
            'client_id' => $appId,
            'client_secret' => $appSecret,
            'code' => $code,
        ];

        $response = Http::post($url, $data);

        if (!$response->successful()) {
            Log::warning('Wix OAuth token exchange failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }

        return $response->json();
    }

    /**
     * Extract appId from Wix OAuth code JWT payload.
     */
    protected function extractAppIdFromOAuthCode(string $code): ?string
    {
        $jwt = str_starts_with($code, 'OAUTH2.') ? substr($code, 7) : $code;
        $parts = explode('.', $jwt);
        if (count($parts) < 2) {
            return null;
        }
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        if (!$payload) {
            return null;
        }
        $appId = $payload['appId'] ?? null;
        if (!$appId && isset($payload['data'])) {
            $data = is_string($payload['data']) ? json_decode($payload['data'], true) : $payload['data'];
            $appId = $data['appId'] ?? null;
        }

        return $appId;
    }

    protected function getAppsData(string $token): ?array
    {
        $response = Http::withHeaders(['Authorization' => $token])
            ->get('https://www.wixapis.com/apps/v1/instance');

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['site']) && isset($data['site']['installedWixApps'])) {
                return $data;
            }
        }

        return null;
    }

    /**
     * Fetch site URL from Wix instance API.
     * Instance is passed as query param when dashboard is opened from Wix.
     * GET https://www.wixapis.com/apps/v1/instance with Authorization: <instance>
     */
    public function getSiteUrl(Request $request): JsonResponse
    {
        $instance = $request->query('instance');

        if (!$instance) {
            return response()->json(['error' => 'Missing instance query param'], 400);
        }

        $response = Http::withHeaders(['Authorization' => $instance])
            ->get('https://www.wixapis.com/apps/v1/instance');

        if (!$response->successful()) {
            Log::warning('Wix instance API failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return response()->json(
                ['error' => 'Failed to fetch site info from Wix'],
                $response->status()
            );
        }

        $data = $response->json();
        $siteUrl = $data['site']['url'] ?? null;

        if (!$siteUrl || !is_string($siteUrl)) {
            return response()->json(['error' => 'Site URL not available (site may be unpublished)'], 404);
        }

        return response()->json(['siteUrl' => $siteUrl]);
    }
}
