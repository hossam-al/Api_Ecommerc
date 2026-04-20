# 📋 E-Commerce API - Complete Request/Response Documentation

## 🔐 Authentication Endpoints

### POST /api/v1/register
**Auth Required:** No  
**Permission:** Public (becomes Customer by default), Owner can specify role_id

**Request Fields:**
```json
{
  "name": "string" (required),
  "email": "string" (required, must be valid email, unique),
  "phone": "string" (required, 10-20 chars, only numbers/+/-, unique),
  "password": "string" (required),
  "password_confirmation": "string" (required, must match password),
  "image_path": "file" (nullable, max 2MB, types: jpg,jpeg,png,webp),
  "role_id": "integer" (optional, only if Owner is creating, must exist in roles table)
}
```

**Response 201:**
```json
{
  "status": true,
  "message": "User registered successfully",
  "data": {
    "id": 1,
    "name": "John",
    "email": "john@example.com",
    "phone": "0123456789",
    "image_url": "http://..."
  },
  "token": "eyJ..."
}
```

---

### POST /api/v1/login
**Auth Required:** No  
**Rate Limit:** 10 requests per 5 minutes

**Request Fields:**
```json
{
  "email": "string" (required, must be valid email),
  "password": "string" (required)
}
```

**Response 200:**
```json
{
  "status": true,
  "message": "Login successful",
  "data": {
    "id": 1,
    "name": "John",
    "email": "john@example.com",
    "role": {
      "id": 1,
      "name": "Owner"
    }
  },
  "token": "eyJ..."
}
```

**Response 401:**
```json
{
  "status": false,
  "message": "Invalid credentials"
}
```

---

### POST /api/v1/update
**Auth Required:** Yes  
**Permission:** Authenticated user (can update own profile), Owner can change role_id

**Request Fields:**
```json
{
  "name": "string" (optional),
  "email": "string" (optional, must be valid email, unique),
  "phone": "string" (optional, 10-20 chars, only numbers/+/-),
  "password": "string" (optional),
  "password_confirmation": "string" (required if password is provided),
  "image_path": "file" (nullable, max 2MB),
  "role_id": "integer" (optional, only Owner can use this, must exist in roles)
}
```

---

### POST /api/v1/logout
**Auth Required:** Yes

**Request:** No body required  
**Response 200:**
```json
{
  "status": true,
  "message": "User logged out successfully"
}
```

---

### DELETE /api/v1/deleteUser
**Auth Required:** Yes

**Request:** No body required  
**Response 200:**
```json
{
  "status": true,
  "message": "User deleted successfully"
}
```

---

## 📦 Products Endpoints (Main Catalog)

### GET /api/v1/products
**Auth Required:** Yes  
**Permission:** All authenticated users

**Query Parameters:**
```
search: string (optional) - searches in name, sku, description
category_id: integer (optional) - filter by category
min_price: number (optional)
max_price: number (optional)
is_active: boolean (optional)
sort_by: string (optional) - name|price|stock|created_at|updated_at
sort_direction: string (optional) - asc|desc
```

**Response 200:**
```json
{
  "status": true,
  "message": "Products retrieved successfully",
  "results": 10,
  "data": [
    {
      "id": 1,
      "name": "Product Name",
      "description": "...",
      "price": 99.99,
      "stock": 50,
      "category": {
        "id": 1,
        "name": "Category Name"
      }
    }
  ]
}
```

---

### GET /api/v1/products/my-products
**Auth Required:** Yes  
**Permission:** Admin (role_id = 2) only

**Response 200:**
```json
{
  "status": true,
  "message": "Products retrieved successfully",
  "results": 5,
  "data": [...]
}
```

---

### GET /api/v1/products/{id}
**Auth Required:** Yes  
**Permission:** All authenticated users

