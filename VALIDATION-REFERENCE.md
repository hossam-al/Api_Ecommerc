# API Validation Reference - Quick Guide

## 🔐 Authentication Endpoints

### POST /api/v1/register
**Required Fields:**
- `name` (string)
- `email` (string, unique, email format)
- `phone` (string, 10-20 chars, unique, regex: `/^[0-9+\-\s()]+$/`)
- `password` (string, min 8 chars)
- `password_confirmation` (string, must match password)

**Optional Fields:**
- `image_path` (file: jpg, jpeg, png, webp | max 2MB)
- `role_id` (integer, **Owner only** can set this)

**Rate Limit:** None

---

### POST /api/v1/login
**Required Fields:**
- `email` (string)
- `password` (string)

**Rate Limit:** 10 attempts per 5 minutes per IP

---

### POST /api/v1/update
**Auth:** Required (Bearer token)

**All Fields Optional:**
- `name` (string)
- `email` (string, unique, email format)
- `phone` (string, 10-20 chars, unique)
- `password` (string, min 8 chars) - if provided, requires `password_confirmation`
- `password_confirmation` (string)
- `image_path` (file: max 2MB)
- `role_id` (integer, **Owner only**)

---

### POST /api/v1/logout
**Auth:** Required
**Fields:** None

---

### DELETE /api/v1/deleteUser
**Auth:** Required
**Fields:** None

---

## 📦 Products Endpoints (Main Catalog)

### GET /api/v1/products
**Auth:** Optional (shows only active products for guests)

**Query Parameters (all optional):**
- `search` (string)
- `category_id` (integer)
- `min_price` (numeric)
- `max_price` (numeric)
- `is_active` (boolean: 0 or 1)
- `sort_by` (string: name, price, created_at)
- `sort_direction` (string: asc, desc)

---

### POST /api/v1/products
**Auth:** Required - **Owner only** (role_id = 1)

**Required Fields:**
- `name` (string, max 255)
- `description` (string)
- `price` (numeric, min 0)
- `stock` (integer, min 0)

**Optional Fields:**
- `sku` (string, max 100)
- `category_id` (integer, exists in categories table)
- `is_active` (boolean: 0 or 1, default: 1)
- `is_featured` (boolean: 0 or 1, default: 0)
- `image_url` (file: jpg, jpeg, png, webp | max 2MB)

---

### PUT /api/v1/products/{id}
**Auth:** Required - **Owner only** (role_id = 1)

**All Fields Optional (sometimes validation):**
- `name` (string, max 255)
- `description` (string)
- `price` (numeric, min 0)
- `stock` (integer, min 0)
- `sku` (string, max 100)
- `category_id` (integer, exists)
- `is_active` (boolean)
- `is_featured` (boolean)
- `image_url` (file: max 2MB)

**Note:** Validation is "sometimes" - only validates if field is present.

---

### DELETE /api/v1/products/{id}
**Auth:** Required - **Owner only**
**Fields:** None

---

### DELETE /api/v1/products
**Auth:** Required - **Owner only**
**Fields:** None (deletes all products)

---

## 🏷️ Categories Endpoints

### GET /api/v1/category
**Auth:** Optional

**Query Parameters:**
- `search` (string, optional)

---

### POST /api/v1/category
**Auth:** Required - **Owner only**

**Required Fields:**
- `name` (string, unique, max 255)

**Optional Fields:**
- `description` (string, nullable)
- `is_active` (boolean: 0 or 1, default: 1)

---

### PUT /api/v1/category/{id}
**Auth:** Required - **Owner only**

**All Fields Optional:**
- `name` (string, max 255)
- `description` (string, nullable)
- `is_active` (boolean)

---

### DELETE /api/v1/category/{id}
**Auth:** Required - **Owner only**
**Fields:** None

---

### DELETE /api/v1/category
**Auth:** Required - **Owner only**
**Fields:** None (deletes all categories)

---

## 🛒 Orders Endpoints

### GET /api/v1/orders
**Auth:** Required
**Fields:** None

**Permissions:**
- Regular users see only their own orders
- Super Admin sees all orders

---

### POST /api/v1/orders
**Auth:** Required

**Required Fields:**
- `product_id` (integer, exists in products table)
- `quantity` (integer, min 1)

**Optional Fields:**
- `notes` (string, nullable)

**Automatic Actions:**
- Auto-generates `order_number` (ORD-{timestamp}-{random})
- Calculates `total_amount` from product price × quantity
- Sets `status` to "pending"
- **Decreases product stock** by quantity
- Creates order and order_item in one operation

