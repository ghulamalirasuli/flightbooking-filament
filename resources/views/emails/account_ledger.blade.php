<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ledger Report</title>
</head>
<body>
    <p>Hello,</p>
    <p>Please find the attached ledger report for {{ $account->account_name }}.</p>
    <p>Best regards,</p>
    <p>{{ config('app.name') }}</p>
</body>
</html>