<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#2e6b2f">
    <title>Nuevo administrador · Bio Natural Carmelita</title>
</head>
<body style="margin:0; min-height:100vh; display:grid; place-items:center; background:#f4f7f0; color:#29352a; font-family:Arial,sans-serif;">
    <main style="width:min(440px, calc(100% - 32px)); padding:28px; border-radius:16px; background:#fff; box-shadow:0 12px 36px #23451b18;">
        <a href="{{ route('admin') }}" style="color:#2e6b2f; text-decoration:none; font-size:13px;">← Volver al panel</a>
        <h1 style="margin:20px 0 6px; color:#24552a; font-size:25px;">Agregar administrador</h1>
        <p style="margin:0 0 20px; color:#657064; font-size:14px; line-height:1.5;">Esta cuenta podrá acceder al panel, al inventario y a las ventas.</p>

        @if (session('status'))
            <div role="status" style="margin-bottom:16px; padding:12px; border-radius:8px; background:#e8f5e9; color:#205c2b;">{{ session('status') }}</div>
        @endif

        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <label for="name" style="display:block; margin:14px 0 6px; font-size:13px; font-weight:700;">Nombre completo</label>
            <input id="name" name="name" value="{{ old('name') }}" autocomplete="name" required maxlength="255" style="box-sizing:border-box; width:100%; padding:11px; border:1px solid #ccd6c8; border-radius:8px;">
            @error('name') <small style="color:#b42318;">{{ $message }}</small> @enderror

            <label for="email" style="display:block; margin:14px 0 6px; font-size:13px; font-weight:700;">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required maxlength="255" style="box-sizing:border-box; width:100%; padding:11px; border:1px solid #ccd6c8; border-radius:8px;">
            @error('email') <small style="color:#b42318;">{{ $message }}</small> @enderror

            <label for="password" style="display:block; margin:14px 0 6px; font-size:13px; font-weight:700;">Contraseña (mínimo 8 caracteres)</label>
            <input id="password" type="password" name="password" autocomplete="new-password" required minlength="8" style="box-sizing:border-box; width:100%; padding:11px; border:1px solid #ccd6c8; border-radius:8px;">
            @error('password') <small style="color:#b42318;">{{ $message }}</small> @enderror

            <label for="password_confirmation" style="display:block; margin:14px 0 6px; font-size:13px; font-weight:700;">Confirmar contraseña</label>
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required minlength="8" style="box-sizing:border-box; width:100%; padding:11px; border:1px solid #ccd6c8; border-radius:8px;">

            <button type="submit" style="width:100%; margin-top:22px; padding:12px; border:0; border-radius:8px; background:#2e6b2f; color:#fff; font-size:15px; font-weight:700; cursor:pointer;">Crear administrador</button>
        </form>
    </main>
</body>
</html>
