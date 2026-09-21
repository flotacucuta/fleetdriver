<?php
session_start();

// Importar la configuración de la base de datos (subiendo un nivel desde 'controllers')
require_once '../include/config.php';

// Validar que se haya enviado el formulario por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit();
}

// Obtener la conexión usando tu función
$conn = getConnection();

// Recibir y limpiar los datos enviados
$cedula = trim($_POST['cedula'] ?? '');
$password_ingresada = $_POST['password'] ?? '';

// Validar que no estén vacíos
if (empty($cedula) || empty($password_ingresada)) {
    $_SESSION['error_login'] = "Por favor, complete todos los campos.";
    header('Location: ../login.php');
    exit();
}

// Buscar al usuario por cédula usando sentencias preparadas de mysqli
$stmt = $conn->prepare("SELECT id, centro_id, nombre, password, rol, estado FROM usuarios WHERE cedula = ? LIMIT 1");
$stmt->bind_param("s", $cedula);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

// Si el usuario existe y la contraseña es correcta (password_verify comprueba el hash)
if ($usuario && password_verify($password_ingresada, $usuario['password'])) {
    
    // Verificar que la cuenta esté activa (estado = 1)
    if ($usuario['estado'] != 1) {
        $_SESSION['error_login'] = "Su cuenta está inactiva. Contacte al administrador.";
        header('Location: ../login.php');
        exit();
    }

    // Login exitoso: Guardar datos en variables de sesión
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['rol'] = $usuario['rol'];
    $_SESSION['centro_id'] = $usuario['centro_id'];
    $_SESSION['nombre'] = $usuario['nombre'];

    // Redirigir al dashboard principal (en la raíz)
    header('Location: ../index.php');
    exit();

} else {
    // Credenciales incorrectas
    $_SESSION['error_login'] = "Cédula o contraseña incorrectas.";
    header('Location: ../login.php');
    exit();
}

// Cerrar conexión
$stmt->close();
$conn->close();
?>