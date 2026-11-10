# Knowledge.md

## สรุปความรู้ที่ได้รับจากการฝึกงาน (วันแรก -- ปัจจุบัน)

เอกสารนี้รวบรวมความรู้และทักษะจากการฝึกปฏิบัติงานด้านการพัฒนาเว็บแอปพลิเคชันด้วย
Laravel การเชื่อมต่อ API กับ Flutter และการใช้ AI Coding Agent
โดยเรียงตามลำดับการฝึกงาน

------------------------------------------------------------------------

## สัปดาห์ที่ 1: Laravel, Database, CRUD, Blade และ REST API

### วันที่ 1: พื้นฐานฐานข้อมูลและ CRUD ด้วย Laravel

1.  **Laravel Migration**
    -   ใช้ Migration สร้างและจัดการโครงสร้างตารางฐานข้อมูล
    -   กำหนดชื่อฟิลด์และชนิดข้อมูลให้เหมาะสมกับข้อมูลที่จัดเก็บ
    -   เข้าใจการทำงานของตาราง `users` และ `products`
2.  **CRUD (Create, Read, Update, Delete)**
    -   Create: เพิ่มข้อมูลลงฐานข้อมูล
    -   Read: อ่านและแสดงข้อมูล
    -   Update: แก้ไขข้อมูล
    -   Delete: ลบข้อมูล
3.  **Pagination**
    -   แบ่งข้อมูลออกเป็นหลายหน้า
    -   ช่วยลดจำนวนข้อมูลที่แสดงในแต่ละครั้งและทำให้ใช้งานสะดวกขึ้น
4.  **Soft Delete และ Restore**
    -   Soft Delete เป็นการทำเครื่องหมายว่าข้อมูลถูกลบ โดยยังเก็บข้อมูลไว้ในฐานข้อมูล
    -   Restore ใช้กู้คืนข้อมูลที่ถูก Soft Delete
5.  **Laravel Controller และ Route**
    -   ใช้ Route กำหนดเส้นทางการเรียกใช้งาน
    -   ใช้ Controller แยกการทำงานและจัดการคำขอจากผู้ใช้
    -   ฝึกสร้าง CRUD API สำหรับจัดการข้อมูล

### วันที่ 2: Laravel Blade, AJAX และ jQuery

1.  **Laravel Blade**
    -   ใช้ Blade Template สร้างหน้าแสดงรายการผู้ใช้งาน
    -   ส่งข้อมูลจาก Controller ไปยัง View ด้วย `compact()`
    -   แสดงข้อมูลที่ได้รับจาก Controller ภายในหน้าเว็บ
2.  **AJAX และ jQuery**
    -   ใช้ AJAX เรียกข้อมูลจาก Controller โดยไม่ต้องโหลดหน้าเว็บใหม่
    -   ใช้ jQuery จัดการเหตุการณ์และปรับปรุงข้อมูลบนหน้าเว็บ
    -   รับและจัดการข้อมูลในรูปแบบ JSON
3.  **การแก้ไขข้อมูลโดยไม่ Reload หน้าเว็บ**
    -   สร้างแบบฟอร์มแก้ไขข้อมูลผู้ใช้งาน
    -   ส่งข้อมูลเพื่อ Update ผ่าน AJAX/jQuery
    -   ปรับปรุงแถวข้อมูลในตารางหลังแก้ไขสำเร็จ โดยไม่ต้อง Reload หน้าเว็บ

### วันที่ 3: DataTables

1.  **DataTables**
    -   ใช้ DataTables แสดงข้อมูลในรูปแบบตาราง
    -   เพิ่มความสามารถในการค้นหา (Search)
    -   เรียงลำดับข้อมูล (Sorting)
    -   แบ่งหน้า (Pagination)
2.  **การแก้ไขข้อมูลผ่าน DataTable**
    -   เลือกข้อมูลจากตารางเพื่อแก้ไข
    -   ส่งคำขอแก้ไขข้อมูลผ่าน AJAX
    -   Reload ข้อมูลใน DataTable หลัง Update โดยไม่ต้อง Reload หน้าเว็บทั้งหมด
3.  **HTTP Request**
    -   GET ใช้เรียกดูข้อมูล
    -   POST ใช้ส่งข้อมูลหรือสร้างข้อมูล
    -   PUT ใช้แก้ไขข้อมูล

### วันที่ 4: การสร้าง REST API ด้วย Laravel

1.  **REST API**
    -   สร้าง API สำหรับจัดการข้อมูล Users และ Products
    -   แยก API Controller ไว้ภายใน `Controllers/Api/`
    -   ใช้ HTTP Methods เพื่อจัดการข้อมูลผ่าน API
