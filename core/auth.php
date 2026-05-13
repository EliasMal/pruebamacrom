<?php
/**
 * AUTH GLOBAL
 * Protege páginas privadas verificando la sesión del cliente
 */

require_once $_SERVER['DOCUMENT_ROOT'] . "/core/bootstrap.php";

if (!isset($_SESSION["iduser"]) || empty($_SESSION["iduser"])) {
    
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    $isJson = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;

    if ($isAjax || $isJson) {
        header('Content-Type: application/json');
        echo json_encode([
            "Bandera" => 0, 
            "mensaje" => "Tu sesión ha expirado o es inválida. Por favor, inicia sesión nuevamente.",
            "logout_forzado" => true
        ]);
        exit;
    }

    header("Location: /");
    exit;
}
?>