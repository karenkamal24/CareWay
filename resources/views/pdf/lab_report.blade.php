<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lab Report</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; padding: 20px; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 10px; text-align: left; }
    </style>
</head>
<body>

    <h1>Lab Report</h1>

    <p><strong>Patient Name:</strong> {{ $record->patient->name ?? 'N/A' }}</p>
    <p><strong>Test Name:</strong> {{ $record->test_name }}</p>
    <p><strong>Result:</strong> {{ $record->result }}</p>
    <p><strong>Unit:</strong> {{ $record->unit ?? '-' }}</p>
    <p><strong>Range:</strong> {{ $record->range ?? '-' }}</p>
    <p><strong>Test Date:</strong> {{ $record->test_date }}</p>
    <p><strong>Result Date:</strong> {{ $record->result_date }}</p>
    <p><strong>Doctor:</strong> {{ $record->doctor->name ?? 'N/A' }}</p>

    <h3>Notes</h3>
    <p>{{ $record->note ?? 'No additional notes' }}</p>

</body>
</html>