2.  **Request Validation**
    -   ตรวจสอบข้อมูลที่ผู้ใช้ส่งมาก่อนนำไปประมวลผล
    -   กำหนดเงื่อนไขว่าข้อมูลใดจำเป็นหรือมีรูปแบบอย่างไร
3.  **JSON Response และ API Resource**
    -   รับและส่งข้อมูลผ่าน JSON
    -   ใช้ API Resource จัดรูปแบบข้อมูลก่อนส่งกลับไปยังผู้เรียก API
    -   ทำความเข้าใจการตอบกลับด้วย HTTP Status Code
4.  **API Pagination และการทดสอบ**
    -   แบ่งผลลัพธ์ API ออกเป็นหน้า
    -   ใช้ Postman ส่ง Request และตรวจสอบ Response

### วันที่ 5: Authentication ด้วย Laravel Sanctum

1.  **Laravel Sanctum**
    -   ใช้ Sanctum จัดการการยืนยันตัวตนด้วย Token
    -   เรียนรู้การสร้าง Token เมื่อเข้าสู่ระบบ
2.  **Login และ Logout**
    -   Login เพื่อตรวจสอบข้อมูลผู้ใช้และออก Token
    -   Logout เพื่อยกเลิก Token ที่ใช้งาน
3.  **การป้องกัน API**
    -   ใช้ Middleware `auth:sanctum` ป้องกัน API Endpoint
    -   กำหนดให้เฉพาะผู้ที่ผ่านการยืนยันตัวตนเข้าถึง API ที่กำหนดได้
4.  **Route::apiResource()**
    -   ทดลองใช้ `Route::apiResource()` เพื่อกำหนดเส้นทาง CRUD API
        ตามรูปแบบมาตรฐานของ Laravel

------------------------------------------------------------------------

## สัปดาห์ที่ 2: Flutter API Integration และ AI Coding Agent

### วันที่ 1: เริ่มต้น Flutter และเชื่อมต่อ Laravel API

1.  **Flutter Project และ Package**
    -   สร้าง Flutter Project
    -   ติดตั้ง Package ที่จำเป็นสำหรับการพัฒนาและเรียกใช้งาน API
2.  **HTTP Request**
    -   ใช้ Package `http` เรียก REST API
    -   ฝึกส่งคำขอแบบ GET, POST, PUT และ DELETE
3.  **JSON และ Model**
    -   แปลง JSON ที่ได้รับจาก API ให้เป็น Dart Model
    -   ใช้ `fromJson()` เพื่อสร้าง Object จากข้อมูล JSON
4.  **Sanctum Token Authentication**
    -   Login ผ่าน Laravel Sanctum
    -   ส่ง Token ใน Header แบบ Bearer Token เมื่อต้องเรียก API ที่มีการป้องกัน
    -   จัดเก็บ Token ไว้ในเครื่องเพื่อใช้ในการเรียก API ครั้งต่อไป
5.  **FutureBuilder**
    -   ใช้ `FutureBuilder` จัดการสถานะการโหลดข้อมูล
    -   แสดง Loading ระหว่างรอข้อมูล
    -   แสดง Error เมื่อเกิดข้อผิดพลาด
    -   แสดง Data เมื่อได้รับข้อมูลสำเร็จ

### วันที่ 2: CRUD และการจัดการข้อมูลใน Flutter

1.  **Users CRUD**
    -   แสดงรายการ Users จาก API
    -   เพิ่ม แก้ไข และลบข้อมูลผ่านหน้าจอ Flutter
    -   แสดงผลการทำงานหลังจากเรียก API
2.  **Validation Error**
    -   จัดการ Response เมื่อ Laravel ส่ง HTTP Status Code `422`
    -   นำข้อความ Validation Error มาแสดงให้ผู้ใช้ทราบ
3.  **Delete Confirmation**
    -   แสดง Confirm Dialog ก่อนลบข้อมูล
    -   ดำเนินการลบเมื่อผู้ใช้ยืนยัน
4.  **Products CRUD**
    -   ใช้ API ที่สร้างจาก `Route::apiResource('products', ...)`
    -   เชื่อมต่อหน้าจอ Flutter กับ API สำหรับจัดการ Products
5.  **Pagination และ Infinite Scroll**
    -   เรียกข้อมูลหน้าถัดไปด้วยพารามิเตอร์ เช่น `?page=2`
    -   ใช้ข้อมูล `meta.last_page` ตรวจสอบหน้าสุดท้าย
    -   โหลดข้อมูลเพิ่มเติมเมื่อผู้ใช้เลื่อนหน้าจอ
6.  **Profile และ Logout**
    -   เรียก `/api/profile` เพื่อแสดงข้อมูลผู้ใช้
    -   Logout และลบ Token ที่จัดเก็บไว้ในเครื่อง

