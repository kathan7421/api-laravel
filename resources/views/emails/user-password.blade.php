<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Your Account Activation</title>
</head>
<body>
    <h2>Welcome, {{ $user->name }}!</h2>

    <p>Your account has been activated. Here are your account details:</p>
    
    <p><strong>Email:</strong> {{ $user->email }}</p>
    <p><strong>Password:</strong> {{ $password }}</p>
    
    <p>Please keep this information secure and do not share it with anyone.</p>
    
    <p>Thank you!</p>
</body>
</html>
