<?php

namespace App\Http\Controllers;

use App\Models\brands;
use App\Models\category;
use App\Models\Order;
use App\Models\products;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function showOwnerLoginForm()
    {
        if (Auth::check() && (int) Auth::user()->role_id === 1) {
            return redirect('/dashboard/owner');
        }

        return view('dashboard.login');
    }

    public function ownerLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();

            if ((int) $user->role_id === 1) {
                $request->session()->regenerate();

                return redirect('/dashboard/owner');
            }

            Auth::logout();

            return back()->with('error', 'غير مصرح لك بالوصول إلى لوحة التحكم');
        }

        return back()->with('error', 'بيانات تسجيل الدخول غير صحيحة');
    }

    public function ownerLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/dashboard/login')->with('success', 'تم تسجيل الخروج بنجاح');
    }

    public function ownerDashboard()
    {
        if (!Auth::check()) {
            return redirect('/dashboard/login')->with('error', 'يجب تسجيل الدخول أولاً');
        }

        $user = Auth::user();

        if ((int) $user->role_id !== 1) {
            return redirect('/')->with('error', 'غير مصرح لك بالوصول إلى لوحة التحكم');
        }

        $apiToken = $user->createToken('dashboard-panel')->plainTextToken;

        $stats = [
            'orders_count' => Order::count(),
            'products_count' => products::count(),
            'users_count' => User::count(),
            'categories_count' => category::count(),
            'orders_growth' => $this->getGrowthPercentage(Order::class),
            'products_growth' => $this->getGrowthPercentage(products::class),
            'users_growth' => $this->getGrowthPercentage(User::class),
        ];

        $recent_orders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recent_users = User::with('role')
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        $recent_products = products::with('category')
            ->orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        $admins = User::with('role')
            ->where('role_id', 1)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $customers = User::with('role')
            ->where('role_id', 3)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $top_categories = category::withCount('products')
            ->orderBy('products_count', 'desc')
            ->take(5)
            ->get();

        return view('dashboard.owner', compact(
            'stats',
            'recent_orders',
            'recent_users',
            'recent_products',
            'admins',
            'customers',
            'top_categories',
            'user',
            'apiToken'
        ));
    }

    private function getGrowthPercentage($model)
    {
        $now = now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        $previousMonth = $currentMonth == 1 ? 12 : $currentMonth - 1;
        $previousYear = $currentMonth == 1 ? $currentYear - 1 : $currentYear;

        $currentCount = $model::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->count();

        $previousCount = $model::whereMonth('created_at', $previousMonth)
            ->whereYear('created_at', $previousYear)
            ->count();

        if ($previousCount == 0) {
            return $currentCount > 0 ? 100 : 0;
        }

        return round((($currentCount - $previousCount) / $previousCount) * 100);
    }

    private function ensureOwner()
    {
        if (!Auth::check()) {
            abort(403);
        }

        $user = Auth::user();

        if ((int) $user->role_id !== 1) {
            abort(403);
        }

        return $user;
    }

    public function productsIndex()
    {
        $user = $this->ensureOwner();

        $productsList = products::with(['category', 'brand'])
            ->orderByDesc('created_at')
            ->paginate(10);

        $categories = category::orderBy('name_ar')->get();
        $brands = brands::orderBy('name_ar')->get();

        $apiToken = $user->createToken('dashboard-products')->plainTextToken;

        return view('dashboard.products.index', [
            'user' => $user,
            'products' => $productsList,
            'categories' => $categories,
            'brands' => $brands,
            'apiToken' => $apiToken,
        ]);
    }

    public function productsStore(Request $request)
    {
        $user = $this->ensureOwner();

        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'primary_image' => 'required|file|image|max:2048',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'is_active' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('primary_image')) {
            $imageData = $request->file('primary_image');
            $imageName = time() . '_' . $imageData->getClientOriginalName();
            $location = public_path('upload');
            $imageData->move($location, $imageName);
            $imagePath = url('upload/' . $imageName);
        }

        products::create([
            'user_id' => $user->id,
            'brand_id' => $request->brand_id,
            'name_en' => $request->name_en,
            'name_ar' => $request->name_ar,
            'description_en' => $request->description_en,
            'description_ar' => $request->description_ar,
            'primary_image' => $imagePath,
            'is_active' => $request->boolean('is_active', true),
            'is_featured' => $request->boolean('is_featured', false),
            'category_id' => $request->category_id,
        ]);

        return redirect()->route('dashboard.products.index')
            ->with('success', 'تم إضافة المنتج بنجاح');
    }

    public function productsUpdate(Request $request, products $product)
    {
        $this->ensureOwner();

        $request->validate([
            'name_en' => 'sometimes|string|max:255',
            'name_ar' => 'sometimes|string|max:255',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'primary_image' => 'nullable|file|image|max:2048',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'is_active' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
        ]);

        $data = $request->only([
            'name_en',
            'name_ar',
            'description_en',
            'description_ar',
            'category_id',
            'brand_id',
        ]);

        if ($request->hasFile('primary_image')) {
            if ($product->primary_image) {
                $oldImagePath = public_path('upload/' . basename($product->primary_image));
                if (file_exists($oldImagePath)) {
                    @unlink($oldImagePath);
                }
            }

            $imageData = $request->file('primary_image');
            $imageName = time() . '_' . $imageData->getClientOriginalName();
            $location = public_path('upload');
            $imageData->move($location, $imageName);
            $data['primary_image'] = url('upload/' . $imageName);
        }

        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        if ($request->has('is_featured')) {
            $data['is_featured'] = $request->boolean('is_featured');
        }

        $product->update($data);

        return redirect()->route('dashboard.products.index')
            ->with('success', 'تم تحديث المنتج بنجاح');
    }

    public function productsDestroy(products $product)
    {
        $this->ensureOwner();

        if ($product->primary_image) {
            $imagePath = public_path('upload/' . basename($product->primary_image));
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }

        $product->delete();

        return redirect()->route('dashboard.products.index')
            ->with('success', 'تم حذف المنتج بنجاح');
    }

    public function categoriesIndex()
    {
        $user = $this->ensureOwner();

        $categories = category::orderByDesc('created_at')->paginate(10);
        $apiToken = $user->createToken('dashboard-categories')->plainTextToken;

        return view('dashboard.categories.index', [
            'user' => $user,
            'categories' => $categories,
            'apiToken' => $apiToken,
        ]);
    }

    public function categoriesStore(Request $request)
    {
        $user = $this->ensureOwner();

        $request->validate([
            'name_en' => 'required|string|max:255|unique:categories,name_en',
            'name_ar' => 'required|string|max:255|unique:categories,name_ar',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        category::create([
            'name_en' => $request->name_en,
            'name_ar' => $request->name_ar,
            'description_en' => $request->description_en,
            'description_ar' => $request->description_ar,
            'is_active' => $request->boolean('is_active', true),
            'user_id' => $user->id,
        ]);

        return redirect()->route('dashboard.categories.index')
            ->with('success', 'تم إضافة الفئة بنجاح');
    }

    public function categoriesUpdate(Request $request, category $category)
    {
        $this->ensureOwner();

        $request->validate([
            'name_en' => 'sometimes|string|max:255|unique:categories,name_en,' . $category->id,
            'name_ar' => 'sometimes|string|max:255|unique:categories,name_ar,' . $category->id,
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $data = $request->only([
            'name_en',
            'name_ar',
            'description_en',
            'description_ar',
        ]);

        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        $category->update($data);

        return redirect()->route('dashboard.categories.index')
            ->with('success', 'تم تحديث الفئة بنجاح');
    }

    public function categoriesDestroy(category $category)
    {
        $this->ensureOwner();
        $category->delete();

        return redirect()->route('dashboard.categories.index')
            ->with('success', 'تم حذف الفئة بنجاح');
    }

    public function brandsIndex()
    {
        $user = $this->ensureOwner();

        $brandsList = brands::orderByDesc('created_at')->paginate(10);
        $apiToken = $user->createToken('dashboard-brands')->plainTextToken;

        return view('dashboard.brands.index', [
            'user' => $user,
            'brands' => $brandsList,
            'apiToken' => $apiToken,
        ]);
    }

    public function ordersIndex()
    {
        $user = $this->ensureOwner();
        $apiToken = $user->createToken('dashboard-orders')->plainTextToken;

        return view('dashboard.orders.index', [
            'user' => $user,
            'apiToken' => $apiToken,
        ]);
    }

    public function brandsStore(Request $request)
    {
        $this->ensureOwner();

        $request->validate([
            'name_en' => 'required|string|max:255|unique:brands,name_en',
            'name_ar' => 'required|string|max:255|unique:brands,name_ar',
            'logo' => 'nullable|string|max:2048',
            'is_active' => 'sometimes|boolean',
        ]);

        brands::create([
            'name_en' => $request->name_en,
            'name_ar' => $request->name_ar,
            'logo' => $request->logo,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('dashboard.brands.index')
            ->with('success', 'تم إضافة البراند بنجاح');
    }

    public function brandsUpdate(Request $request, brands $brand)
    {
        $this->ensureOwner();

        $request->validate([
            'name_en' => 'sometimes|string|max:255|unique:brands,name_en,' . $brand->id,
            'name_ar' => 'sometimes|string|max:255|unique:brands,name_ar,' . $brand->id,
            'logo' => 'nullable|string|max:2048',
            'is_active' => 'sometimes|boolean',
        ]);

        $data = $request->only([
            'name_en',
            'name_ar',
            'logo',
        ]);

        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }

        $brand->update($data);

        return redirect()->route('dashboard.brands.index')
            ->with('success', 'تم تحديث البراند بنجاح');
    }

    public function brandsDestroy(brands $brand)
    {
        $this->ensureOwner();
        $brand->delete();

        return redirect()->route('dashboard.brands.index')
            ->with('success', 'تم حذف البراند بنجاح');
    }
}
