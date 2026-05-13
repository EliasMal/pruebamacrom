<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name("loginCliente");
    session_start();
}

date_default_timezone_set('America/Mexico_City');
ini_set('display_errors', 0);
error_reporting(E_ALL);