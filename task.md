# [Level Last Boss] Mini Shop – Full Stack Challenge

รวมทุกอย่างจาก Level 1–5 สร้างระบบร้านค้าออนไลน์ขนาดเล็ก 1 ระบบ ตั้งแต่ Database → Admin Web → REST API → Mobile App

> ใช้ AI Coding Agent ได้ตาม **Rules สำหรับ Last Boss** ใน Level 5 (ต้องมี `CLAUDE.md`, Commit ทีละงาน, อธิบายโค้ด Create Order ได้เอง)

```text
┌──────────────┐      ┌───────────────────────┐      ┌──────────────┐
│  Admin Web   │◄────►│   Laravel Back-end    │◄────►│ Flutter App  │
│ Blade + AJAX │      │ Eloquent + API + Auth │      │  (Customer)  │
│  (Level 2)   │      │   (Level 1 + 3)       │      │  (Level 4)   │
└──────────────┘      └───────────────────────┘      └──────────────┘
```

| Role | ใช้ผ่าน | ทำอะไรได้ |
|---|---|---|
| Admin | Web (Blade) | จัดการสินค้า, หมวดหมู่, ดู/อัปเดตสถานะ Order |
| Customer | Flutter App | สมัคร, Login, ดูสินค้า, สั่งซื้อ, ดู/ยกเลิก Order ของตัวเอง |

---

## Phase 1 – Database (Level 1)

### 1.1 Tables

**users** (มีอยู่แล้ว เพิ่ม `role`)

| Column | Type | Note |
|---|---|---|
| role | string | `admin` / `customer` default `customer` |

**categories**

| Column | Type | Note |
|---|---|---|
| id | bigint | PK |
| name | string | unique |
| timestamps | | |

**products**

| Column | Type | Note |
|---|---|---|
| id | bigint | PK |
| category_id | foreignId | `constrained()` |
| name | string | |
| description | text | nullable |
| price | decimal(10,2) | |
| stock | unsignedInteger | default 0 |
| is_active | boolean | default true |
| timestamps | | |
| deleted_at | | **Soft Delete** |

**orders**

| Column | Type | Note |
|---|---|---|
| id | bigint | PK |
| user_id | foreignId | `constrained()` |
| total | decimal(10,2) | |
| status | string | `pending` / `paid` / `shipped` / `completed` / `cancelled` |
| timestamps | | |

**order_items**

| Column | Type | Note |
|---|---|---|
| id | bigint | PK |
| order_id | foreignId | `constrained()->cascadeOnDelete()` |
| product_id | foreignId | `constrained()` |
| quantity | unsignedInteger | |
| price | decimal(10,2) | ราคา ณ ตอนสั่ง (ห้ามดึงจาก products ภายหลัง) |

### 1.2 Relationships

```text
Category 1 ── * Product
User     1 ── * Order
Order    1 ── * OrderItem * ── 1 Product
```

ต้องมีใน Model: `hasMany`, `belongsTo` ครบทุกคู่

### 1.3 Seeder

- Admin 1 คน: `admin@shop.test` / `password123`
- Customer 1 คน: `customer@shop.test` / `password123`
- Categories อย่างน้อย 3
- Products อย่างน้อย 30 (ใช้ Factory) เพื่อทดสอบ Pagination

```bash
php artisan migrate:fresh --seed
```

---

## Phase 2 – Admin Web (Level 2)

ทุกหน้าต้อง Login และต้องเป็น `role = admin` (สร้าง Middleware `admin`)

### 2.1 Products Page

- แสดงด้วย **DataTables** (ค้นหา, แบ่งหน้า)
- เพิ่ม / แก้ไข ผ่าน Modal + **AJAX** (ไม่ Reload หน้า)
- ลบเป็น **Soft Delete**
- มี Tab/Filter ดูสินค้าที่ถูกลบ (`onlyTrashed()`) และปุ่ม **Restore**
- แสดง Validation Error 422 ใต้ช่อง Input

