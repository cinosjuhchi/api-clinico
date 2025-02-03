<!DOCTYPE html>
<html>
<head>
    <title>{{ $subject }}</title>
</head>
<body>
    <h1>Hello,</h1>
    <p>Thank you for your report. Here are the details:</p>
    <ul>
        <li><strong>Email:</strong> {{ $reportBug->email }}</li>
        <li><strong>About:</strong> {{ $reportBug->reportBugType->name }}</li>
        <li><strong>Report Note:</strong> {{ $reportBug->note }}</li>
        <li><strong>Status:</strong> {{ $reportBug->status }}</li>
    </ul>
    <p>{{ $emailMessage }}</p>
    <p>Best regards,<br>{{ config('app.name') }}</p>
</body>
</html>
