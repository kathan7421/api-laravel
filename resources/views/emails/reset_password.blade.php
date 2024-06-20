<!-- resources/views/emails/reset_password.blade.php -->

<p>You are receiving this email because we received a password reset request for your account.</p>
<!-- <p><a href="{{ config('app.frontend_url') }}/reset-password/{{ $token }}">Reset Password</a></p> -->
 <a href="http://localhost:4200/admin/reset-password/{{$token}}">Reset Password </a>
<p>If you did not request a password reset, no further action is required.</p>