### วันที่ 3: การใช้ AI Coding Agent ช่วยพัฒนาโปรแกรม

1.  **AI Coding Agent**
    -   ติดตั้งและทดลองใช้งาน Claude Code หรือ Coding Agent อื่น
    -   เรียนรู้การใช้ AI Agent เพื่อช่วยสำรวจ แก้ไข และพัฒนาโค้ดในโปรเจกต์
2.  **ไฟล์ CLAUDE.md**
    -   สร้าง `CLAUDE.md` เพื่อกำหนดข้อมูลและแนวทางการทำงานของ Agent
        ภายในโปรเจกต์
    -   ใช้ไฟล์ดังกล่าวเป็นบริบทให้ Agent เข้าใจโครงสร้างและข้อกำหนดของโปรเจกต์
3.  **Plan Mode**
    -   ใช้ Plan Mode เพื่อสำรวจและวางแผนก่อนเริ่มพัฒนาฟีเจอร์ใหม่
    -   พิจารณาขั้นตอนและไฟล์ที่ต้องแก้ไขก่อนลงมือเขียนโค้ด
4.  **Workflow การทำงานกับ Agent**
    -   Explore: สำรวจโครงสร้างและทำความเข้าใจโปรเจกต์
    -   Plan: วางแผนการพัฒนา
    -   Code: ลงมือแก้ไขหรือสร้างโค้ด
    -   Verify: ตรวจสอบและทดสอบผลลัพธ์
5.  **Prompt และเงื่อนไขการทำงาน**
    -   เขียน Prompt ให้ชัดเจน ระบุสิ่งที่ต้องการและข้อจำกัด
    -   กำหนดเงื่อนไขว่าแบบใดจึงถือว่างานเสร็จสมบูรณ์ (Acceptance Criteria)
6.  **Git สำหรับตรวจสอบและย้อนกลับงาน**
    -   ใช้ Git เป็นจุดบันทึกสถานะของโค้ดก่อนให้ Agent แก้ไข
    -   ใช้ `git diff` ตรวจสอบความเปลี่ยนแปลง
    -   ฝึกย้อนกลับการแก้ไขด้วยคำสั่ง Git เช่น `git checkout` ตามขอบเขตที่ต้องการ
    -   ตรวจสอบโค้ดที่ Agent สร้างและทำความเข้าใจการทำงานก่อนนำไปใช้

------------------------------------------------------------------------

## สรุปทักษะที่ได้รับ

### Backend Development

-   PHP และ Laravel Framework
-   Laravel Migration และ Eloquent ORM
-   Controller, Route และ REST API
-   CRUD และ Request Validation
-   JSON Response และ API Resource
-   Pagination และ Soft Delete
-   Authentication ด้วย Laravel Sanctum

### Frontend Development

-   Laravel Blade Template
-   JavaScript และ jQuery
-   AJAX และ DataTables
-   Flutter และ Dart
-   การสร้าง UI เพื่อจัดการข้อมูลผ่าน API

### API Integration

-   HTTP Methods: GET, POST, PUT และ DELETE
-   JSON Serialization และ Deserialization
-   Bearer Token Authentication
-   การจัดการ HTTP Status Code และ Validation Error
-   การแสดง Loading, Error และ Data
-   Pagination และ Infinite Scroll

### Development Tools และ Workflow

-   Visual Studio Code
-   Postman
-   Git และ GitHub
-   Claude Code หรือ AI Coding Agent
-   การเขียน Prompt และ CLAUDE.md
-   Workflow: Explore → Plan → Code → Verify
-   การตรวจสอบโค้ดด้วย `git diff`

------------------------------------------------------------------------

## สรุปภาพรวม

จากการฝึกงานตั้งแต่วันแรกจนถึงปัจจุบัน
ได้เรียนรู้กระบวนการพัฒนาเว็บแอปพลิเคชันตั้งแต่การออกแบบและจัดการฐานข้อมูล การพัฒนาระบบ
CRUD ด้วย Laravel การสร้าง REST API และระบบยืนยันตัวตนด้วย Sanctum ไปจนถึงการนำ
API มาเชื่อมต่อกับ Flutter เพื่อพัฒนาแอปพลิเคชันที่สามารถจัดการข้อมูลได้
นอกจากนี้ยังได้เรียนรู้การใช้ AI Coding Agent ช่วยพัฒนาโปรแกรมอย่างเป็นระบบ โดยใช้
Prompt, Plan Mode และ Git ในการควบคุมและตรวจสอบคุณภาพของโค้ด

> หมายเหตุ: เอกสารนี้สรุปจากหัวข้อ Practice และ Requirements ที่ระบุในการฝึกงาน
> ไม่ได้ยืนยันว่าทุกหัวข้อผ่านการทดสอบหรือเสร็จสมบูรณ์แล้ว
