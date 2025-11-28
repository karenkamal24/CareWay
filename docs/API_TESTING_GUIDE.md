# دليل اختبار API / API Testing Guide

## نظرة عامة / Overview

هذا الدليل يشرح كيفية اختبار Patient API endpoints.
This guide explains how to test Patient API endpoints.

---

## المتطلبات / Requirements

- PHP 8.1+
- Laravel 10+
- PHPUnit
- Postman (اختياري / Optional)

---

## تشغيل الاختبارات / Running Tests

### تشغيل جميع الاختبارات
### Run All Tests

```bash
php artisan test
```

### تشغيل اختبارات Patient API فقط
### Run Patient API Tests Only

```bash
php artisan test --filter PatientApiTest
```

### تشغيل اختبار محدد
### Run Specific Test

```bash
php artisan test --filter test_store_medication
```

---

## الاختبارات المتوفرة / Available Tests

### 1. test_store_medication
اختبار تسجيل دواء جديد
Test registering a new medication

### 2. test_get_medications
اختبار جلب جميع الأدوية
Test getting all medications

### 3. test_submit_survey_habits
اختبار إرسال استبيان (عادات فقط)
Test submitting survey (habits only)

### 4. test_submit_survey_diseases
اختبار إرسال استبيان (أمراض)
Test submitting survey (diseases)

### 5. test_submit_survey_with_attachments
اختبار إرسال استبيان مع مرفقات
Test submitting survey with attachments

### 6. test_get_survey
اختبار جلب بيانات الاستبيان
Test getting survey data

### 7. test_get_visits
اختبار جلب جميع الزيارات
Test getting all visits

### 8. test_download_visit_report
اختبار تحميل تقرير الزيارة
Test downloading visit report

### 9. test_download_visit_report_no_visits
اختبار تحميل تقرير بدون زيارات
Test downloading report with no visits

### 10. test_medications_requires_authentication
اختبار أن الـ endpoint يتطلب authentication
Test that endpoint requires authentication

### 11. test_cannot_book_appointment_if_exists
اختبار منع الحجز إذا كان هناك appointment نشط
Test preventing booking if active appointment exists

---

## استخدام Postman / Using Postman

### 1. استيراد Collection
### Import Collection

1. افتح Postman
2. اضغط على Import
3. اختر ملف `docs/PATIENT_API_POSTMAN_COLLECTION.json`
4. Collection سيتم استيرادها تلقائياً

### 2. إعداد المتغيرات
### Setup Variables

1. افتح Collection Settings
2. في تبويب Variables، قم بتعديل:
   - `base_url`: عنوان الـ API الخاص بك (مثال: `http://localhost:8000/api`)
   - `auth_token`: سيتم تعبئته تلقائياً بعد تسجيل الدخول

### 3. تسجيل الدخول
### Login

1. افتح request "Login" في مجلد "Authentication"
2. عدّل بيانات الدخول إذا لزم الأمر
3. اضغط Send
4. الـ token سيتم حفظه تلقائياً في المتغيرات

### 4. اختبار الـ Endpoints
### Test Endpoints

الآن يمكنك اختبار أي endpoint من المجلدات:
- Medications
- Survey
- Visits
- Appointments

---

## أمثلة على الاستخدام / Usage Examples

### باستخدام cURL / Using cURL

#### تسجيل دواء
#### Register Medication

```bash
curl -X POST http://localhost:8000/api/patient/medications \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "medication_name": "Aspirin",
    "dose": "100mg",
    "frequency": "Once daily"
  }'
```

#### إرسال استبيان
#### Submit Survey

```bash
curl -X POST http://localhost:8000/api/patient/survey \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "habits[smoking]=No" \
  -F "habits[caffeine]=2 cups per day" \
  -F "diseases[0][disease_name]=Diabetes" \
  -F "diseases[0][status]=chronic"
```

#### تحميل تقرير
#### Download Report

```bash
curl -X GET http://localhost:8000/api/patient/visits/1/report \
  -H "Authorization: Bearer YOUR_TOKEN" \
  --output visit_report.pdf
```

---

## استكشاف الأخطاء / Troubleshooting

### خطأ 401 Unauthorized
- تأكد من إرسال Bearer Token في الـ header
- تأكد من أن الـ token صالح وغير منتهي

### خطأ 422 Validation Error
- تحقق من جميع الحقول المطلوبة
- تأكد من صحة تنسيق البيانات

### خطأ 404 Not Found
- تأكد من صحة الـ URL
- تأكد من وجود البيانات المطلوبة (مثل doctor_id)

### خطأ 500 Server Error
- تحقق من logs في `storage/logs/laravel.log`
- تأكد من إعدادات قاعدة البيانات

---

## ملاحظات مهمة / Important Notes

1. **Authentication**: جميع الـ endpoints تحتاج Bearer Token
2. **File Uploads**: الملفات يجب أن تكون أقل من 10MB
3. **Date Format**: التواريخ بصيغة `Y-m-d` (YYYY-MM-DD)
4. **File Types**: jpeg, jpg, png, pdf فقط

---

## الدعم / Support

للمساعدة، راجع ملف `docs/API_PATIENT_ENDPOINTS.md` للتوثيق الكامل.
For help, see `docs/API_PATIENT_ENDPOINTS.md` for full documentation.

