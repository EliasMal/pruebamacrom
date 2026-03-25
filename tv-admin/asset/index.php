<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("seguridad.php"); // Aquí ya debe estar el session_start()

if(!isset($_SESSION['bandera'])) $_SESSION['bandera'] = 0;
date_default_timezone_set('America/Mexico_City');   

// Cargar configuración y clases base
include('conf.php');
include('./Clases/dbconectar.php');
include('./Clases/ConexionMySQL.php');
include('./Clases/Funciones.php');

$func = new Funciones();
$conn = new HelperMySql($array_principal["server"], $array_principal["user"], $array_principal["pass"], $array_principal["db"]);

// Determinar el módulo solicitado
$modulo = !empty($_GET['mod']) ? $_GET['mod'] : MODULO_DEFECTO;

// Si el módulo no existe en conf.php, mandarlo al default
if (empty($conf[$modulo])) {
    $modulo = MODULO_DEFECTO;
}

// VALIDACIÓN DINÁMICA DE PERMISOS (Corazón del nuevo sistema)
$rolActual = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';
$accesoOtorgado = false;

// Módulos que siempre son accesibles (Sin importar la BD)
$modulosLibres = ['home', 'Perfil', 'login', MODULO_DEFECTO];

if (in_array($modulo, $modulosLibres)) {
    $accesoOtorgado = true;
} else {
    // Consultar en la base de datos si el rol tiene permiso para este módulo
    // Convertimos el nombre del módulo al formato de la BD: "?mod=Nombre"
    $mod_db = "?mod=" . $modulo;
    $rol_db = $rolActual;

    $sqlAcceso = "SELECT 1 FROM Permisos_Roles p 
                  INNER JOIN Modulos_Admin m ON p.id_modulo = m.id_modulo 
                  WHERE p.rol_nombre = '$rol_db' AND m.opc = '$mod_db' LIMIT 1";
    
    $resAcceso = $conn->query($sqlAcceso);
    if ($resAcceso && $resAcceso->num_rows > 0) {
        $accesoOtorgado = true;
    }
}

//Lógica de visualización (Layout y Fotos)
$ruta_foto = match($_SESSION["rol"]) {
    'ALUMNO'   => "Alumnoss/",
    'PROFESOR' => "Docentes/",
    default    => "Personal/",
};

$img = (file_exists("imagenes/Fotos/".$ruta_foto.$_SESSION["usr"].".jpg")) 
        ? "imagenes/Fotos/".$ruta_foto.$_SESSION["usr"].".jpg" 
        : "imagenes/sinfoto.jpg";

// Definir rutas de carga
if (empty($conf[$modulo]['layout'])) $conf[$modulo]['layout'] = LAYOUT_DEFECTO;

$path_layout = LAYOUT_PATH . '/' . $conf[$modulo]['layout'];
$path_modulo = MODULO_PATH . '/' . $conf[$modulo]['archivo'];

// Carga Final del Módulo
if ($accesoOtorgado) {
    //$permisos para que plantilla.php y Funciones::siAcceso2 no marquen error
    $permisos = $conf[$modulo];
    $permisos['permisos'] = [$rolActual]; 

    if (file_exists($path_layout)) {
        include($path_layout);
    } elseif (file_exists($path_modulo)) {
        include($path_modulo);
    } else {
        die('Error al cargar el módulo <b>'.$modulo.'</b>: no existe el archivo.');
    }
} else {
    // Si no tiene acceso, alert y regresar al home
    echo "<script>
            alert('No tienes permisos para acceder a este módulo.');
            window.location.href = '?mod=" . MODULO_DEFECTO . "';
          </script>";
}