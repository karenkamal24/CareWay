# Patient API Documentation

## نظرة عامة / Overview

هذا التوثيق يشرح جميع الـ API endpoints المتعلقة بالمريض (Patient).

This documentation explains all Patient-related API endpoints.

---

## Authentication

جميع الـ endpoints تحتاج إلى **Bearer Token** في الـ header:
All endpoints require **Bearer Token** in the header:

```
Authorization: Bearer {your_token}


## Base URL

```
http://your-domain.com/api
```

---

## Endpoints
### 3. إرسال استبيان / Submit Survey

**POST** `/patient/survey`

#### Request Body

يمكن إرسال أي من الحقول التالية أو جميعها:
You can send any of the following fields or all of them:

```json
{
  "habits": {
    "smoking": "No",
    "caffeine": "2 cups per day",
    "exercise": "3 times per week",
    "sleep_hours": 8,
    "notes": "Regular exercise routine"
  },
  "diseases": [
    {
      "disease_name": "Diabetes",
      "status": "chronic"
    },
    {
      "disease_name": "Hypertension",
      "status": "active"
    }
  ],
  "medications": [
    {
      "medication_name": "Aspirin",
      "dose": "100mg",
      "frequency": "Once daily",
      "duration": "7 days",
      "start_date": "2024-01-01",
      "end_date": "2024-01-08",
      "patient_notes": "Take with food"
    },
    {
      "medication_name": "Paracetamol",
      "dose": "500mg",
      "frequency": "Twice daily",
      "duration": "5 days"
    }
  ],
  "attachments": [
    {
      "type": "xray",
      "file": "[FILE_UPLOAD]",
      "description": "Chest X-ray"
    }
  ]
}
```

#### Fields

**Habits:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `habits.smoking` | string | No | عادة التدخين |
| `habits.caffeine` | string | No | استهلاك الكافيين |
| `habits.exercise` | string | No | التمارين الرياضية |
| `habits.sleep_hours` | integer | No | ساعات النوم (0-24) |
| `habits.notes` | string | No | ملاحظات إضافية |

**Diseases:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `diseases[].disease_name` | string | Yes* | اسم المرض |
| `diseases[].status` | string | No | الحالة: `active`, `chronic`, `resolved` |

**Medications:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `medications[].medication_name` | string | Yes* | اسم الدواء |
| `medications[].dose` | string | No | الجرعة |
| `medications[].frequency` | string | No | التكرار |
| `medications[].duration` | string | No | المدة |
| `medications[].start_date` | date | No | تاريخ البدء (Y-m-d) |
| `medications[].end_date` | date | No | تاريخ الانتهاء (Y-m-d) |
| `medications[].patient_notes` | string | No | ملاحظات المريض |

**Attachments:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `attachments[].type` | string | Yes* | نوع المرفق |
| `attachments[].file` | file | Yes* | الملف (jpeg, jpg, png, pdf, max 10MB) |
| `attachments[].description` | string | No | وصف المرفق |

\* Required if the parent field is provided

#### Response (201 Created)

```json
{
  "success": true,
  "message": "Survey submitted successfully"
}
```

---

### 4. جلب بيانات الاستبيان / Get Survey Data

**GET** `/patient/survey`

#### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "habits": {
      "id": 1,
      "patient_id": 1,
      "smoking": "No",
      "caffeine": "2 cups per day",
      "exercise": "3 times per week",
      "sleep_hours": 8,
      "notes": "Regular exercise routine",
      "created_at": "2024-01-01T10:00:00.000000Z",
      "updated_at": "2024-01-01T10:00:00.000000Z"
    },
    "diseases": [
      {
        "id": 1,
        "patient_id": 1,
        "disease_name": "Diabetes",
        "status": "chronic",
        "source": "patient",
        "created_at": "2024-01-01T10:00:00.000000Z",
        "updated_at": "2024-01-01T10:00:00.000000Z"
      }
    ],
    "medications": [
      {
        "id": 1,
        "patient_id": 1,
        "medication_name": "Aspirin",
        "dose": "100mg",
        "frequency": "Once daily",
        "duration": "7 days",
        "start_date": "2024-01-01",
        "end_date": "2024-01-08",
        "patient_notes": "Take with food",
        "is_active": true,
        "source": "patient",
        "created_at": "2024-01-01T10:00:00.000000Z",
        "updated_at": "2024-01-01T10:00:00.000000Z"
      }
    ],
    "attachments": [
      {
        "id": 1,
        "type": "xray",
        "file_url": "http://your-domain.com/storage/patient_attachments/...",
        "description": "Chest X-ray",
        "created_at": "2024-01-01T10:00:00.000000Z"
      }
    ]
  }
}
```

