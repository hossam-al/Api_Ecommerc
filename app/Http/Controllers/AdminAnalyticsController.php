<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AdminAnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {
    }

    public function summary()
    {
        return $this->respond($this->analyticsService->summary());
    }

    public function usersAnalytics(Request $request)
    {
        return $this->respond($this->analyticsService->usersAnalytics($request));
    }

    public function productsAnalytics(Request $request)
    {
        return $this->respond($this->analyticsService->productsAnalytics($request));
    }

    public function ordersAnalytics(Request $request)
    {
        return $this->respond($this->analyticsService->ordersAnalytics($request));
    }

    public function categoriesAnalytics()
    {
        return $this->respond($this->analyticsService->categoriesAnalytics());
    }
}
