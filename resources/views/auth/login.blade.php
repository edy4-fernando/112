<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Bio Natural Carmelita</title>
    @livewireStyles
</head>
<body style="background-color: #f4f6f8; font-family: Arial, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0;">

    <div style="background: white; padding: 30px; border-radius: 10px; width: 100%; max-width: 380px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-top: 5px solid #2e7d32;">
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="color: #2e7d32; margin: 0; font-size: 22px;">🌿 Acceso Administrativo</h2>
            <p style="color: #666; font-size: 13px; margin-top: 5px;">Bio Natural Carmelita - Cusco</p>
        </div>

        @if(session('error'))
            <div style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 6px; font-size: 13px; margin-bottom: 15px; text-align: center;">
                {{ session('error') }}
            </div>
        @endif

        <form action="/login" method="POST">
            @csrf
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 5px; color: #333;">Correo Electrónico</label>
                <input type="email" name="email" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 14px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 12px; font-weight: bold; margin-bottom: 5px; color: #333;">Contraseña</label>
                <input type="password" name="password" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-size: 14px;">
            </div>

            <button type="submit" style="width: 100%; background: #2e7d32; color: white; border: none; padding: 12px; border-radius: 6px; font-weight: bold; font-size: 14px; cursor: pointer;">
                Ingresar al Dashboard
            </button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
            <a href="/" style="color: #666; font-size: 12px; text-decoration: none;">← Volver al Catálogo Público</a>
        </div>
    </div>

    @livewireScripts
</body>
</html>