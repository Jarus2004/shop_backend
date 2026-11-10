# 🧠 สิ่งที่ต้องเรียนรู้เพิ่มเติม (Learning Guide)
**ต่อยอดจากพื้นฐานสู่ระดับ "Last Boss" – E-Commerce Full Stack Architecture**

เอกสารนี้รวบรวมแนวคิดเชิงลึก (Core Concepts), เทคนิคเฉพาะทาง และเหตุผลเบื้องหลังสถาปัตยกรรมที่ระบุไว้ในแผนการพัฒนา เพื่อให้นักศึกษาสามารถทำความเข้าใจและนำไปอธิบายต่อได้อย่างเชี่ยวชาญ

---

## 📌 สารบัญหัวข้อการเรียนรู้
1. [Database Transaction & ACID Properties](#1-database-transaction--acid-properties)
2. [Concurrency Control & Pessimistic Locking (`lockForUpdate`)](#2-concurrency-control--pessimistic-locking-lockforupdate)
3. [ความถูกต้องของข้อมูลและ Price Snapshotting](#3-ความถูกต้องของข้อมูลและ-price-snapshotting)
4. [การทำ Client-Side Token Authentication & Global AJAX Interceptor](#4-การทำ-client-side-token-authentication--global-ajax-interceptor)
5. [การจัดการ Validation Error 422 เข้ากับ Bootstrap 5](#5-การจัดการ-validation-error-422-เข้ากับ-bootstrap-5)
6. [DataTables AJAX Reload และ Soft Delete/Restore Workflow](#6-datatables-ajax-reload-และ-soft-delete-restore-workflow)
7. [การป้องกัน Foreign Key Violation เชิงตรรกะ (Business Validation)](#7-การป้องกัน-foreign-key-violation-เชิงตรรกะ-business-validation)
8. [โครงสร้าง Middleware ใน Laravel 12 (ไม่มี Kernel.php แล้ว)](#8-โครงสร้าง-middleware-ใน-laravel-12-ไม่มี-kernelphp-แล้ว)
9. [แนวคำถามสัมภาษณ์ที่พบบ่อย (Interview Q&A)](#9-แนวคำถามสัมภาษณ์ที่พบบ่อย-interview-qa)

---

## 1. Database Transaction & ACID Properties

### ทำไมต้องเรียนรู้?
ในการสร้างคำสั่งซื้อ (Create Order) ไม่ได้มีแค่คำสั่งเดียว แต่ประกอบด้วยหลายขั้นตอน:
1. สร้างบันทึกในตาราง `orders`
2. สร้างรายการสินค้าแต่ละชิ้นในตาราง `order_items`
3. หักจำนวนสต็อกในตาราง `products`

**ปัญหาที่จะเกิดขึ้นหากไม่ใช้ Transaction:**
หากขั้นตอนที่ 1 และ 2 สำเร็จ แต่ในขั้นตอนที่ 3 เกิดระบบขัดข้อง (เช่น ไฟดับ, สต็อกไม่พอ, หรือเน็ตหลุด) ข้อมูล Order จะถูกสร้างขึ้นค้างไว้ในฐานข้อมูล แต่สินค้าไม่ถูกตัดสต็อก ทำให้ยอดเงินและคลังสินค้าไม่ตรงกัน!

### แนวคิด ACID:
* **Atomicity (ความเป็นหนึ่งเดียว):** "ต้องสำเร็จทั้งหมด หรือไม่สำเร็จเลย" ถ้ามีข้อผิดพลาดแม้แต่จุดเดียว ระบบจะทำการ **Rollback** ยกเลิกคำสั่งทั้งหมดเสมือนไม่เคยเกิดขึ้น
* **Consistency:** ข้อมูลหลังทำธุรกรรมต้องถูกต้องตามเงื่อนไข (เช่น สต็อกต้องไม่ติดลบ)
* **Isolation:** ธุรกรรมที่ทำงานพร้อมกันต้องไม่รบกวนซึ่งกันและกัน
* **Durability:** เมื่อ Commit สำเร็จ ข้อมูลจะถูกบันทึกถาวร

### ตัวอย่างการเขียนใน Laravel:
```php
use Illuminate\Support\Facades\DB;

DB::transaction(function () use ($request, $user) {
    // 1. สร้าง Order
    $order = Order::create([...]);

    foreach ($request->items as $item) {
        // 2. สร้าง Item
        $order->orderItems()->create([...]);

        // 3. หัก Stock
        $product->decrement('stock', $item['quantity']);
    }

    // หากมี Exception เกิดขึ้นที่จุดใดก็ตาม Laravel จะ Rollback ให้อัตโนมัติ
});
```

---

## 2. Concurrency Control & Pessimistic Locking (`lockForUpdate`)

### ปัญหา Race Condition ในโลกจริง
สมมติว่าสินค้าชิ้นหนึ่ง **เหลือสต็อก 1 ชิ้นสุดท้าย**
* นาย A และ นาย B กดปุ่ม "สั่งซื้อ" เข้ามาในเวลา **19:00:00.001 น.** เดียวกันเป๊ะ
* หากอ่านค่าสต็อกตามปกติ:
  * Thread A อ่านสต็อก -> พบว่าเหลือ 1 (ผ่าน)
  * Thread B อ่านสต็อก -> พบว่าเหลือ 1 (ผ่าน)
  * Thread A บันทึกออเดอร์ และลดสต็อกเหลือ 0
  * Thread B บันทึกออเดอร์ และลดสต็อกเหลือ -1 (เกิดปัญหาสต็อกติดลบ หรือขายเกินจริง Overselling!)

### การแก้ปัญหาด้วย `lockForUpdate()` (Pessimistic Locking)
การใช้ `lockForUpdate()` ใน Laravel จะสร้างคำสั่ง SQL:
```sql
SELECT * FROM products WHERE id = 1 FOR UPDATE;
```
* **กลไกการทำงาน:** ฐานข้อมูล (MySQL InnoDB) จะทำการล็อกแถว (Row Lock) ของสินค้านั้นไว้ทันที
* ถ้าคำขอของนาย B มาถึงในขณะที่นาย A ยังประมวลผลไม่เสร็จ คำขอของนาย B จะต้อง **รอ (Wait)** จนกว่า Transaction ของนาย A จะเสร็จสมบูรณ์
* เมื่อนาย B ได้รับการทำงานต่อ สต็อกที่นาย B เห็นจะกลายเป็น 0 ทันที ระบบจึงสามารถตอบ `422 สต็อกไม่เพียงพอ` ได้อย่างถูกต้องและปลอดภัย

```php
// ตัวอย่างใน Controller
DB::transaction(function () use ($item) {
    // ล็อกแถวข้อมูลสินค้านี้ไว้จนกว่าจะสิ้นสุด Transaction
    $product = Product::lockForUpdate()->findOrFail($item['product_id']);

    if ($product->stock < $item['quantity']) {
        throw new \Exception("สินค้า {$product->name} มีสต็อกไม่เพียงพอ");
    }

    $product->decrement('stock', $item['quantity']);
});
```

---

## 3. ความถูกต้องของข้อมูลและ Price Snapshotting

### 3.1 ห้ามเชื่อราคาจาก Client เด็ดขาด!
* ผู้ไม่หวังดีสามารถใช้ Postman หรือแก้ไข Request Payload ผ่าน Developer Tools เปลี่ยนราคาจาก `5,000` บาท เป็น `1` บาทได้
* **กฎเหล็กของ Backend:** ราคาต่อชิ้น (`price`) และยอดรวมคำสั่งซื้อ (`total`) ต้องถูกคำนวณและดึงจาก **Database ฝั่งเซิร์ฟเวอร์เท่านั้น**

### 3.2 ทำไมต้องมีฟิลด์ `price` ในตาราง `order_items`?
* ตาราง `products` มีคอลัมน์ `price` อยู่แล้ว ทำไม `order_items` ถึงต้องเก็บ `price` ซ้ำซ้อน?
* **คำตอบ:** เรียกว่าการทำ **Historical Price Snapshot**
  * วันนี้สินค้า A ขายราคา 500 บาท ลูกค้าสั่งซื้อไป ยอดรวมคือ 500 บาท
  * เดือนหน้าเจ้าของร้านปรับราคาสินค้า A เป็น 700 บาท
  * หากเราไม่เก็บ `price` ไว้ใน `order_items` แล้วไปดึงจาก `products.price` เมื่อเปิดดูประวัติออเดอร์เก่า ยอดคำสั่งซื้อของลูกค้าจะเพี้ยนกลายเป็น 700 บาททันที!
  * ดังนั้น ราคา ณ วันที่ทำการสั่งซื้อต้องถูกบันทึกแยกไว้ถาวร

---

## 4. การทำ Client-Side Token Authentication & Global AJAX Interceptor

ใน Admin Web แบบดั้งเดิมจะใช้ Cookie Session แต่ในโจทย์นี้เราเลือกใช้ **Token-based Authentication (Sanctum Token)** ผ่าน AJAX ทุกคำขอ

### 4.1 การตั้งค่า `$.ajaxSetup`
เพื่อให้ทุกคำขอ AJAX ที่ส่งออกจากหน้าเว็บ แนบ Bearer Token ไปด้วยโดยไม่ต้องเขียนซ้ำในทุกฟังก์ชัน:
```javascript
const token = localStorage.getItem('admin_token');

$.ajaxSetup({
    headers: {
        'Accept': 'application/json',
        'Authorization': token ? 'Bearer ' + token : ''
    }
});
```

### 4.2 การดักจับ Error ส่วนกลาง (`Global AJAX Error Handler`)
หาก Token หมดอายุ หรือผู้ใช้ถูกเพิกถอนสิทธิ์ เซิร์ฟเวอร์จะตอบกลับ `401 Unauthorized` หรือ `403 Forbidden`
เราสามารถเขียนดักจับไว้ที่ระดับ Document เพื่อให้ Redirect ไปหน้า Login ทันที:
```javascript
$(document).ajaxError(function (event, jqXHR) {
    if (jqXHR.status === 401 || jqXHR.status === 403) {
        localStorage.removeItem('admin_token');
        window.location.href = '/admin/login';
    }
});
```

---

## 5. การจัดการ Validation Error 422 เข้ากับ Bootstrap 5

เมื่อ Laravel พบว่าข้อมูลนำเข้าไม่ผ่านเงื่อนไข (เช่น ไม่ได้กรอกชื่อ หรือราคาติดลบ) จะส่งกลับ HTTP Status `422 Unprocessable Content` ในรูปแบบ JSON:
```json
{
  "message": "The name field is required.",
  "errors": {
    "name": ["The name field is required."],
    "price": ["The price must be at least 0."]
  }
}
```

### เทคนิคการนำข้อความไปแสดงใต้ช่อง Input แบบไดนามิก:
ใน Bootstrap 5 จะใช้คลาส `.is-invalid` ที่ตัว Input และ `<div class="invalid-feedback">` สำหรับข้อความแจ้งเตือน:
```javascript
function handleValidationErrors(form, errors) {
    // 1. เคลียร์ Error เดิมออกก่อน
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').remove();

    // 2. วนลูปแสดง Error ตามชื่อฟิลด์
    $.each(errors, function (field, messages) {
        const input = form.find(`[name="${field}"]`);
        input.addClass('is-invalid');
        input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
    });
}
```

---

## 6. DataTables AJAX Reload และ Soft Delete/Restore Workflow

### 6.1 การรีโหลดตารางโดยไม่รีเฟรชหน้าเว็บ
เมื่อเพิ่มหรือแก้ไขสินค้าเสร็จสิ้น แทนที่จะใช้ `location.reload()` ให้ใช้เมธอดของ DataTables:
```javascript
// รีโหลดข้อมูลจาก API ใหม่ โดยคงหน้า Pagination เดิมไว้ (ไม่เด้งกลับไปหน้า 1)
table.ajax.reload(null, false);
```

### 6.2 Soft Delete vs Force Delete
* **Normal Delete (Hard Delete):** ลบแถวออกจากฐานข้อมูลจริง กู้คืนไม่ได้
* **Soft Delete:** เติมค่าเวลาลงในคอลัมน์ `deleted_at` 
  * เมื่อเรียก `Product::all()` Laravel จะเติมเงื่อนไข `WHERE deleted_at IS NULL` ให้อัตโนมัติ ทำให้สินค้าเสมือนหายไปจากหน้าร้าน
  * เมื่อต้องการดูสินค้าที่ลบไป: `Product::onlyTrashed()->get()`
  * เมื่อต้องการกู้คืน: `$product->restore()` (ค่าใน `deleted_at` จะถูกเซ็ตกลับเป็น `NULL`)

---

## 7. การป้องกัน Foreign Key Violation เชิงตรรกะ (Business Validation)

### ปัญหาเมื่อลบ Category ที่มีสินค้าผูกอยู่
หากในตาราง `products` มีคอลัมน์ `category_id` ที่ผูก Foreign Key ไว้:
* ถ้าเราสั่ง `$category->delete()` ทันที ฐานข้อมูล MySQL จะปฏิเสธและโยน Exception:
  `SQLSTATE[23000]: Integrity constraint violation: 1451 Cannot delete or update a parent row`
* ผลลัพธ์: ผู้ใช้งานจะเจอกับหน้าจอ Error 500 ซึ่งไม่เป็นมิตรและดูไม่เป็นมืออาชีพ

### วิธีปฏิบัติที่ถูกต้อง (Business Logic Check):
ตรวจสอบจำนวนสินค้าที่ผูกอยู่ก่อนเสมอ หากพบว่ายังมีสินค้าอยู่ ให้ตอบกลับเป็น Error ที่เข้าใจง่าย:
```php
public function destroy(Category $category)
{
    if ($category->products()->exists()) {
        return response()->json([
            'success' => false,
            'message' => 'ไม่สามารถลบหมวดหมู่นี้ได้เนื่องจากยังมีสินค้าอยู่ในหมวดหมู่ กรุณาย้ายหรือลบสินค้าก่อน'
        ], 422);
    }

    $category->delete();

    return response()->json([
        'success' => true,
        'message' => 'ลบหมวดหมู่เรียบร้อยแล้ว'
    ]);
}
```

---

## 8. โครงสร้าง Middleware ใน Laravel 12 (ไม่มี Kernel.php แล้ว)

### ความเปลี่ยนแปลงใน Laravel 11 และ 12
ในเวอร์ชันก่อนหน้า (Laravel 10 ลงไป) การลงทะเบียน Middleware จะทำในไฟล์ `app/Http/Kernel.php`
แต่ตั้งแต่ Laravel 11 เป็นต้นมา ไฟล์ `Kernel.php` ถูกตัดออก เพื่อให้โครงสร้างโปรเจกต์กระชับขึ้น

### วิธีการลงทะเบียน Middleware Alias ใน Laravel 12:
เปิดไฟล์ [**`bootstrap/app.php`**](file:///C:/xampp/htdocs/shop_backend/bootstrap/app.php) แล้วกำหนดผ่าน Callback `withMiddleware`:
```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // ลงทะเบียน Alias สำหรับเรียกใช้ใน Route เช่น ->middleware('admin')
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
```

---

## 9. แนวคำถามสัมภาษณ์ที่พบบ่อย (Interview Q&A)

| คำถาม | แนวทางการตอบเพื่อสร้างความประทับใจ |
| :--- | :--- |
| **Q: ทำไมการสร้าง Order ต้องใช้ Database Transaction?** | **A:** เพราะการสั่งซื้อประกอบด้วยหลายขั้นตอนย่อย (สร้าง Order, สร้าง Items, ตัด Stock) หากขั้นตอนใดขั้นตอนหนึ่งล้มเหลว Transaction จะทำการ Rollback ข้อมูลทั้งหมดกลับ เพื่อป้องกันไม่ให้เกิดความไม่สอดคล้องของข้อมูล (Data Inconsistency) เช่น มี Order เกิดขึ้นแต่สต็อกไม่ถูกตัด |
| **Q: จะป้องกันปัญหาสินค้าถูกซื้อพร้อมกันจนสต็อกติดลบ (Overselling) ได้อย่างไร?** | **A:** ใช้ Pessimistic Locking ผ่าน `lockForUpdate()` ภายใน Database Transaction ซึ่งจะสั่ง Row Lock ในระดับ Database Engine ทำให้ Request อื่นต้องรอจนกว่าธุรกรรมแรกจะประมวลผลเสร็จสิ้น |
| **Q: ข้อดีของการใช้ Laravel API Resource คืออะไร?** | **A:** ช่วยทำ Data Transformation เพื่อแยกโครงสร้างตารางฐานข้อมูลออกจากข้อมูลที่เปิดเผยสู่ภายนอก (Data Abstraction), ป้องกันข้อมูลสำคัญรั่วไหล เช่น password หรือ deleted_at, และช่วยให้ API Response มีโครงสร้างที่แน่นอนสำหรับฝั่ง Mobile/Frontend |
| **Q: ทำไมต้องบันทึกราคาในตาราง `order_items` ทั้งที่มีราคาใน `products` อยู่แล้ว?** | **A:** เพื่อทำ Historical Price Snapshot เนื่องจากราคาสินค้าในตาราง products สามารถเปลี่ยนแปลงได้ตามกาลเวลา การเก็บราคา ณ ขณะสั่งซื้อทำให้ประวัติคำสั่งซื้อและยอดคำนวณในอดีตถูกต้องเสมอ |

---

> 💡 **สรุปคำแนะนำ:** เมื่อเข้าใจหลักการทั้ง 8 ข้อนี้แล้ว คุณจะไม่ใช่แค่เขียนโค้ดตามโจทย์ได้ แต่จะเข้าใจ "เหตุผลทางสถาปัตยกรรม" (Architectural Rationale) ซึ่งเป็นหัวใจสำคัญของการเป็น Full Stack Developer มืออาชีพครับ!