**Response 200:**
```json
{
  "status": true,
  "message": "Product retrieved successfully",
  "data": {
    "id": 1,
    "name": "T-Shirt",
    "description": "Cotton T-shirt",
    "price": 99.99,
    "stock": 50,
    "sku": "TSH-001",
    "category": {
      "id": 1,
      "name": "Clothing"
    },
    "is_active": true,
    "is_featured": false
  }
}
```

**Response 404:**
```json
{
  "status": false,
  "message": "Product not found"
}
```

---

### POST /api/v1/products
**Auth Required:** Yes  
**Permission:** Owner (role_id = 1) ONLY

**Request Fields:**
```json
{
  "name": "string" (required, max 255 chars),
  "description": "string" (required, 3-255 chars),
  "price": "number" (required, min 0),
  "stock": "integer" (required, min 0),
  "sku": "string" (optional, unique),
  "category_id": "integer" (optional, must exist in categories),
  "is_active": "boolean" (optional, default true),
  "is_featured": "boolean" (optional, default true),
  "image_url": "file" (optional, max 2MB, types: jpg,jpeg,png,webp)
}
```

**Response 201:**
```json
{
  "status": true,
  "message": "Product added successfully",
  "data": {...}
}
```

**Response 403:**
```json
{
  "status": false,
  "message": "Only Super Admin can add products to main catalog"
}
```

---

### POST /api/v1/products/{id}
**Auth Required:** Yes  
**Permission:** Owner (role_id = 1) ONLY

**Request Fields:** (All optional)
```json
{
  "name": "string" (max 255 chars),
  "description": "string" (3-255 chars),
  "price": "number" (min 0),
  "stock": "integer" (min 0),
  "sku": "string" (unique),
  "category_id": "integer" (must exist),
  "is_active": "boolean",
  "is_featured": "boolean",
  "image_url": "file" (max 2MB)
}
```

---

### DELETE /api/v1/products/{id}
**Auth Required:** Yes  
**Permission:** Owner (role_id = 1) ONLY

**Response 200:**
```json
{
  "status": true,
  "message": "Product deleted successfully"
}
```

---

### DELETE /api/v1/products/DeleteAll/delete
**Auth Required:** Yes  
**Permission:** Owner (role_id = 1) ONLY

**Response 200:**
```json
{
  "status": true,
  "message": "All products deleted successfully"
}
```

---

## 👨‍💼 Admin Products Endpoints

### GET /api/v1/admin-products
**Auth Required:** Yes  
**Permission:** Admin (role_id = 2) ONLY

**Query Parameters:**
```
search: string (optional)
```

**Response 200:**
```json
{
  "status": true,
  "data": [...]
}
```

---

### POST /api/v1/admin-products
**Auth Required:** Yes  
**Permission:** Admin (role_id = 2) ONLY

**Request Fields:**
```json
{
  "name": "string" (required, max 255),
  "description": "string" (optional),
  "price": "number" (required, min 0),
  "sku": "string" (optional, unique in admin_products),
  "stock": "integer" (optional, min 0, default 0),
  "category_id": "integer" (optional, must exist),
  "is_active": "boolean" (optional, default true),
  "is_featured": "boolean" (optional, default false),
  "image_url": "file" (optional, max 2MB)
}
```

---

### GET /api/v1/admin-products/{id}
**Auth Required:** Yes  
**Permission:** Admin (role_id = 2) ONLY - can only view own products

**Response 200:**
```json
{
  "status": true,
  "data": {
    "id": 1,
    "name": "My Product",
    "description": "Product description",
    "price": 99.99,
    "stock": 50,
    "user_id": 2
  }
}
```

---

### POST /api/v1/admin-products/{id}
**Auth Required:** Yes  
**Permission:** Admin (role_id = 2) ONLY - can only update own products

**Request Fields:** (All optional)
```json
{
  "name": "string" (max 255),
  "description": "string",
  "price": "number" (min 0),
  "sku": "string" (unique),
  "stock": "integer" (min 0),
  "category_id": "integer",
  "is_active": "boolean",
  "is_featured": "boolean",
  "image_url": "file" (max 2MB)
}
```

