# ezoService — مكتبة وخدمات تعليمية

منصة PHP + MySQL (مكتبة، بيع/شراء كتب، اختبارات، جامعة مفتوحة) مع لوحة إدارة وإرسال SMS عبر Twilio.

## المتطلبات
- PHP 8.x + MySQL/MariaDB (XAMPP)
- Composer

## التشغيل
1. انسخ المشروع إلى `htdocs/ezoService`
2. أنشئ قاعدة بيانات `azouz_bookstore` (حسب `includes/conect.php`):
   ```sql
   CREATE DATABASE azouz_bookstore CHARACTER SET utf8mb4;
   ```
   ثم استورد ملف `azouz_bookstore.sql`:
   - phpMyAdmin ← استيراد ← `azouz_bookstore.sql`
3. إعداد الاتصال في `includes/conect.php` و `admin/includes/temp/conect.php` (الوضع الافتراضي: `root` بدون كلمة سر)
4. تثبيت المكتبات:
   ```bash
   composer install
   ```
   (المجلد `vendor/` مستبعد من Git قصداً)
5. افتح: `http://localhost/ezoService/index.php`

## إرسال SMS (Twilio) — اختياري
صفحة `admin/send_sms.php` فقط تحتاجه. اضبط متغيرات البيئة:
```bash
TWILIO_SID=ACxxxxxxxxxxxxxxxx
TWILIO_TOKEN=xxxxxxxx
TWILIO_NUMBER=+249000000000
```
بدونها يعمل الموقع كاملاً ما عدا إرسال الرسائل.
