# 🛒 Shop Backend (E-Commerce REST API)

ระบบ Backend และ RESTful API สำหรับร้านค้าออนไลน์ (E-Commerce) พัฒนาด้วย **Laravel 12** เพื่อทำหน้าที่เป็นศูนย์กลางข้อมูลและให้บริการ API แก่เว็บแอปพลิเคชันและโมบายล์แอปพลิเคชัน (เช่น Flutter)

---

## 📑 สารบัญเอกสาร (Documentation)

* 📖 **[คู่มือสถาปัตยกรรมและรายละเอียดโปรเจกต์ฉบับสมบูรณ์ (PROJECT_MANUAL.md)](PROJECT_MANUAL.md)**
  * แผนภาพสถาปัตยกรรมระบบ (System Architecture)
  * โครงสร้างฐานข้อมูลและ ER Diagram (Mermaid ERD)
  * รายละเอียดตารางและ Eloquent Models (`User`, `Category`, `Product`, `Order`, `OrderItem`)
  * ขั้นตอนการติดตั้งและการรัน Migration / Seeder
  * แนวทางการพัฒนา REST API และการเชื่อมต่อกับ Flutter
* 📝 **[บันทึกองค์ความรู้และเนื้อหาการฝึกปฏิบัติงาน (knowledge.md)](knowledge.md)**

---

## 🚀 เริ่มต้นใช้งานอย่างรวดเร็ว (Quick Start)

### 1. ติดตั้ง Dependencies
```bash
composer install
npm install
```

### 2. ตั้งค่าสภาพแวดล้อม
```bash
cp .env.example .env
php artisan key:generate
```

### 3. ตั้งค่าฐานข้อมูลและรัน Seeder
สร้างฐานข้อมูล `shop_db` ใน MySQL (XAMPP) หรือสลับไปใช้ SQLite ใน `.env` จากนั้นรัน:
```bash
php artisan migrate --seed
```

### 4. รัน Development Server
```bash
php artisan serve
```
ระบบจะพร้อมให้บริการที่ `http://127.0.0.1:8000`

---

## 🔑 บัญชีทดสอบเริ่มต้น (Seeded Accounts)

* **Admin:** `admin@shop.test` / รหัสผ่าน: `password123`
* **Customer:** `customer@shop.test` / รหัสผ่าน: `password123`
* **หมวดหมู่และสินค้า:** ระบบสร้างหมวดหมู่หลัก 3 รายการ และสุ่มสินค้าจำลอง 30 รายการโดยอัตโนมัติ

---

## 🛠️ เทคโนโลยีที่ใช้งาน (Tech Stack)
* **Framework:** Laravel 12.x
* **Language:** PHP 8.2+
* **Database:** MySQL / SQLite (Eloquent ORM)
* **Frontend Assets:** Vite 7.x, Tailwind CSS v4
