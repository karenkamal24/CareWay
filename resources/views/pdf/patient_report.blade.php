<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Patient Medical Report</title>
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
        <h1>Patient Medical Report</h1>
        <p>Generated on: {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>

    <div class="section">
        <div class="section-title">Patient Information</div>
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
            <tr>
                <th>Gender</th>
                <td>{{ $patient->gender ?? 'N/A' }}</td>
                <th>Address</th>
                <td>{{ $patient->address ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Appointment Details</div>
        <table>
            <tr>
                <th>Doctor</th>
                <td>{{ $doctor->name ?? 'N/A' }}</td>
                <th>Specialization</th>
                <td>{{ $doctor->specialization ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Appointment Date</th>
                <td>{{ $appointment->appointment_time->format('Y-m-d H:i') }}</td>
                <th>Status</th>
                <td>{{ ucfirst($appointment->status ?? 'N/A') }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Diseases</div>
        @if($patient->diseases->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Disease Name</th>
                        <th>Status</th>
                        <th>Source</th>
                        <th>Added At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($patient->diseases as $disease)
                        <tr>
                            <td>{{ $disease->disease_name }}</td>
                            <td>{{ ucfirst($disease->status) }}</td>
                            <td>{{ ucfirst($disease->source) }}</td>
                            <td>{{ $disease->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No diseases recorded.</p>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Medications</div>
        @if($patient->medications->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Medication Name</th>
                        <th>Dose</th>
                        <th>Frequency</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Source</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($patient->medications as $medication)
                        <tr>
                            <td>{{ $medication->medication_name }}</td>
                            <td>{{ $medication->dose ?? 'N/A' }}</td>
                            <td>{{ $medication->frequency ?? 'N/A' }}</td>
                            <td>{{ $medication->duration ?? 'N/A' }}</td>
                            <td>{{ $medication->is_active ? 'Active' : 'Stopped' }}</td>
                            <td>{{ ucfirst($medication->source) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No medications recorded.</p>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Habits</div>
        @if($patient->habits)
            <table>
                <tr>
                    <th>Smoking</th>
                    <td>{{ $patient->habits->smoking ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Caffeine</th>
                    <td>{{ $patient->habits->caffeine ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Exercise</th>
                    <td>{{ $patient->habits->exercise ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Sleep Hours</th>
                    <td>{{ $patient->habits->sleep_hours ?? 'N/A' }}</td>
                </tr>
                @if($patient->habits->notes)
                    <tr>
                        <th>Notes</th>
                        <td>{{ $patient->habits->notes }}</td>
                    </tr>
                @endif
            </table>
        @else
            <p>No habits recorded.</p>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Visits</div>
        @if($patient->visits->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Symptoms</th>
                        <th>Diagnosis</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($patient->visits as $visit)
                        <tr>
                            <td>{{ $visit->created_at->format('Y-m-d') }}</td>
                            <td>{{ $visit->symptoms ?? 'N/A' }}</td>
                            <td>{{ $visit->diagnosis ?? 'N/A' }}</td>
                            <td>{{ $visit->notes ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No visits recorded.</p>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Attachments</div>
        @if($patient->attachments->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Added At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($patient->attachments as $attachment)
                        <tr>
                            <td>{{ ucfirst($attachment->type) }}</td>
                            <td>{{ $attachment->description ?? 'N/A' }}</td>
                            <td>{{ $attachment->created_at->format('Y-m-d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No attachments recorded.</p>
        @endif
    </div>

    <div class="footer">
        <p>This report was generated automatically by the medical system.</p>
        <p>© {{ date('Y') }} Medical System. All rights reserved.</p>
    </div>
</body>
</html>


