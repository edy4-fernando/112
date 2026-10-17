<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#2e6b2f">
    <title>Bio Natural Carmelita</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    @livewireStyles
    <style>
        :root {
            --verde: #2e6b2f;
            --verde-claro: #e8efe0;
            --marron: #6b3f1d;
            --fondo: #fbfaf5;
            --texto: #4a4a4a;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; scroll-padding-top: 84px; }
        body { margin: 0; color: var(--texto); background: var(--fondo); font-family: 'Poppins', Arial, sans-serif; line-height: 1.6; }
        a { color: inherit; }
        .script { color: var(--verde); font-family: 'Dancing Script', cursive; }
        .contenedor { width: min(1100px, 100%); margin: 0 auto; padding: 0 20px; }

        .barra-superior { position: sticky; z-index: 20; top: 0; border-top: 4px solid var(--verde); background: #fff; box-shadow: 0 2px 10px rgb(0 0 0 / 5%); }
        .barra { display: flex; align-items: center; justify-content: space-between; gap: 18px; max-width: 1100px; margin: auto; padding: 12px 20px; }
        .logo { display: inline-flex; flex: 0 0 auto; align-items: center; text-decoration: none; }
        .logo img { display: block; width: min(194px, 42vw); height: auto; }
        .nav-links { display: flex; flex-wrap: wrap; gap: 22px; font-size: 14px; }
        .nav-links a { padding: 5px 0; color: #333; text-decoration: none; }
        .nav-links a:hover, .nav-links a:focus-visible { color: var(--verde); }
        .acciones { display: flex; align-items: center; gap: 12px; }
        .icono-enlace { display: inline-flex; width: 38px; height: 38px; align-items: center; justify-content: center; border-radius: 50%; background: var(--verde-claro); font-size: 18px; text-decoration: none; }
        .icono-enlace:hover, .icono-enlace:focus-visible { outline: 2px solid var(--verde); }

        .hero { display: flex; min-height: 360px; align-items: center; background: linear-gradient(120deg, #dcebc8, #f3f8e8 50%, #9fc27a); }
        .hero-contenido { display: flex; width: 100%; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; padding-top: 42px; padding-bottom: 42px; }
        .hero h1 { margin: 0; color: var(--verde); font-size: clamp(34px, 6vw, 52px); line-height: 1.08; }
        .hero h1 span { display: block; color: var(--marron); }
        .hero .lema { margin: 12px 0 0; font-size: clamp(22px, 4vw, 30px); }
        .hero .frase { max-width: 340px; margin: 0; font-size: 27px; text-align: center; transform: rotate(-5deg); }

        .historia { display: grid; grid-template-columns: 0.8fr 1.3fr 1fr; align-items: center; gap: 30px; padding-top: 54px; padding-bottom: 54px; }
        .foto-redonda { width: min(250px, 100%); aspect-ratio: 1; margin: auto; border: 8px solid #b8d58f; border-radius: 50%; background: radial-gradient(circle at 35% 30%, #d7e6bd, #8aa86a 70%); }
        .historia h2, .naturaleza h2, .recomendaciones h2 { margin: 0 0 10px; font-size: clamp(30px, 5vw, 40px); }
        .historia p { margin: 0 0 12px; font-size: 14px; }
        .tarjeta { margin-bottom: 14px; padding: 17px 19px; border-radius: 14px; background: var(--verde-claro); font-size: 13px; }
        .tarjeta h3 { margin: 0 0 4px; color: var(--verde); font-family: 'Dancing Script', cursive; font-size: 26px; }

        .valores { padding: 30px 0; background: var(--verde-claro); }
        .valores-lista { display: flex; justify-content: space-around; gap: 22px; flex-wrap: wrap; text-align: center; font-size: 14px; }
        .circulo { display: flex; width: 60px; height: 60px; align-items: center; justify-content: center; margin: 0 auto 8px; border-radius: 50%; background: var(--verde); color: #fff; font-size: 26px; }

        .naturaleza { padding: 52px 0; text-align: center; }
        .naturaleza > .contenedor > p { margin: 8px 0 26px; font-size: 14px; }
        .galeria { display: grid; grid-template-columns: repeat(auto-fit, minmax(145px, 1fr)); gap: 14px; }
        .galeria-item { position: relative; height: 150px; overflow: hidden; border-radius: 12px; background: linear-gradient(145deg, #d2dfb5, #7c9f5d); box-shadow: 0 3px 8px rgb(0 0 0 / 12%); }
        .galeria-item:nth-child(even) { background: linear-gradient(145deg, #e6d9b6, #a9bd7b); }
        .galeria-item img { width: 100%; height: 100%; object-fit: cover; }
        .galeria-item span { position: absolute; right: 8px; bottom: 8px; left: 8px; padding: 3px 10px; border-radius: 20px; background: var(--verde); color: #fff; font-size: 12px; }
        .creditos-imagenes { margin: 14px 0 0; color: #666; font-size: 11px; line-height: 1.8; }
        .creditos-imagenes a { color: var(--verde); }

        .seccion-productos { padding: 28px 0 10px; scroll-margin-top: 80px; }
        .seccion-productos > .contenedor > h2 { margin: 0 0 6px; color: var(--verde); font-size: 32px; }
        .seccion-productos > .contenedor > p { margin: 0 0 18px; color: #666; }

        .recomendaciones { padding: 44px 0 50px; border-top: 1px solid #ece9dc; text-align: center; }
        .recomendaciones h2 { margin-bottom: 24px; }
        .opiniones { padding: 48px 0; background: #f0f5e9; text-align: center; }
        .opiniones h2 { margin: 0 0 8px; font-size: clamp(30px, 5vw, 40px); }
        .opiniones > .contenedor > p { max-width: 680px; margin: 0 auto 24px; font-size: 14px; }
        .opiniones-guia { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 14px; margin-bottom: 24px; }
        .opinion-guia { padding: 18px; border: 1px solid #e2eadb; border-radius: 14px; background: #fff; box-shadow: 0 5px 16px rgb(37 79 40 / 6%); }
        .opinion-guia strong { display: block; margin: 6px 0; color: var(--verde); }
        .opinion-guia span { font-size: 13px; }
        .lista-recomendaciones { display: flex; justify-content: space-around; gap: 20px; flex-wrap: wrap; }
        .lista-recomendaciones > div { max-width: 175px; font-size: 13px; }
        .lista-recomendaciones .circulo { background: var(--verde-claro); color: var(--verde); }
        .lista-recomendaciones strong { display: block; color: var(--verde); font-size: 15px; }

        .cierre { padding: 30px 0; background: linear-gradient(90deg, #7fb05a, #b9d68f); }
        .cierre-contenido { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
        .cierre p { margin: 0; color: #fff; font-size: 26px; }
        .boton { display: inline-flex; align-items: center; justify-content: center; padding: 11px 24px; border: 0; border-radius: 30px; background: var(--verde); color: #fff; font-size: 14px; text-decoration: none; }
        .boton:hover, .boton:focus-visible { background: #214f22; }
        .boton-chat { background: #1a73e8; }
        .boton-chat:hover, .boton-chat:focus-visible { background: #1558b0; }
        .boton-facebook { background: #0866ff; }
        .boton-facebook:hover, .boton-facebook:focus-visible { background: #064fc4; }
        footer { padding: 18px 0; background: #244c26; color: #fff; font-size: 13px; text-align: center; }

        @media (max-width: 800px) {
            .barra { flex-wrap: wrap; }
            .nav-links { order: 3; width: 100%; justify-content: space-between; gap: 10px; font-size: 12px; }
            .historia { grid-template-columns: 1fr; }
            .foto-redonda { width: 210px; }
            .hero .frase { text-align: left; transform: none; }
        }

        @media (max-width: 420px) {
            .contenedor { padding-right: 15px; padding-left: 15px; }
            .barra { padding-right: 15px; padding-left: 15px; }
            .nav-links { gap: 7px; font-size: 11px; }
            .acciones { gap: 7px; }
        }
    </style>
</head>
<body>
    <header class="barra-superior">
        <div class="barra">
            <a class="logo" href="#inicio" aria-label="Bio Natural Carmelita, inicio"><img src="{{ asset('images/carmelitas-logo.png') }}" alt="Carmelitas"></a>
            <nav class="nav-links" aria-label="Navegación principal">
                <a href="#inicio">Inicio</a>
                <a href="#nosotros">Nosotros</a>
                <a href="#productos">Productos</a>
                <a href="#beneficios">Beneficios</a>
                <a href="#contacto">Contacto</a>
            </nav>
            <div class="acciones" aria-label="Accesos rápidos">
                <a class="icono-enlace" href="#catalog-search" aria-label="Buscar productos" onclick="document.getElementById('catalog-search')?.focus();">🔍</a>
                <a class="icono-enlace" href="{{ route('login') }}" aria-label="Acceso de administrador">👤</a>
                <a class="icono-enlace" href="#catalog-cart" aria-label="Abrir carrito" onclick="event.preventDefault(); document.getElementById('catalog-cart')?.click();">🛒</a>
            </div>
        </div>
    </header>

    <main>
        <section class="hero" id="inicio">
            <div class="contenedor hero-contenido">
                <div>
                    <h1>BIO NATURAL <span>CARMELITA</span></h1>
                    <p class="script lema">Lo mejor de la naturaleza para tu bienestar</p>
                </div>
                <p class="script frase">Tu salud es nuestra inspiración ♡</p>
            </div>
        </section>

        <section class="contenedor historia" id="nosotros">
            <div class="foto-redonda" role="img" aria-label="Ilustración de hojas naturales"></div>
            <div>
                <h2 class="script">Nuestra historia</h2>
                <p>Bio Natural Carmelita nació del sueño de una mujer apasionada por la naturaleza y el bienestar. Con la convicción de que la mejor salud comienza en lo natural, la Sra. Carmelita decidió compartir los beneficios de las plantas medicinales y los superalimentos, creando productos de alta calidad.</p>
                <p>Hoy somos una empresa comprometida con el cuidado de la salud, con soluciones naturales para toda la familia.</p>
            </div>
            <div>
                <div class="tarjeta"><h3>Misión</h3>Brindar productos naturales de alta calidad que contribuyan al bienestar de nuestros clientes.</div>
                <div class="tarjeta"><h3>Visión</h3>Ser una empresa reconocida por la calidad, innovación y compromiso con el bienestar y el medio ambiente.</div>
            </div>
        </section>

        <section class="valores" id="beneficios">
            <div class="contenedor valores-lista">
                <div><div class="circulo">🌿</div>Productos<br>naturales</div>
                <div><div class="circulo">🛡️</div>Calidad<br>garantizada</div>
                <div><div class="circulo">❤️</div>Compromiso<br>con tu bienestar</div>
                <div><div class="circulo">🌱</div>Cuidado del<br>medio ambiente</div>
                <div><div class="circulo">👨‍👩‍👧</div>Para toda<br>la familia</div>
            </div>
        </section>

        <section class="naturaleza">
            <div class="contenedor">
                <h2 class="script">Lo que la naturaleza nos ofrece</h2>
                <p>Seleccionamos materias primas naturales para crear productos para tu bienestar diario.</p>
                <div class="galeria">
                    <div class="galeria-item"><img src="{{ asset('images/naturaleza/jengibre.jpg') }}" alt="Raíces frescas de jengibre" loading="lazy"><span>Raíz de jengibre</span></div>
                    <div class="galeria-item"><img src="{{ asset('images/naturaleza/curcuma.jpg') }}" alt="Rizomas frescos de cúrcuma" loading="lazy"><span>Cúrcuma</span></div>
                    <div class="galeria-item"><img src="{{ asset('images/naturaleza/hojas-medicinales.jpg') }}" alt="Hojas de una planta medicinal" loading="lazy"><span>Plantas medicinales</span></div>
                    <div class="galeria-item"><span>Superalimentos</span></div>
                    <div class="galeria-item"><span>Semillas y cereales</span></div>
                    <div class="galeria-item"><span>Frutas naturales</span></div>
                </div>
                <p class="creditos-imagenes">
                    Imágenes: <a href="https://commons.wikimedia.org/wiki/File:Ginger_Root.jpg" target="_blank" rel="noopener noreferrer">“Ginger Root” de Sanjay Acharya</a> (redimensionada) y <a href="https://commons.wikimedia.org/wiki/File:Turmeric_rhizomes.jpg" target="_blank" rel="noopener noreferrer">“Turmeric rhizomes” de Lavinia Engelbrecht</a>, licencia <a href="https://creativecommons.org/licenses/by-sa/4.0/" target="_blank" rel="noopener noreferrer">CC BY-SA 4.0</a>; <a href="https://commons.wikimedia.org/wiki/File:Medicinal_leaves.jpg" target="_blank" rel="noopener noreferrer">“Medicinal leaves” de Maina Tudu</a>, dominio público <a href="https://creativecommons.org/publicdomain/zero/1.0/" target="_blank" rel="noopener noreferrer">CC0 1.0</a>.
                </p>
            </div>
        </section>

        <section class="seccion-productos" id="productos">
            <div class="contenedor">
                <h2>Nuestros productos</h2>
                <p>Explora cápsulas, bebidas y harinas. Puedes buscar por nombre o categoría de salud.</p>
            </div>
            <livewire:catalog-manager />
        </section>

        <section class="opiniones" id="opiniones">
            <div class="contenedor">
                <h2 class="script">Comparte tu experiencia</h2>
                <p>Estamos reuniendo opiniones reales de quienes ya compraron. Cuéntanos cómo fue tu atención o tu pedido; publicaremos tu testimonio solo con tu autorización.</p>
                <div class="opiniones-guia">
                    <div class="opinion-guia"><div class="circulo">💬</div><strong>La atención</strong><span>¿Cómo te atendimos?</span></div>
                    <div class="opinion-guia"><div class="circulo">📦</div><strong>Tu pedido</strong><span>¿Qué tal fue la compra y entrega?</span></div>
                    <div class="opinion-guia"><div class="circulo">🌿</div><strong>Tu experiencia</strong><span>¿Qué te gustaría contar a otras personas?</span></div>
                </div>
                <a class="boton" href="https://wa.me/51929053458?text=Hola%2C%20ya%20compr%C3%A9%20en%20Bio%20Natural%20Carmelita%20y%20quiero%20compartir%20mi%20opini%C3%B3n%3A" target="_blank" rel="noopener noreferrer">Enviar mi opinión por WhatsApp</a>
            </div>
        </section>

        <section class="recomendaciones" id="contacto">
            <div class="contenedor">
                <h2 class="script">Recomendaciones para una vida más saludable</h2>
                <div class="lista-recomendaciones">
                    <div><div class="circulo">💧</div><strong>Hidrátate</strong>Toma agua durante el día.</div>
                    <div><div class="circulo">🍎</div><strong>Come natural</strong>Incluye frutas y verduras en tus comidas.</div>
                    <div><div class="circulo">🏃</div><strong>Mantente activo</strong>El movimiento ayuda a tu bienestar.</div>
                    <div><div class="circulo">🌙</div><strong>Descansa bien</strong>Reserva tiempo para dormir y recuperarte.</div>
                    <div><div class="circulo">😊</div><strong>Cuida tus emociones</strong>Dedica tiempo a tu tranquilidad.</div>
                </div>
            </div>
        </section>

        <section class="cierre">
            <div class="contenedor cierre-contenido">
                <p class="script">Porque tu bienestar es nuestra mayor recompensa ♡</p>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a class="boton" href="#productos">Descubre nuestros productos ›</a>
                    <a class="boton" href="https://wa.me/51929053458" target="_blank" rel="noopener noreferrer">Escríbenos por WhatsApp</a>
                    <a class="boton boton-chat" href="https://chat.google.com/room/AAQAzQWHUgo/VTFE0Gif0kk/VTFE0Gif0kk?cls=10" target="_blank" rel="noopener noreferrer">Únete a nuestro Google Chat</a>
                    <a class="boton boton-facebook" href="https://www.facebook.com/share/1DCm7RHEpE/?mibextid=wwXIfr" target="_blank" rel="noopener noreferrer">Síguenos en Facebook</a>
                </div>
            </div>
        </section>
    </main>

    <footer>Bio Natural Carmelita · Cusco</footer>
    @livewireScripts
    <script>
        (function (a, unused, o, c, r, key) {
            a[key] = {
                id: '1083267',
                hash: '55e1d0a34edd98409c209a0af7a71ddbfc070d500c9ce0312d86579d8e104ec8',
                locale: 'es',
                setMeta: function (p) {
                    this.params = (this.params || []).concat([p]);
                },
            };
            a[o] = a[o] || function () {
                (a[o].q = a[o].q || []).push(arguments);
            };

            var d = a.document;
            var s = d.createElement('script');
            s.async = true;
            s.id = key + '_script';
            s.src = 'https://gso.kommo.com/js/button.js';
            d.head && d.head.appendChild(s);
        }(window, 0, 'crmPlugin', 0, 0, 'crm_plugin'));
    </script>
</body>
</html>
