<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FleeDriver</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #0f172a; color: #333; min-height: 100vh; display: flex; align-items: center; justify-content: center; overflow: hidden; }
        
        /* Fondo animado y partículas heredados de tu diseño */
        .animated-bg { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%); z-index: 0; }
        .particles { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; pointer-events: none; }
        .particle { position: absolute; background: radial-gradient(circle, rgba(255, 215, 0, 0.6) 0%, rgba(255, 215, 0, 0) 70%); border-radius: 50%; animation: float 20s infinite ease-in-out; }
        
        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0) scale(1); }
            25% { transform: translateY(-100px) translateX(50px) scale(1.1); }
            50% { transform: translateY(-50px) translateX(-50px) scale(0.9); }
            75% { transform: translateY(-150px) translateX(100px) scale(1.05); }
        }

        /* Contenedor del Login */
        .login-wrapper { position: relative; z-index: 10; width: 100%; max-width: 450px; padding: 20px; animation: fadeInDown 0.8s ease-out; }
        
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card {
            background: #ffffff; border-radius: 24px; padding: 40px; box-shadow: 0 20px 40px rgba(0,0,0,0.3), 0 0 0 1px rgba(255, 255, 255, 0.1);
            position: relative; overflow: hidden; text-align: center;
        }

        .login-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #FFD700 0%, #FFA500 100%); z-index: 1;
        }

        .logo-icon { font-size: 3.5rem; color: #1e293b; margin-bottom: 10px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1)); }
        .login-title { font-size: 2rem; font-weight: 800; color: #1e293b; margin-bottom: 5px; letter-spacing: -0.5px; }
        .login-subtitle { color: #64748b; font-size: 0.95rem; margin-bottom: 30px; font-weight: 500; }

        /* Formulario */
        .input-group { margin-bottom: 20px; text-align: left; position: relative; }
        .input-group label { display: block; font-size: 0.9rem; font-weight: 600; color: #1e293b; margin-bottom: 8px; }
        .input-group i { position: absolute; bottom: 15px; left: 15px; color: #94a3b8; font-size: 1.1rem; transition: color 0.3s; }
        .input-group input {
            width: 100%; padding: 14px 14px 14px 45px; border-radius: 12px; border: 2px solid #e2e8f0; font-family: 'Poppins', sans-serif;
            font-size: 1rem; color: #334155; background: #f8fafc; transition: all 0.3s ease; outline: none;
        }
        
        .input-group input:focus { border-color: #FFA500; background: #ffffff; box-shadow: 0 0 0 4px rgba(255, 165, 0, 0.1); }
        .input-group input:focus + i { color: #FFA500; }

        .btn-submit {
            width: 100%; padding: 16px; border-radius: 12px; border: none; background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            color: #1e293b; font-family: 'Poppins', sans-serif; font-size: 1.1rem; font-weight: 700; cursor: pointer; transition: all 0.3s ease;
            margin-top: 10px; box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3); display: flex; justify-content: center; align-items: center; gap: 10px;
        }

        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(255, 215, 0, 0.5); background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%); }
    </style>
</head>
<body>
    <div class="animated-bg"></div>
    <div class="particles" id="particles"></div>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="logo-icon"><i class="fas fa-truck-moving"></i></div>
            <h1 class="login-title">FleeDriver</h1>
            <p class="login-subtitle">Ingresa tus credenciales para continuar</p>

            <!-- El action apuntaría a tu script PHP de validación -->
            <form action="controllers/procesar_login.php" method="POST">
                <div class="input-group">
                    <label for="cedula">Cédula de Ciudadanía</label>
                    <input type="text" id="cedula" name="cedula" placeholder="Ej: 1090123456" required autocomplete="off">
                    <i class="fas fa-id-card"></i>
                </div>
                
                <div class="input-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                    <i class="fas fa-lock"></i>
                </div>

                <button type="submit" class="btn-submit">
                    Ingresar <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>

    <script>
        const particlesContainer = document.getElementById('particles');
        for (let i = 0; i < 20; i++) {
            const particle = document.createElement('div');
            particle.classList.add('particle');
            const size = Math.random() * 4 + 2;
            particle.style.width = size + 'px';
            particle.style.height = size + 'px';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.top = Math.random() * 100 + '%';
            particle.style.animationDelay = Math.random() * 20 + 's';
            particle.style.animationDuration = (Math.random() * 10 + 15) + 's';
            particlesContainer.appendChild(particle);
        }
    </script>
</body>
</html>