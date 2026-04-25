<?php

namespace App\Http\Controllers;

use App\Http\Requests\Coupons\StoreCouponRequest;
use App\Http\Requests\Coupons\UpdateCouponRequest;
use App\Http\Requests\Coupons\ValidateCouponRequest;
use App\Services\CouponService;

class CouponsController extends Controller
{
    public function __construct(
        protected CouponService $couponService
    ) {
    }

    public function index()
    {
        return $this->respond($this->couponService->index());
    }

    public function store(StoreCouponRequest $request)
    {
        return $this->respond($this->couponService->store($request->validated()));
    }

    public function show($id)
    {
        return $this->respond($this->couponService->show($id));
    }

    public function update(UpdateCouponRequest $request, $id)
    {
        return $this->respond($this->couponService->update($id, $request->validated()));
    }

    public function destroy($id)
    {
        return $this->respond($this->couponService->destroy($id));
    }

    public function validateCoupon(ValidateCouponRequest $request)
    {
        return $this->respond($this->couponService->validateCoupon($request->validated()));
    }
}
