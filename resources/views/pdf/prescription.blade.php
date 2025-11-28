<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>روشتة طبية - Medical Prescription</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
            background: #fff;
        }

        .prescription-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #0056b3;
            padding-bottom: 15px;
        }

        .prescription-header h1 {
            font-size: 24px;
            color: #0056b3;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .prescription-header .date {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .patient-info, .doctor-info {
            flex: 1;
            padding: 0 15px;
        }

        .patient-info {
            border-left: 2px solid #0056b3;
        }

        .doctor-info {
            border-right: 2px solid #0056b3;
        }

        .info-section h3 {
            font-size: 14px;
            color: #0056b3;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .info-section p {
            margin: 5px 0;
            font-size: 12px;
        }

        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .section-title {
            background-color: #0056b3;
            color: #fff;
            padding: 10px 15px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
            border-radius: 3px;
        }

        .medications-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }

        .medications-table th {
            background-color: #0056b3;
            color: #fff;
            padding: 10px;
            text-align: right;
            font-weight: bold;
            border: 1px solid #003d82;
        }

        .medications-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: right;
        }

        .medications-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .habits-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-right: 3px solid #0056b3;
        }

        .habits-box p {
            margin: 8px 0;
            font-size: 12px;
        }

        .habits-box strong {
            color: #0056b3;
            margin-left: 10px;
        }

        .diagnosis-box {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            border-right: 3px solid #ffc107;
            margin-bottom: 20px;
        }

        .diagnosis-box h4 {
            color: #856404;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .diagnosis-box p {
            font-size: 12px;
            line-height: 1.8;
            color: #333;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
            padding: 20px 0;
        }

        .signature-box {
            text-align: center;
            width: 45%;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
            font-size: 11px;
        }

        @media print {
            body {
                padding: 10px;
            }
            .section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="prescription-header">
        <h1>روشتة طبية - Medical Prescription</h1>
        <div class="date">
            @php
                $visitDate = $visit->visit_date ?? $visit->created_at;
            @endphp
            تاريخ الزيارة / Visit Date: {{ $visitDate->format('Y-m-d') }}
            |
            الوقت / Time: {{ $visitDate->format('H:i') }}
        </div>
    </div>

    <div class="info-section">
        <div class="patient-info">
            <h3>معلومات المريض / Patient Information</h3>
            <p><strong>الاسم / Name:</strong> {{ $patient->name }}</p>
            <p><strong>البريد الإلكتروني / Email:</strong> {{ $patient->email }}</p>
            @if($patient->phone)
                <p><strong>الهاتف / Phone:</strong> {{ $patient->phone }}</p>
            @endif
            @if($patient->date_of_birth)
                <p><strong>تاريخ الميلاد / Date of Birth:</strong>
                    @if(is_string($patient->date_of_birth))
                        {{ $patient->date_of_birth }}
                    @else
                        {{ $patient->date_of_birth->format('Y-m-d') }}
                    @endif
                </p>
            @endif
        </div>

        <div class="doctor-info">
            <h3>معلومات الطبيب / Doctor Information</h3>
            <p><strong>اسم الطبيب / Doctor Name:</strong> {{ $doctor->name ?? 'N/A' }}</p>
            @if($doctor->specialization)
                <p><strong>التخصص / Specialization:</strong> {{ $doctor->specialization }}</p>
            @endif
            @if($doctor->department)
                <p><strong>القسم / Department:</strong> {{ $doctor->department->name ?? 'N/A' }}</p>
            @endif
        </div>
    </div>

    @if($visit->diagnosis)
    <div class="section">
        <div class="diagnosis-box">
            <h4>التشخيص / Diagnosis</h4>
            <p>{{ $visit->diagnosis }}</p>
        </div>
    </div>
    @endif

    @if($medications->count() > 0)
    <div class="section">
        <div class="section-title">الأدوية الموصوفة / Prescribed Medications</div>
        <table class="medications-table">
            <thead>
                <tr>
                    <th style="width: 25%;">اسم الدواء / Medication Name</th>
                    <th style="width: 15%;">الجرعة / Dose</th>
                    <th style="width: 20%;">التكرار / Frequency</th>
                    <th style="width: 15%;">المدة / Duration</th>
                    <th style="width: 25%;">ملاحظات / Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($medications as $medication)
                    <tr>
                        <td><strong>{{ $medication->medication_name }}</strong></td>
                        <td>{{ $medication->dose ?? '---' }}</td>
                        <td>{{ $medication->frequency ?? '---' }}</td>
                        <td>{{ $medication->duration ?? '---' }}</td>
                        <td>{{ $medication->doctor_notes ?? '---' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($habits)
    <div class="section">
        <div class="section-title">العادات الصحية / Health Habits</div>
        <div class="habits-box">
            @if($habits->smoking)
                <p><strong>التدخين / Smoking:</strong> {{ $habits->smoking }}</p>
            @endif
            @if($habits->caffeine)
                <p><strong>الكافيين / Caffeine:</strong> {{ $habits->caffeine }}</p>
            @endif
            @if($habits->exercise)
                <p><strong>التمرين / Exercise:</strong> {{ $habits->exercise }}</p>
            @endif
            @if($habits->sleep_hours)
                <p><strong>ساعات النوم / Sleep Hours:</strong> {{ $habits->sleep_hours }} ساعة / hours</p>
            @endif
            @if($habits->notes)
                <p><strong>ملاحظات إضافية / Additional Notes:</strong> {{ $habits->notes }}</p>
            @endif
        </div>
    </div>
    @endif

    @if($visit->notes)
    <div class="section">
        <div class="section-title">ملاحظات إضافية / Additional Notes</div>
        <div class="habits-box">
            <p>{{ $visit->notes }}</p>
        </div>
    </div>
    @endif

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">
                توقيع الطبيب / Doctor Signature
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                توقيع المريض / Patient Signature
            </div>
        </div>
    </div>

    <div class="footer">
        <p>تم إنشاء هذه الروشتة تلقائياً من النظام الطبي / This prescription was automatically generated by the medical system</p>
        <p>© {{ date('Y') }} Medical System. All rights reserved.</p>
    </div>
</body>
</html>

