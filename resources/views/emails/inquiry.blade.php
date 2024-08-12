<!DOCTYPE html>
<html>
<head>
    <title>Inquiry Details</title>
</head>
<body>
    <p> Dear ,{{$companies->name}}
    <h1>Inquiry Details</h1>
    <p>Name: {{ $inquiry->name }}</p>
    <p>Email: {{ $inquiry->email }}</p>
    <p>Phone: {{ $inquiry->phone }}</p>
    <p>Message: {{ $inquiry->message }}</p>
</body>
</html>