### 2.2 Categories Page

- CRUD ด้วย Blade + `compact()` แบบธรรมดาก็ได้
- ห้ามลบ Category ที่ยังมี Product อยู่ (ตอบ Error ที่อ่านเข้าใจได้)

### 2.3 Orders Page

- รายการ Orders ทั้งหมด + ชื่อลูกค้า + ยอดรวม + สถานะ
- กดดูรายละเอียด (Items)
- เปลี่ยนสถานะด้วย Dropdown + AJAX แบบ **Seamless Update** (แถวในตารางเปลี่ยนทันที)

---

## Phase 3 – REST API (Level 3)

Response ทุกตัวใช้รูปแบบเดียวกัน และใช้ **API Resource** (`ProductResource`, `OrderResource`, `UserResource`)

```json
{ "success": true, "message": "...", "data": { } }
```

### 3.1 Endpoints

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/api/register` | - | สมัครสมาชิก (role = customer) คืน token |
| POST | `/api/login` | - | Login คืน token |
| POST | `/api/logout` | ✅ | ลบ token ปัจจุบัน |
| GET | `/api/profile` | ✅ | ข้อมูลตัวเอง |
| GET | `/api/categories` | - | หมวดหมู่ทั้งหมด |
| GET | `/api/products` | - | Pagination 10/หน้า, เฉพาะ `is_active` |
| GET | `/api/products/{id}` | - | รายละเอียดสินค้า |
| GET | `/api/orders` | ✅ | Orders **ของตัวเอง** เท่านั้น (Pagination) |
| POST | `/api/orders` | ✅ | สร้าง Order |
| GET | `/api/orders/{id}` | ✅ | รายละเอียด Order ของตัวเอง |
| POST | `/api/orders/{id}/cancel` | ✅ | ยกเลิก (เฉพาะ `pending`) |

### 3.2 Query Parameters – `GET /api/products`

| Param | Example | Description |
|---|---|---|
| `search` | `?search=shirt` | ค้นหาจากชื่อ |
| `category_id` | `?category_id=2` | กรองตามหมวด |
| `page` | `?page=2` | หน้าที่ต้องการ |

### 3.3 Create Order – `POST /api/orders`

**Request**

```json
{
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 5, "quantity": 1 }
  ]
}
```

**Validation**

```php
'items' => 'required|array|min:1',
'items.*.product_id' => 'required|integer|exists:products,id',
'items.*.quantity' => 'required|integer|min:1',
```

**Business Rules (สำคัญ – นี่คือ Boss จริง)**

1. ราคาและยอดรวมต้องคำนวณจาก **Database** ห้ามเชื่อราคาจาก Client
2. ถ้า Stock ไม่พอ → ตอบ `422` พร้อมบอกว่าสินค้าไหนไม่พอ
3. สร้าง Order + Items + ตัด Stock ต้องอยู่ใน **`DB::transaction()`** (พังกลางทาง = ไม่มีอะไรถูกบันทึก)
4. กันสั่งพร้อมกันจน Stock ติดลบ ใช้ `lockForUpdate()` ตอนอ่าน Product
5. Cancel Order → คืน Stock กลับ (ใน Transaction เช่นกัน)

```php
DB::transaction(function () use ($request) {
    $product = Product::lockForUpdate()->findOrFail($id);
    // check stock, create order, create items, decrement stock
});
```

### 3.4 Security Rules

| Case | Expected |
|---|---|
| ไม่ส่ง Token ไป Endpoint ที่ต้อง Auth | `401` |
| Customer ดู Order ของคนอื่น `/api/orders/{id}` | `404` (หรือ `403`) |
| Cancel Order ที่ไม่ใช่ `pending` | `422` |
| Customer เรียก Admin Web | Redirect / `403` |
| Response ห้ามมี `password`, `remember_token` | ใช้ API Resource |

---

## Phase 4 – Flutter App (Level 4)

### 4.1 Screens

| Screen | Requirement |
|---|---|
| Register / Login | Validate Form, แสดง Error 401 / 422 |
| Products | List + **Infinite Scroll** (Pagination), Search, Filter Category, Pull to Refresh |
| Product Detail | ชื่อ, ราคา, Stock, ปุ่ม Add to Cart (ห้ามเกิน Stock) |
| Cart | เพิ่ม/ลด/ลบจำนวน, ยอดรวม, ปุ่ม Checkout |
| My Orders | รายการ Orders + Status (ใช้สีแยกสถานะ) |
| Order Detail | Items + ยอดรวม + ปุ่ม Cancel (แสดงเฉพาะ `pending`) |
| Profile | ข้อมูลผู้ใช้ + Logout |

### 4.2 Technical Requirements

- แยกโครงสร้าง `models/`, `services/`, `screens/` (ต่อยอดจาก Level 4)
- เก็บ Token ด้วย `shared_preferences` (เปิด App ใหม่ไม่ต้อง Login)
- ถ้า API ตอบ `401` → ลบ Token และพากลับหน้า Login อัตโนมัติ
- Checkout สำเร็จ → ล้าง Cart และไปหน้า Order Detail
- Checkout แล้ว Stock ไม่พอ (422) → แสดงข้อความชัดเจน ไม่ล้าง Cart
- ทุกหน้ามีสถานะ Loading / Error / Empty

---

## Test Scenarios (ต้องผ่านทั้งหมด)

1. `php artisan migrate:fresh --seed` แล้วระบบใช้งานได้ทันที
2. Register ใน App → Login อัตโนมัติ
3. ค้นหาสินค้า + เลื่อนลงจนโหลดหน้า 2, 3 ได้
4. สั่งซื้อ 2 รายการ → Stock ใน Admin Web ลดลงถูกต้อง
5. สั่งซื้อเกิน Stock → ได้ 422 และ Stock ไม่เปลี่ยน
6. Cancel Order → Stock คืน, สถานะเป็น `cancelled`
7. Admin เปลี่ยนสถานะเป็น `shipped` → App Pull to Refresh แล้วเห็นสถานะใหม่ / Cancel ไม่ได้แล้ว
8. Admin Soft Delete สินค้า → หายจาก App, Restore → กลับมา
9. ใช้ Token ของ Customer A เปิด Order ของ Customer B → ไม่ได้
10. Logout ใน App → เรียก API ด้วย Token เดิมได้ `401`

---

## Scoring (100 คะแนน)

| Part | Points |
|---|---|
| Phase 1 – Migration, Relationship, Seeder | 15 |
| Phase 2 – Admin Web (DataTables, AJAX, Soft Delete/Restore) | 20 |
| Phase 3 – API (Resource, Validation, Pagination, Auth) | 20 |
| Phase 3 – Order Logic (Transaction, Stock, Lock, Cancel) | 15 |
| Phase 4 – Flutter App (Screens + Flow ครบ) | 20 |
| Test Scenarios ผ่านครบ 10 ข้อ | 10 |

## Bonus (+ คะแนนพิเศษ)

- Upload รูปสินค้า (`storage:link`) และแสดงใน App
- Feature Test ด้วย PHPUnit / Pest สำหรับ `POST /api/orders` (+ กรณี Stock ไม่พอ)
- Dashboard ใน Admin: ยอดขายวันนี้, จำนวน Order แต่ละสถานะ
- Rate Limit สำหรับ `/api/login` (`throttle:5,1`)
- Postman Collection สำหรับทุก Endpoint

## Submission

- Git Repository 2 ตัว (หรือ Monorepo): `shop-backend/`, `shop_app/`
- `README.md` มีวิธีรัน + บัญชีทดสอบ

**Defeat the Last Boss = Workshop Complete** 🏆