**Stock Check:** Returns 400 error if insufficient stock

---

### GET /api/v1/orders/{id}
**Auth:** Required

**Permissions:**
- Order owner can view
- Super Admin can view any order

---

### PUT /api/v1/orders/{id}
**Auth:** Required - **Super Admin only**

**Optional Fields:**
- `status` (string: pending, processing, completed, cancelled)
- `notes` (string, nullable)

**Note:** Does NOT modify order items or totals

---

### DELETE /api/v1/orders/{id}
**Auth:** Required - **Super Admin only**
**Fields:** None

---

## 📋 Order Items Endpoints

### GET /api/v1/order-items
**Auth:** Required

**Query Parameters:**
- `order_id` (integer, optional)

**Permissions:**
- Users see items from their own orders
- Admin/Super Admin see all

---

### POST /api/v1/order-items
**Auth:** Required

**Required Fields:**
- `order_id` (integer, exists, user must own order)
- `product_id` (integer, exists in products)
- `quantity` (integer, min 1)

**Optional Fields:**
- `price` (numeric, min 0) - defaults to product price if not provided

**Automatic Actions:**
- **Decreases product stock** by quantity
- Calculates subtotal (price × quantity)
- **Recalculates order total_amount**

**Stock Check:** Returns 400 if insufficient stock

---

### GET /api/v1/order-items/{id}
**Auth:** Required

**Permissions:**
- Order owner can view
- Admin/Super Admin can view any

---

### PUT /api/v1/order-items/{id}
**Auth:** Required

**Optional Fields:**
- `quantity` (integer, min 1)
- `price` (numeric, min 0)

**Stock Management:**
- If quantity increases: checks stock and decreases product stock
- If quantity decreases: restores stock to product
- **Automatically recalculates order total**

---

### DELETE /api/v1/order-items/{id}
**Auth:** Required
**Fields:** None

**Automatic Actions:**
- **Restores product stock** by item quantity
- **Recalculates order total**

---

## 🔧 Admin Products Endpoints

**All endpoints require Admin role (role_id = 2)**

