<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="site-bg min-h-screen flex items-center justify-center p-4">
    <form method="POST" action="{{ route('admin.login.submit') }}" class="card-premium w-full max-w-md space-y-4">
        @csrf
        <h1 class="font-serif text-3xl text-rose-900" style="text-align: center;">Login</h1>
<!--         <p class="text-sm text-rose-700">Akses panel hanya melalui URL <strong>/login</strong>.</p>
 -->        @if ($errors->any())
            <div class="rounded-lg bg-rose-50 border border-rose-200 p-3 text-sm text-rose-700">{{ $errors->first() }}</div>
        @endif
        <label class="field"><span>Email</span><input type="email" name="email" required></label>
        <label class="field"><span>Password</span><input type="password" name="password" required></label>
        <input type="hidden" name="_latitude" id="login-latitude">
        <input type="hidden" name="_longitude" id="login-longitude">
        <input type="hidden" name="_geo_location" id="login-geo-location">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember"> Ingat saya</label>
        <button class="btn-primary w-full" type="submit">Masuk</button>
    </form>
    <script>
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((pos) => {
            document.getElementById('login-latitude').value = pos.coords.latitude;
            document.getElementById('login-longitude').value = pos.coords.longitude;
            document.getElementById('login-geo-location').value = 'Browser Geolocation';
        });
    }
    </script>
</body>
</html>