---

### DELETE /api/v1/admin-products/{id}
**Auth Required:** Yes  
**Permission:** Admin (role_id = 2) ONLY - can only delete own products

---

### DELETE /api/v1/admin-products/DeleteAll/delete
**Auth Required:** Yes  
**Permission:** Admin (role_id = 2) ONLY - deletes all own products

---

## 🏷️ Categories Endpoints

### GET /api/v1/category
**Auth Required:** Yes  
**Permission:** All authenticated users

**Query Parameters:**
```
search: string (optional) - searches in name or id
```

---

### GET /api/v1/category/{id}
**Auth Required:** Yes  
**Permission:** All authenticated users

**Response 200:**
```json
{
  "status": true,
  "message": "Category retrieved successfully",
  "data": {
    "id": 1,
    "name": "Electronics",
    "description": "Electronic products",
    "is_active": true
  }
}
```

**Response 404:**
```json
{
  "status": false,
  "message": "Category not found"
}
```

---

### POST /api/v1/category
**Auth Required:** Yes  
**Permission:** Owner (role_id = 1) ONLY

**Request Fields:**
```json
{
  "name": "string" (required, unique),
  "description": "string" (optional),
  "is_active": "boolean" (optional, default true)
}
```

---

### POST /api/v1/category/{id}
**Auth Required:** Yes  
**Permission:** Owner (role_id = 1) ONLY

**Request Fields:** (All optional)
```json
{
  "name": "string" (unique),
  "description": "string",
  "is_active": "boolean"
}
```

---

### DELETE /api/v1/category/{id}
**Auth Required:** Yes  
**Permission:** Owner (role_id = 1) ONLY

---

### DELETE /api/v1/category/DeleteAll/delete
**Auth Required:** Yes  
**Permission:** Owner (role_id = 1) ONLY

---

## 🛒 Orders Endpoints

### GET /api/v1/orders
**Auth Required:** Yes  
**Permission:** 
- Super Admin: sees all orders
- Regular users: see only own orders

**Response 200:**
```json
{
  "message": "Orders retrieved successfully",
  "orders": [
    {
      "id": 1,
      "user_id": 1,
      "order_number": "ABC123XYZ",
      "status": "pending",
      "total_amount": 199.98,
      "notes": "Please deliver after 5 PM",
      "items": [...]
    }
  ]
}
```

---

### POST /api/v1/orders
**Auth Required:** Yes  
**Permission:** All authenticated users

**Request Fields:**
```json
{
  "product_id": "integer" (required, must exist in products table),
  "quantity": "integer" (required, min 1),
  "notes": "string" (optional)
}
```

**Response 201:**
```json
{
  "success": true,
  "message": "Order created successfully",
  "order": {
    "id": 1,
    "user_id": 1,
    "order_number": "ABC123XYZ",
    "status": "pending",
    "total_amount": 199.98,
    "items": [
      {
        "id": 1,
        "product_id": 5,
        "quantity": 2,
        "price": 99.99,
        "subtotal": 199.98
      }
    ]
  }
}
```

**Response 400:**
```json
{
  "message": "Product {name} does not have enough stock."
}
```

---

### GET /api/v1/orders/{id}
**Auth Required:** Yes  
**Permission:** Super Admin OR order owner

**Response 200:**
```json
{
  "order": {
    "id": 1,
    "user_id": 1,
    "order_number": "ABC123XYZ",
    "status": "pending",
    "total_amount": 199.98,
    "notes": "Deliver after 5 PM",
    "items": [
      {
        "id": 1,
        "product": {
          "id": 5,
          "name": "T-Shirt",
          "price": 99.99
        },
        "quantity": 2,
        "price": 99.99,
        "subtotal": 199.98
      }
    ]
  }
}
```

---

### PUT /api/v1/orders/{id}
**Auth Required:** Yes  
**Permission:** Super Admin ONLY

