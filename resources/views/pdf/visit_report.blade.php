<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Visit Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            background-color: #f0f0f0;
            padding: 8px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .info-box {
            background-color: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Medical Visit Report</h1>
        <p>Visit Date: {{ $visit->visit_date ? $visit->visit_date->format('Y-m-d H:i') : 'N/A' }}</p>
    </div>

    <div class="section">
        <div class="section-title">Patient Information</div>
        <div class="info-box">
            <table>
                <tr>
                    <th>Name</th>
                    <td>{{ $patient->name }}</td>
                    <th>Email</th>
                    <td>{{ $patient->email }}</td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td>{{ $patient->phone ?? 'N/A' }}</td>
                    <th>Date of Birth</th>
                    <td>{{ $patient->date_of_birth ? $patient->date_of_birth->format('Y-m-d') : 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Doctor Information</div>
        <div class="info-box">
            <table>
                <tr>
                    <th>Doctor Name</th>
                    <td>{{ $doctor->name ?? 'N/A' }}</td>
                    <th>Specialization</th>
                    <td>{{ $doctor->specialization ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Visit Details</div>
        <div class="info-box">
            <table>
                <tr>
                    <th style="width: 20%;">Visit Date</th>
                    <td>{{ $visit->visit_date ? $visit->visit_date->format('Y-m-d H:i') : 'N/A' }}</td>
                </tr>
                @if($visit->symptoms)
                    <tr>
                        <th>Symptoms</th>
                        <td>{{ $visit->symptoms }}</td>
                    </tr>
                @endif
                @if($visit->diagnosis)
                    <tr>
                        <th>Diagnosis</th>
                        <td>{{ $visit->diagnosis }}</td>
                    </tr>
                @endif
                @if($visit->notes)
                    <tr>
                        <th>Notes</th>
                        <td>{{ $visit->notes }}</td>
                    </tr>
                @endif
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Medications Prescribed in This Visit</div>
        @php
            $visitMedications = collect();
            if ($visit->visit_date) {
                $visitMedications = \App\Models\PatientMedication::where('patient_id', $patient->id)
                    ->where('doctor_id', $visit->doctor_id)
                    ->whereDate('start_date', $visit->visit_date->format('Y-m-d'))
                    ->get();
            }
        @endphp
        @if($visitMedications->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Medication Name</th>
                        <th>Dose</th>
                        <th>Frequency</th>
                        <th>Duration</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($visitMedications as $medication)
                        <tr>
                            <td>{{ $medication->medication_name }}</td>
                            <td>{{ $medication->dose ?? 'N/A' }}</td>
                            <td>{{ $medication->frequency ?? 'N/A' }}</td>
                            <td>{{ $medication->duration ?? 'N/A' }}</td>
                            <td>{{ $medication->doctor_notes ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No medications prescribed in this visit.</p>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Diseases Diagnosed in This Visit</div>
        @php
            $visitDiseases = collect();
            if ($visit->visit_date) {
                $visitDiseases = \App\Models\PatientDisease::where('patient_id', $patient->id)
                    ->whereDate('created_at', $visit->visit_date->format('Y-m-d'))
                    ->get();
            }
        @endphp
        @if($visitDiseases->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Disease Name</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($visitDiseases as $disease)
                        <tr>
                            <td>{{ $disease->disease_name }}</td>
                            <td>{{ ucfirst($disease->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No diseases diagnosed in this visit.</p>
        @endif
    </div>

    <div class="footer">
        <p>This report was generated automatically by the medical system.</p>
        <p>© {{ date('Y') }} Medical System. All rights reserved.</p>
    </div>
</body>
</html>
