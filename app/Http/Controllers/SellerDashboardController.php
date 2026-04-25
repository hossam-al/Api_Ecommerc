<?php

namespace App\Http\Controllers;

use App\Services\SellerDashboardService;
use Illuminate\Http\Request;

class SellerDashboardController extends Controller
{
    public function __construct(
        protected SellerDashboardService $sellerDashboardService
    ) {
    }

    public function home(Request $request)
    {
        return $this->respond($this->sellerDashboardService->home($request->user()));
    }

    public function accountStatus(Request $request)
    {
        return $this->respond($this->sellerDashboardService->accountStatus($request->user()));
    }
}
