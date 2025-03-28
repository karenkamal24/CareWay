<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Lab Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            padding: 20px;
            /* background-color: #f9f9f9; */
            background-color: white;
            color: #333;
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .report-header {
            text-align: center;
            /* margin-bottom: 30px; */
        }

        .report-header img {
            width: 100px;
            height: auto;
        }

        .report-header h2 {
            margin: 10px 0;
            font-size: 24px;
            color: #34495e;
        }

        .report-details {
            /* background-color: #fff; */
            padding: 20px;
            border-radius: 8px;
            /* box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); */
            /* margin-bottom: 20px; */
        }

        .report-details p {
            margin: 10px 0;
            font-size: 16px;
        }

        .report-details strong {
            color: #2c3e50;
        }

        .notes {
            /* background-color: #fff; */
            padding: 0px 20px;
            border-radius: 8px;
            /* box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); */
        }

        .notes h3 {
            color: #34495e;
            margin-bottom: 10px;
        }

        .notes p {
            font-size: 16px;
            line-height: 1.6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: transparent;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
            border: none;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border: none;
        }

        th {
            /* background-color: #f2f2f2; */
        }

        .Collection {
            display: flex;
            justify-content: space-between;
        }

        .report-header .Logo {
            display: flex;
            justify-content: flex-end;
        }
    </style>
</head>

<body>

    <div class="report-header">
        
        {{-- <img src="{{ public_path('images/logo.png') }}" alt="شعار المختبر" class="logo"> --}}
        <h2>Lab Report</h2>

    </div>

    <div class="report-details">
        <div class="Collection">
            <div>
                <p><strong>Patient Name:</strong> {{ $record->patient->name ?? 'N/A' }}</p>
                <p><strong>Doctor:</strong> {{ $record->doctor->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p><strong>Test Date:</strong> {{ $record->test_date }}</p>
                <p><strong>Result Date:</strong> {{ $record->result_date }}</p>
            </div>
        </div>
    </div>

    <div class="report-details">
        <h3>Tests</h3>
        <table>
            <thead>
                <tr>
                    <th>Test Name</th>
                    <th>Results</th>
                    <th>Ranges</th>
                </tr>
            </thead>
            <tbody>
                @foreach($record->tests as $test)
                <tr>
                    <td>{{ $test['test'] }}</td>
                    <td>
                        @foreach($test['results'] as $result)
                        <p><strong>Result:</strong> {{ $result['result'] }} {{ $result['unit'] }}</p>
                        @endforeach
                    </td>
                    <td>
                        @foreach($test['ranges'] as $range)
                        <p><strong>Range:</strong> {{ $range['test'] }} - {{ $range['description'] }}</p>
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="notes">
        <h3>Notes</h3>
        <p>{{ $record->note ?? 'No additional notes' }}</p>
    </div>

</body>

</html>