**Request Fields:**
```json
{
  "status": "string" (optional, values: pending|processing|completed|cancelled),
  "notes": "string" (optional)
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Order updated successfully",
  "order": {
    "id": 1,
    "status": "processing",
    "notes": "Order is being prepared"
  }
}
```

---

### POST /api/v1/orders/{id}/cancel
**Auth Required:** Yes  
**Permission:** Order owner (can cancel own pending orders)

**Request:** No body required

**Response 200:**
```json
{
  "success": true,
  "message": "Order cancelled successfully",
  "order": {
    "id": 1,
    "status": "cancelled",
    "total_amount": 199.98
  }
}
```

**Response 400:**
```json
{
  "error": "Cannot cancel order. Only pending orders can be cancelled."
}
```

**Response 403:**
```json
{
  "error": "Unauthorized"
}
```

**Note:** 
- Only orders with status "pending" can be cancelled
- Product stock is automatically restored when order is cancelled
- User can only cancel their own orders

---

### DELETE /api/v1/orders/{id}
**Auth Required:** Yes  
**Permission:** Super Admin ONLY

**Response 200:**
```json
{
  "success": true,
  "message": "Order deleted successfully"
}
```

---



## 🔒 Permission Summary

| Endpoint | Owner (1) | Admin (2) | Customer (3) | Super Admin |
|----------|-----------|-----------|--------------|-------------|
| Products CRUD (main) | ✅ | ❌ | ❌ | ❌ |
| Admin Products CRUD | ❌ | ✅ (own) | ❌ | ❌ |
| Categories CRUD | ✅ | ❌ (view only) | ❌ (view only) | ❌ (view only) |
| Orders Create | ✅ | ✅ | ✅ | ✅ |
| Orders View Own | ✅ | ✅ | ✅ | ✅ |
| Orders View All | ❌ | ❌ | ❌ | ✅ |
| Orders Cancel (pending) | ✅ (own) | ✅ (own) | ✅ (own) | ✅ |
| Orders Update Status | ❌ | ❌ | ❌ | ✅ |
| Orders Delete | ❌ | ❌ | ❌ | ✅ |


---

## 📝 Validation Notes

1. **File Uploads:** Max 2MB, types: jpg, jpeg, png, webp
2. **Phone Format:** 10-20 characters, allows: 0-9, +, -, spaces
3. **Stock Management:** Automatically adjusted on order create/cancel
4. **Unique Constraints:** email, phone, SKU (per table)
5. **Password:** Must be confirmed with password_confirmation field
6. **Multipart Form Data:** Required for endpoints with file uploads (image_path, image_url)
7. **Order Cancellation:** Only pending orders can be cancelled, stock is restored automatically
8. **Order Number:** Auto-generated in format: ORD-{timestamp}-{random}

---

## 🔑 Role IDs Reference

- **Owner:** `role_id = 1` (Manages main product catalog and categories)
- **Admin:** `role_id = 2` (Manages own products in admin_products table)
- **Customer:** `role_id = 3` (Can place orders and manage own account)
- **Super Admin:** Role with `slug = 'super-admin'` (Full system access, manages all orders)

---

## ⚠️ Important Notes

1. **Order Flow:**
   - User creates order → status: "pending"
   - User can cancel order (if status = "pending") → status: "cancelled"
   - Super Admin updates status → "processing" → "completed"
   
2. **Stock Management:**
   - Creating order: decreases product stock
   - Cancelling order: restores product stock
   - Stock check performed before order creation

3. **Super Admin vs Owner:**
   - Owner (role_id = 1): Manages main catalog (products, categories)
   - Super Admin (slug = 'super-admin'): Manages orders and system operations

4. **Deleted Features:**
   - ❌ Order Items endpoints removed (orders now contain single product)
   - ❌ Role management endpoints removed
   - Orders are created with single product + quantity

---

**API Version:** v1  
**Base URL:** `http://127.0.0.1:8000/api/v1`  
**Last Updated:** {{ date('Y-m-d') }}
