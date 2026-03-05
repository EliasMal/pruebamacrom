<?php
// Iniciar sesión solo si no existe
if (session_status() === PHP_SESSION_NONE) {
    session_name("loginCliente");
    session_start();
}

// Zona horaria
date_default_timezone_set('America/Mexico_City');

// Configuración de errores (producción)
ini_set('display_errors', 0);
error_reporting(E_ALL);