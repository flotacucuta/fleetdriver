<?php
session_start();
// Si no hay sesión iniciada, redirigir al login
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'include/config.php'; 

$rol = $_SESSION['rol'] ?? 'EMPLEADO'; // 'SUPERADMIN', 'ADMIN' o 'EMPLEADO'
$nombre_usuario = $_SESSION['nombre'] ?? 'Usuario';
$usuario_id = $_SESSION['usuario_id'];
$cedula = $_SESSION['cedula'] ?? '';

// Variables de control de vista
$show_transporte = false;
$show_almacen = false;

// Determinar qué módulos mostrar según el rol
if ($rol === 'SUPERADMIN' || $rol === 'ADMIN') {
    // Administradores ven ambos módulos siempre
    $show_transporte = true;
    $show_almacen = true;
} else {
    // Es operativo/empleado. Consultamos exactamente a qué módulo está asignado en la BD
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT modulo FROM registros WHERE usuario_id = ? OR cedula = ? LIMIT 1");
    $stmt->bind_param("is", $usuario_id, $cedula);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        $user_modulo = strtoupper(trim($row['modulo']));
        if ($user_modulo === 'TRANSPORTE') {
            $show_transporte = true;
        } elseif ($user_modulo === 'ALMACEN') {
            $show_almacen = true;
        }
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FleeDriver - Panel Principal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #0f172a;
            color: #333;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        /* Fondo animado con partículas */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            z-index: 0;
        }

        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            background: radial-gradient(circle, rgba(255, 215, 0, 0.6) 0%, rgba(255, 215, 0, 0) 70%);
            border-radius: 50%;
            animation: float 20s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) translateX(0) scale(1); }
            25% { transform: translateY(-100px) translateX(50px) scale(1.1); }
            50% { transform: translateY(-50px) translateX(-50px) scale(0.9); }
            75% { transform: translateY(-150px) translateX(100px) scale(1.05); }
        }

        /* Header mejorado */
        .header {
            background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
            padding: 50px 20px 80px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(255, 215, 0, 0.2);
            position: relative;
            overflow: hidden;
            z-index: 10;
            clip-path: polygon(0 0, 100% 0, 100% 85%, 0 100%);
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 20px,
                rgba(255, 255, 255, 0.08) 20px,
                rgba(255, 255, 255, 0.08) 40px
            );
            animation: slidePattern 30s linear infinite;
        }

        @keyframes slidePattern {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }

        .header-content {
            position: relative;
            z-index: 2;
            animation: fadeInDown 1s ease-out;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-icon {
            font-size: 4.5rem;
            margin-bottom: 15px;
            animation: bounce 2s ease-in-out infinite;
            display: inline-block;
            filter: drop-shadow(0 8px 15px rgba(0,0,0,0.2));
            color: #1e293b;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
        }

        .header h1 {
            color: #1e293b;
            font-size: 3.5rem;
            font-weight: 900;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            letter-spacing: -1.5px;
        }

        .header p {
            color: #1e293b;
            font-size: 1.2rem;
            font-weight: 600;
            opacity: 0.9;
        }

        /* Widget de Usuario */
        .user-widget {
            position: absolute;
            top: 20px;
            right: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(5px);
            padding: 8px 20px 8px 15px;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            z-index: 20;
            transition: all 0.3s ease;
        }

        .user-widget:hover {
            background: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .user-name {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.2;
        }

        .user-role {
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .user-avatar {
            font-size: 2.2rem;
            color: #1e293b;
            background: #fff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .logout-btn {
            margin-left: 5px;
            padding-left: 15px;
            border-left: 1px solid rgba(30, 41, 59, 0.2);
            color: #dc2626;
            font-size: 1.3rem;
            text-decoration: none;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .logout-btn:hover {
            transform: scale(1.1);
            color: #991b1b;
        }

        .container {
            max-width: 1400px;
            margin: -60px auto 60px;
            padding: 0 20px;
            position: relative;
            z-index: 10;
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 40px;
        }

        .module-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 0;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2), 0 0 0 1px rgba(255, 255, 255, 0.1);
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .module-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #FFD700 0%, #FFA500 100%);
            z-index: 1;
        }

        .module-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 60px rgba(0,0,0,0.3), 0 0 0 2px rgba(255, 215, 0, 0.5);
        }

        .module-header {
            text-align: center;
            padding: 40px 30px 30px;
            position: relative;
            z-index: 2;
            border-bottom: 1px solid #f1f5f9;
        }

        .module-title {
            font-size: 1.8rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .module-subtitle {
            color: #64748b;
            font-size: 1rem;
            font-weight: 500;
        }

        /* --- MODIFICADO: Grid de botones adaptativo para eliminar espacios vacíos --- */
        .module-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 15px;
            padding: 30px;
            background: #f8fafc;
        }

        .module-btn {
            padding: 25px 15px;
            border-radius: 16px;
            border: none;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            text-decoration: none;
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .module-btn i,
        .module-btn span {
            position: relative;
            z-index: 1;
        }

        .module-btn i {
            font-size: 2.2rem;
            transition: transform 0.3s ease;
        }

        .module-btn:hover {
            transform: translateY(-5px);
        }

        .module-btn:hover i {
            transform: scale(1.15);
        }

        .btn-blue { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); }
        .btn-blue:hover { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); box-shadow: 0 10px 25px rgba(30, 41, 59, 0.4); }

        .btn-gold { background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%); color: #1e293b; }
        .btn-gold:hover { background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%); box-shadow: 0 10px 25px rgba(255, 215, 0, 0.4); }

        .admin-btn {
            position: fixed;
            bottom: 40px;
            right: 40px;
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: #FFD700;
            color: #1e293b;
            border: none;
            font-size: 1.8rem;
            cursor: pointer;
            box-shadow: 0 10px 25px rgba(255, 215, 0, 0.4);
            transition: all 0.3s ease;
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .admin-btn:hover { transform: scale(1.1) rotate(90deg); background: #FFA500; }

        .admin-btn::after {
            content: 'Panel de Administración';
            position: absolute;
            right: 90px;
            background: #ffffff;
            color: #1e293b;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .admin-btn:hover::after { opacity: 1; right: 85px; }

        /* Pantalla Vacia (Sin Modulo) */
        .empty-state {
            grid-column: 1 / -1;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 24px;
            padding: 50px 30px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        @media (max-width: 1024px) {
            .modules-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            .header { padding: 70px 20px 60px; }
            .header h1 { font-size: 2.2rem; }
            .logo-icon { font-size: 3.5rem; }
            .module-buttons { grid-template-columns: 1fr; gap: 15px; padding: 20px; }
            .admin-btn { width: 60px; height: 60px; bottom: 20px; right: 20px; font-size: 1.5rem; }
            .admin-btn::after { display: none; }
            .user-widget { top: 10px; right: 10px; padding: 5px 15px 5px 5px; }
            .user-info { display: none; }
        }

        .fade-in {
            animation: fadeIn 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        @keyframes fadeIn { to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>
    <div class="animated-bg"></div>
    <div class="particles" id="particles"></div>

    <div class="header">
        <div class="user-widget">
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($nombre_usuario); ?></span>
                <span class="user-role"><?php echo htmlspecialchars($rol); ?></span>
            </div>
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <a href="controllers/logout.php" class="logout-btn" title="Cerrar Sesión">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>

        <div class="header-content">
            <div class="logo-icon">
                <i class="fas fa-truck-moving"></i>
            </div>
            <h1>FleeDriver</h1>
            <p>Sistema de Gestión de Flota y Conductores</p>
        </div>
    </div>

   <div class="container">
        <div class="modules-grid">
            
            <?php if (!$show_transporte && !$show_almacen): ?>
                <!-- Si el usuario no tiene módulo asignado -->
                <div class="empty-state fade-in">
                    <i class="fas fa-user-lock" style="font-size: 4rem; color: #cbd5e1; margin-bottom: 20px;"></i>
                    <h2 style="font-size: 1.8rem; color: #1e293b; margin-bottom: 10px;">Perfil no asignado</h2>
                    <p style="color: #64748b; font-size: 1.1rem;">Tu usuario aún no ha sido vinculado a ningún módulo.<br>Por favor, contacta con tu administrador para que actualice tu perfil.</p>
                </div>
            <?php else: ?>

                <!-- MÓDULO TRANSPORTE -->
                <?php if ($show_transporte): ?>
                <div class="module-card fade-in" style="animation-delay: 0.1s;">
                    <div class="module-header">
                        <h2 class="module-title"><span style="font-size: 1.5em;">🚚</span> Transporte</h2>
                        <p class="module-subtitle">Gestión de ruta</p>
                    </div>
                    <div class="module-buttons">
                        <a href="flota.php?modulo=TRANSPORTE&tipo=OPERARIO" class="module-btn btn-blue">
                            <i class="fas fa-user-tie"></i>
                            <span>Operarios</span>
                        </a>
                        <a href="flota.php?modulo=TRANSPORTE&tipo=VEHICULO" class="module-btn btn-gold">
                            <i class="fas fa-truck"></i>
                            <span>Vehículos</span>
                        </a>
                        
                        <?php if ($rol === 'ADMIN' || $rol === 'SUPERADMIN'): ?>
                        <a href="inspeccion.php?modulo=TRANSPORTE" class="module-btn btn-blue">
                            <i class="fas fa-search"></i>
                            <span>Inspección</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- MÓDULO ALMACÉN -->
                <?php if ($show_almacen): ?>
                <div class="module-card fade-in" style="animation-delay: 0.3s;">
                    <div class="module-header">
                        <h2 class="module-title"><span style="font-size: 1.5em;">🏗️</span> Almacén</h2>
                        <p class="module-subtitle">Gestión interna</p>
                    </div>
                    <div class="module-buttons">
                        <a href="flota.php?modulo=ALMACEN&tipo=OPERARIO" class="module-btn btn-blue">
                            <i class="fas fa-user-tie"></i>
                            <span>Operarios</span>
                        </a>
                        <a href="flota.php?modulo=ALMACEN&tipo=VEHICULO" class="module-btn btn-gold">
                            <i class="fas fa-building"></i>
                            <span>Vehículos</span>
                        </a>
                        
                        <?php if ($rol === 'ADMIN' || $rol === 'SUPERADMIN'): ?>
                        <a href="inspeccion.php?modulo=ALMACEN" class="module-btn btn-blue">
                            <i class="fas fa-search"></i>
                            <span>Inspección</span>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>

    <!-- Botón de administración global solo para ADMIN y SUPERADMIN -->
    <?php if ($rol === 'ADMIN' || $rol === 'SUPERADMIN'): ?>
    <button class="admin-btn" onclick="window.location.href='admin.php'">
        <i class="fas fa-cog"></i>
    </button>
    <?php endif; ?>

    <script>
        const particlesContainer = document.getElementById('particles');
        const particleCount = 25; 

        for (let i = 0; i < particleCount; i++) {
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