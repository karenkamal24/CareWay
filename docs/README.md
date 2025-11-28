# Patient API Documentation

## الملفات المتوفرة / Available Files

### 1. API_PATIENT_ENDPOINTS.md
التوثيق الكامل لجميع الـ API endpoints مع أمثلة
Complete documentation for all API endpoints with examples

### 2. API_TESTING_GUIDE.md
دليل شامل لاختبار الـ API
Comprehensive guide for testing the API

### 3. PATIENT_API_POSTMAN_COLLECTION.json
Postman Collection جاهزة للاستخدام
Ready-to-use Postman Collection

---

## البدء السريع / Quick Start

### 1. قراءة التوثيق
### Read Documentation

```bash
cat docs/API_PATIENT_ENDPOINTS.md
```

### 2. تشغيل الاختبارات
### Run Tests

```bash
php artisan test --filter PatientApiTest
```

### 3. استيراد Postman Collection

1. افتح Postman
2. Import → اختر `docs/PATIENT_API_POSTMAN_COLLECTION.json`
3. ابدأ الاختبار!

---

## الـ Endpoints الرئيسية / Main Endpoints

### Medications
- `POST /api/patient/medications` - تسجيل دواء
- `GET /api/patient/medications` - جلب الأدوية

### Survey
- `POST /api/patient/survey` - إرسال استبيان
- `GET /api/patient/survey` - جلب بيانات الاستبيان

### Visits
- `GET /api/patient/visits` - جلب جميع الزيارات
- `GET /api/patient/visits/{doctorId}/report` - تحميل تقرير PDF

### Appointments
- `POST /api/appointments` - حجز موعد (مع قيود)

---

## ملاحظات / Notes

- جميع الـ endpoints تحتاج Bearer Token
- راجع `API_PATIENT_ENDPOINTS.md` للتفاصيل الكاملة

---

## الدعم / Support

للمزيد من المعلومات، راجع الملفات في مجلد `docs/`

