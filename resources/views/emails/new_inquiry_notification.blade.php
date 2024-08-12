<!DOCTYPE html>
<html>
<head>
    <title>New Inquiry Received</title>
</head>
<body>
    <h1>New Inquiry Received</h1>
    <p>Name: {{ $inquiry->name }}</p>
    <p>Email: {{ $inquiry->email }}</p>
    <p>Phone: {{ $inquiry->phone }}</p>
    <p>Message: {{ $inquiry->message }}</p>
    @if($inquiry->company_name)
        <p>Company Name: {{ $inquiry->company_name }}</p>
    @endif
    @if($inquiry->service_name)
        <p>Service Name: {{ $inquiry->service_name }}</p>
    @endif
</body>
</html>