---

### 5. جلب جميع الزيارات / Get All Visits

**GET** `/patient/visits`

#### Response (200 OK)

```json
{
  "success": true,
  "visits": [
    {
      "id": 11,
      "visit_date": "2025-11-28 17:28",
      "doctor": {
        "id": 3,
        "name": "Dr. Ahmed Ali"
      }
    },
    {
      "id": 10,
      "visit_date": "2025-11-26 20:16",
      "doctor": {
        "id": 3,
        "name": "Dr. Ahmed Ali"
      }
    }
  ]
}
```

#### Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | معرف الزيارة (يمكن استخدامه لتحميل التقرير) |
| `visit_date` | string\|null | تاريخ الزيارة (Y-m-d H:i) أو null |
| `doctor.id` | integer | معرف الطبيب |
| `doctor.name` | string | اسم الطبيب |

---

### 6. تحميل تقرير الزيارة / Download Visit Report

**GET** `/patient/visits/{visitId}/report`

#### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `visitId` | integer | Yes | معرف الزيارة (يمكن الحصول عليه من endpoint جلب الزيارات) |

#### Response (200 OK)

Returns a JSON response with PDF download URL.

```json
{
  "success": true,
  "message": "Visit report generated successfully",
  "pdf_url": "http://your-domain.com/storage/pdf_reports/visit_reports/Visit_Report_11_Dr_Ahmed_Ali_Patient_Name_2025-11-28.pdf",
  "download_url": "http://your-domain.com/storage/pdf_reports/visit_reports/Visit_Report_11_Dr_Ahmed_Ali_Patient_Name_2025-11-28.pdf",
  "file_name": "Visit_Report_11_Dr_Ahmed_Ali_Patient_Name_2025-11-28.pdf",
  "visit_id": 11
}
```

#### Fields

| Field | Type | Description |
|-------|------|-------------|
| `success` | boolean | حالة العملية |
| `message` | string | رسالة التأكيد |
| `pdf_url` | string | رابط تحميل PDF |
| `download_url` | string | رابط تحميل PDF (نفس `pdf_url`) |
| `file_name` | string | اسم الملف |
| `visit_id` | integer | معرف الزيارة |

#### Error Responses

**404 Not Found** - Visit not found:
```json
{
  "success": false,
  "message": "Visit not found or you do not have access to this visit"
}
```

**404 Not Found** - Doctor not found for visit:
```json
{
  "success": false,
  "message": "Doctor not found for this visit",
  "debug": {
    "visit_id": 10,
    "doctor_id": 3,
    "patient_id": 8
  }
}
```

**404 Not Found** - Visit has no associated doctor:
```json
{
  "success": false,
  "message": "This visit does not have an associated doctor"
}
```

---

### 7. جلب جميع البيانات الطبية / Get All Medical Data

**GET** `/patient/medical-data`

Endpoint لجلب جميع البيانات الطبية للمريض بشكل منظم (الأمراض، الأدوية، العادات، المرفقات).

Endpoint to get all patient medical data in an organized format (diseases, medications, habits, attachments).

#### Response (200 OK)

```json
{
  "success": true,
  "data": {
    "diseases": [
      {
        "id": 1,
        "patient_id": 1,
        "disease_name": "Diabetes",
        "status": "active",
        "source": "patient",
        "created_at": "2024-01-01T10:00:00.000000Z",
        "updated_at": "2024-01-01T10:00:00.000000Z"
      },
      {
        "id": 2,
        "patient_id": 1,
        "disease_name": "Hypertension",
        "status": "chronic",
        "source": "doctor",
        "created_at": "2024-01-02T10:00:00.000000Z",
        "updated_at": "2024-01-02T10:00:00.000000Z"
      }
    ],
    "medications": [
      {
        "id": 1,
        "medication_name": "Aspirin",
        "dose": "100mg",
        "frequency": "Once daily",
        "duration": "7 days",
        "is_active": true,
        "source": "patient",
        "start_date": "2024-01-01",
        "end_date": "2024-01-08",
        "doctor_notes": null,
        "patient_notes": "Take with food",
        "doctor": {
          "id": 3,
          "name": "Dr. Ahmed Ali"
        },
        "visit_id": null,
        "created_at": "2024-01-01T10:00:00.000000Z",
        "updated_at": "2024-01-01T10:00:00.000000Z"
      }
    ],
    "habits": {
      "id": 1,
      "patient_id": 1,
      "smoking": "No",
      "caffeine": "2 cups per day",
      "exercise": "3 times per week",
      "sleep_hours": 8,
      "notes": "Regular exercise routine",
      "created_at": "2024-01-01T10:00:00.000000Z",
      "updated_at": "2024-01-01T10:00:00.000000Z"
    },
    "attachments": [
      {
        "id": 1,
        "type": "xray",
        "file_url": "http://your-domain.com/storage/patient_attachments/...",
        "description": "Chest X-ray",
        "source": "patient",
        "created_at": "2024-01-01T10:00:00.000000Z",
        "updated_at": "2024-01-01T10:00:00.000000Z"
      }
    ]
  }
}
```