### GET /api/v1/admin-products
**Auth:** Required - **Admin only**
**Fields:** None (shows only user's own admin products)

---

### POST /api/v1/admin-products
**Auth:** Required - **Admin only**

**Required Fields:**
- `name` (string, max 255)
- `price` (numeric, min 0)

**Optional Fields:**
- `description` (string, nullable)
- `sku` (string, max 100, unique in `admin_products` table)
- `stock` (integer, min 0, default: 0)
- `category_id` (integer, exists)
- `is_active` (boolean, default: 1)
- `is_featured` (boolean, default: 0)
- `image_url` (file: jpg, jpeg, png, webp | max 2MB)

**Note:** Each admin can only see and manage their own products

---

### GET /api/v1/admin-products/{id}
**Auth:** Required - **Admin only**
**Permissions:** Can only view own products

---

### PUT /api/v1/admin-products/{id}
**Auth:** Required - **Admin only**

**All Fields Optional:**
- `name` (string, max 255)
- `description` (string, nullable)
- `price` (numeric, min 0)
- `sku` (string, max 100)
- `stock` (integer, min 0)
- `category_id` (integer, exists)
- `is_active` (boolean)
- `is_featured` (boolean)
- `image_url` (file: max 2MB)

**Permissions:** Can only update own products

---

### DELETE /api/v1/admin-products/{id}
**Auth:** Required - **Admin only**
**Permissions:** Can only delete own products

---

### DELETE /api/v1/admin-products
**Auth:** Required - **Admin only**
**Fields:** None (deletes all own admin products)

---

## 👥 Roles Endpoints

**All endpoints require Super Admin (role.slug = 'super-admin')**

### GET /api/v1/roles
**Auth:** Required - **Super Admin only**
**Fields:** None

---

### POST /api/v1/roles
**Auth:** Required - **Super Admin only**

**Required Fields:**
- `name` (string, unique, max 255)

**Optional Fields:**
- `description` (string, nullable)

**Automatic Actions:**
- Auto-generates `slug` from name using `Str::slug()`

---

### GET /api/v1/roles/{id}
**Auth:** Required - **Super Admin only**
**Fields:** None

---

### PUT /api/v1/roles/{id}
**Auth:** Required - **Super Admin only**

**Optional Fields:**
- `name` (string, max 255)
- `description` (string, nullable)

**Restrictions:**
- Cannot modify the `super-admin` role name
- Slug auto-updates if name changes

---

### DELETE /api/v1/roles/{id}
**Auth:** Required - **Super Admin only**
**Fields:** None

**Restrictions:**
- Cannot delete `super-admin` role (slug check)
- Cannot delete `admin` role (slug check)
- Cannot delete roles that have users assigned

---

### POST /api/v1/roles/assign
**Auth:** Required - **Super Admin only**

**Required Fields:**
- `user_id` (integer, exists in users table)
- `role_id` (integer, exists in roles table)

---

## 📊 Permission Matrix

| Endpoint | Guest | Customer | Admin | Owner | Super Admin |
|----------|-------|----------|-------|-------|-------------|
| Register | ✅ | ✅ | ✅ | ✅ | ✅ |
| Login | ✅ | ✅ | ✅ | ✅ | ✅ |
| Logout | ❌ | ✅ | ✅ | ✅ | ✅ |
| Update Profile | ❌ | ✅ | ✅ | ✅ | ✅ |
| Delete User | ❌ | ✅ | ✅ | ✅ | ✅ |
| View Products | ✅* | ✅ | ✅ | ✅ | ✅ |
| Create Product | ❌ | ❌ | ❌ | ✅ | ❌ |
| Update Product | ❌ | ❌ | ❌ | ✅ | ❌ |
| Delete Product | ❌ | ❌ | ❌ | ✅ | ❌ |
| View Categories | ✅ | ✅ | ✅ | ✅ | ✅ |
| Manage Categories | ❌ | ❌ | ❌ | ✅ | ❌ |
| Create Order | ❌ | ✅ | ✅ | ✅ | ✅ |
| View Own Orders | ❌ | ✅ | ✅ | ✅ | ✅ |
| View All Orders | ❌ | ❌ | ❌ | ❌ | ✅ |
| Update Order | ❌ | ❌ | ❌ | ❌ | ✅ |
| Delete Order | ❌ | ❌ | ❌ | ❌ | ✅ |
| Manage Order Items | ❌ | ✅** | ✅ | ✅ | ✅ |
| Manage Admin Products | ❌ | ❌ | ✅*** | ❌ | ❌ |
| Manage Roles | ❌ | ❌ | ❌ | ❌ | ✅ |

*Guests see only active products  
**Only their own order items  
***Only their own admin products

---

## 🔑 Role IDs Reference

- **Owner:** `role_id = 1` (Full system access, manages main catalog)
- **Admin:** `role_id = 2` (Manages own products in admin_products table)
- **Customer:** `role_id = 3` (Can order and manage own data)
- **Super Admin:** Custom role with `slug = 'super-admin'` (System administration, manages roles)

---

## 📝 Important Validation Notes

1. **Phone Validation:** 
   - Regex: `/^[0-9+\-\s()]+$/`
   - Length: 10-20 characters
   - Must be unique

2. **File Uploads:**
   - Max size: 2MB (2048 KB)
   - Allowed types: jpg, jpeg, png, webp
   - Field names: `image_path` (users), `image_url` (products)

3. **Stock Management:**
   - Creating order: decreases product stock
   - Creating order item: decreases product stock
   - Updating order item quantity: adjusts stock difference
   - Deleting order item: restores stock
   - All stock operations use DB transactions

4. **Order Number Format:**
   - Auto-generated: `ORD-{timestamp}-{random 4 digits}`
   - Example: `ORD-1736789123-4521`

5. **Role Slug Generation:**
   - Auto-generated from role name
   - Uses `Str::slug()` Laravel helper
   - Example: "Super Admin" → "super-admin"

6. **Unique Constraints:**
   - User email (across all users)
   - User phone (across all users)
   - Category name (in categories table)
   - Admin product SKU (in admin_products table only)
   - Role name (in roles table)

7. **Cascading Deletes:**
   - Deleting category: does NOT delete products (category_id becomes null)
   - Deleting order: does NOT auto-delete order items (manual handling)
   - Deleting role with users: PREVENTED (must reassign users first)

---

## 🔒 Authentication Headers

All authenticated endpoints require:

```
Authorization: Bearer {your_token_here}
```

Get token from `/login` or `/register` response.

---

## ⚠️ Common Error Responses

### 401 Unauthorized
```json
{
    "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
    "message": "You do not have permission to perform this action."
}
```

### 422 Validation Error
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "field_name": ["Error message here"]
    }
}
```

### 400 Bad Request (Stock)
```json
{
    "message": "Insufficient stock available"
}
```

### 429 Too Many Requests
```json
{
    "message": "Too many login attempts. Please try again in 5 minutes."
}
```

---

**Generated:** {{ date('Y-m-d H:i:s') }}  
**API Version:** v1  
**Base URL:** `http://127.0.0.1:8000/api/v1`
