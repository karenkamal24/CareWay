# إعداد المساعد الذكي (AI Assistant)

## نظرة عامة
تم إنشاء مساعد ذكي لتحليل الأعراض واقتراح الأطباء المناسبين. يستخدم المساعد **Groq API** وهو مجاني تماماً وسريع جداً.

## الحصول على Groq API Key (مجاني)

1. اذهب إلى: https://console.groq.com/
2. سجل حساب جديد (مجاني تماماً)
3. بعد تسجيل الدخول، اذهب إلى **API Keys**
4. اضغط على **Create API Key**
5. انسخ الـ API Key

## إعداد المشروع

1. افتح ملف `.env`
2. أضف السطر التالي:
```env
GROQ_API_KEY=your_api_key_here
```

3. احفظ الملف

## ملاحظات مهمة

- **Groq API مجاني تماماً** مع حد معقول من الطلبات
- إذا لم تضيف الـ API Key، سيعمل النظام باستخدام البحث البسيط بالكلمات المفتاحية
- النظام يعمل بشكل جيد حتى بدون API Key (fallback mode)

## استخدام الـ API

### 1. اقتراح الأطباء بناءً على الأعراض

**Endpoint:** `POST /api/ai-assistant/suggest-doctors`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "symptoms": "أعاني من صداع شديد وألم في الرأس"
}
```

**Response:**
```json
{
    "success": true,
    "message": "تم العثور على 5 طبيب مناسب",
    "symptoms": "أعاني من صداع شديد وألم في الرأس",
    "suggested_departments": [
        {
            "id": 1,
            "name": "الأمراض العصبية",
            "description": "قسم متخصص في تشخيص وعلاج الأمراض العصبية",
            "image_url": null
        }
    ],
    "suggested_doctors": [
        {
            "id": 1,
            "name": "د. أحمد العتيبي",
            "specialization": "طبيب أعصاب",
            "department": "الأمراض العصبية",
            "department_id": 1,
            "price": 200,
            "degree": "MD",
            "rate": 4.5,
            "image_url": null,
            "description": "متخصص في طبيب أعصاب مع خبرة واسعة في مجال الأمراض العصبية",
            "phone": "01234567890"
        }
    ],
    "total_doctors": 1
}
```

### 2. الحصول على قائمة الأعراض الشائعة

**Endpoint:** `GET /api/ai-assistant/common-symptoms`

**Response:**
```json
{
    "success": true,
    "common_symptoms": [
        "صداع",
        "ألم في الصدر",
        "ضيق في التنفس",
        ...
    ]
}
```

## بدائل مجانية أخرى

إذا أردت استخدام بدائل أخرى:

1. **Hugging Face Inference API** - مجاني
2. **Ollama** (محلي) - مجاني تماماً
3. **Google Gemini API** - مجاني لحد معين

يمكن تعديل `SymptomAnalyzerService.php` لاستخدام أي من هذه البدائل.

