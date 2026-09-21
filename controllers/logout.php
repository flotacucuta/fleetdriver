<?php
session_start();
session_unset(); // Destruye todas las variables de sesión
session_destroy(); // Destruye la sesión

// Redirigir de vuelta al login
header("Location: ../login.php");
exit();
?>