#### Fields

**Diseases:**
| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | معرف المرض |
| `disease_name` | string | اسم المرض |
| `status` | string | الحالة: `active`, `chronic`, `resolved` |
| `source` | string | المصدر: `doctor`, `patient`, `external` |

**Medications:**
| Field | Type | Description |
|-------|------|-------------|
| `id` | integer | معرف الدواء |
| `medication_name` | string | اسم الدواء |
| `is_active` | boolean | هل الدواء نشط أم متوقف |
| `doctor` | object\|null | معلومات الطبيب (إن وجد) |
| `visit_id` | integer\|null | معرف الزيارة (إن وجد) |

---

### 8. تعيين المرض كمحلول / Mark Disease as Resolved

**PUT** `/patient/diseases/{id}/resolve`

Endpoint لتعيين حالة المرض إلى `resolved`.

Endpoint to mark a disease status as `resolved`.

#### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | معرف المرض |

#### Response (200 OK)

```json
{
  "success": true,
  "message": "Disease marked as resolved successfully",
  "disease": {
    "id": 1,
    "patient_id": 1,
    "disease_name": "Diabetes",
    "status": "resolved",
    "source": "patient",
    "created_at": "2024-01-01T10:00:00.000000Z",
    "updated_at": "2024-01-01T10:00:00.000000Z"
  }
}
```

#### Error Responses

**404 Not Found** - Disease not found:
```json
{
  "success": false,
  "message": "Disease not found or you do not have access to this disease"
}
```

---

### 9. إيقاف الدواء / Stop Medication

**PUT** `/patient/medications/{id}/stop`

Endpoint لإيقاف الدواء (تعيين `is_active` إلى `false`).

Endpoint to stop a medication (set `is_active` to `false`).

#### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | معرف الدواء |

#### Response (200 OK)

```json
{
  "success": true,
  "message": "Medication stopped successfully",
  "medication": {
    "id": 1,
    "patient_id": 1,
    "medication_name": "Aspirin",
    "dose": "100mg",
    "frequency": "Once daily",
    "is_active": false,
    "source": "patient",
    "created_at": "2024-01-01T10:00:00.000000Z",
    "updated_at": "2024-01-01T10:00:00.000000Z"
  }
}
```

#### Error Responses

**404 Not Found** - Medication not found:
```json
{
  "success": false,
  "message": "Medication not found or you do not have access to this medication"
}
```

---

## Appointment Booking Restriction

### حجز موعد / Book Appointment

**POST** `/appointments`

#### Important Note

المستخدم **لا يمكنه** الحجز إذا كان لديه appointment نشط (scheduled أو completed).

User **cannot** book if they have an active appointment (scheduled or completed).

#### Error Response (400 Bad Request)

```json
{
  "error": "You already have an active appointment. Please complete or cancel your current appointment before booking a new one."
}
```

---

## Error Responses

### 401 Unauthorized

```json
{
  "error": "Authentication required"
}
```

### 422 Validation Error

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "medication_name": [
      "The medication name field is required."
    ]
  }
}
```

### 500 Server Error

```json
{
  "success": false,
  "error": "Error message"
}
```

---


#### Submit Survey
```bash
curl -X POST http://your-domain.com/api/patient/survey \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "habits[smoking]=No" \
  -F "habits[caffeine]=2 cups per day" \
  -F "diseases[0][disease_name]=Diabetes" \
  -F "diseases[0][status]=chronic" \
  -F "medications[0][medication_name]=Aspirin" \
  -F "medications[0][dose]=100mg" \
  -F "medications[0][frequency]=Once daily" \
  -F "attachments[0][type]=xray" \
  -F "attachments[0][file]=@/path/to/file.jpg" \
  -F "attachments[0][description]=Chest X-ray"
```

#### Download Visit Report
```bash
curl -X GET http://your-domain.com/api/patient/visits/11/report \
  -H "Authorization: Bearer YOUR_TOKEN"
```


#### Get All Medical Data
```bash
curl -X GET http://your-domain.com/api/patient/medical-data \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Mark Disease as Resolved
```bash
curl -X PUT http://your-domain.com/api/patient/diseases/1/resolve \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### Stop Medication
```bash
curl -X PUT http://your-domain.com/api/patient/medications/1/stop \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---



