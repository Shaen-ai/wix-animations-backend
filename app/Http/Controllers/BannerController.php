<?php

namespace App\Http\Controllers;

use App\Services\BannerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function __construct(
        private BannerService $bannerService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $baseUrl = $request->getScheme() . '://' . $request->getHttpHost();
        $banners = $this->bannerService->getBanners($baseUrl);
        return response()->json($banners);
    }
}
