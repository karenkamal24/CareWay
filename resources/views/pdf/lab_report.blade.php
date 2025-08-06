<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير تحاليل طبية</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px auto;
            max-width: 800px;
            padding: 20px;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .header .logo {
            width: 80px;
            height: auto;
            margin-left: 20px;
        }

        .header .info {
            text-align: right;
            font-size: 0.9em;
            flex-grow: 1;
        }

        .header .info div {
            margin-bottom: 3px;
        }

        .header .date-time {
            text-align: left;
            font-size: 0.8em;
            color: #666;
            white-space: nowrap;
        }

        .patient-info {
            margin-bottom: 30px;
            font-size: 0.9em;
            text-align: right;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .section-title {
            font-size: 1.1em;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
            color: #0056b3;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 0.9em;
        }

        .results-table th,
        .results-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
            text-align: right;
        }

        .results-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: right;
        }

        .results-table .unit-col {
            text-align: center;
            width: 80px;
        }

        .results-table .ref-range-col {
            text-align: left;
            width: 200px;
        }

        .results-table .value-col {
            font-weight: bold;
        }

        .comments {
            font-size: 0.85em;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border-left: 3px solid #0056b3;
            text-align: right;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 50px;
            border-top: 1px solid #eee;
            padding-top: 10px;
            font-size: 0.8em;
        }

        .footer .reviewed-by strong {
            font-size: 1.1em;
            display: block;
            margin-top: 5px;
            color: #0056b3;
        }

        .footer .qr-code {
            width: 80px;
            height: 80px;
            border: 1px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7em;
            color: #999;
            margin-left: 20px;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="logo">
            <img src="{{ public_path('images/1.png') }}" alt="Company Logo" style="width: 100%;">
        </div>
        <div class="info">
            <div>اسم المريض: {{ $record->patient->name ?? '---' }}</div>
            <div>عمر المريض: {{ $record->age ?? '---' }}</div>
        </div>
        <div class="date-time">
            <div>تاريخ السحب: {{ $record->test_date ?? '---' }}</div>
            <div>تاريخ التقرير: {{ $record->result_date ?? '---' }}</div>
        </div>
    </div>

    <div class="patient-info">
        <div>طلب طبيب: {{ $record->doctor->name ?? '---' }}</div>
    </div>

    <div class="section-title">قسم التحاليل</div>

    <table class="results-table">
        <thead>
            <tr>
                <th>الاختبار</th>
                <th class="value-col">القيمة</th>
                <th class="unit-col">الوحدة</th>
                <th class="ref-range-col">المعدل الطبيعي</th>
            </tr>
        </thead>
        <tbody>
            @foreach($record->tests as $test)
                <tr>
                    <td>{{ $test['test'] ?? '---' }}</td>
                    <td class="value-col">
                        @foreach($test['results'] as $result)
                            {{ $result['result'] ?? '---' }}
                        @endforeach
                    </td>
                    <td class="unit-col">
                        @foreach($test['results'] as $result)
                            {{ $result['unit'] ?? '' }}
                        @endforeach
                    </td>
                    <td class="ref-range-col">
                        @foreach($test['ranges'] as $range)
                            {{ $range['description'] ?? '' }}<br>
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if(!empty($record->note))
    <div class="comments">
        <p>ملاحظات:</p>
        <p>{!! nl2br(e($record->note)) !!}</p>
    </div>
    @endif

    <div class="footer">
        <div class="reviewed-by">
            <div style="font-size: 0.7em; margin-top: 10px; color: #666;">
                طُبع في {{ now()->format('H:i d/m/Y') }}
            </div>
        </div>
    </div>

</body>
</html>
