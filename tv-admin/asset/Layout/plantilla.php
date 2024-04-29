<!DOCTYPE html>
<html ng-app="tsuruVolks">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta http-equiv="refresh" content="<?php echo $duracionsession*60;?>;URL='<?php echo $_SERVER['PHP_SELF'];?>'">
    <title>Refaccionaria Macrom | Dashboard</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
    <!-- Theme style -->
    <link rel="stylesheet" href="./Css/adminlte.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="Plugins/font-awesome/css/all.min.css">
    <!-- <link rel="stylesheet" href="Plugins/font-awesome4-7-0/css/font-awesome.min.css"> -->
    <!-- iCheck -->
    <link rel="stylesheet" href="./Plugins/iCheck/flat/blue.css">
    <!-- Morris chart -->
    <link rel="stylesheet" href="./Plugins/morris/morris.css">
    <!-- jvectormap -->
    <link rel="stylesheet" href="./Plugins/jvectormap/jquery-jvectormap-1.2.2.css">
    <!-- Date Picker -->
    <link rel="stylesheet" href="./Plugins/datepicker/datepicker3.css">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="./Plugins/daterangepicker/daterangepicker-bs3.css">
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="./Plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
    <!-- Select2 -->
    <link rel="stylesheet" href="./Plugins/select2/select2.min.css">
    <!--toastr-->
    <link rel="stylesheet" href="./Plugins/toastr/build/toastr.min.css">
    <!--pace-->
    <link rel="stylesheet" href="./Plugins/pace/pace.css">
    <!-- swictchery -->
    <link rel="stylesheet" href="./Plugins/switchery/dist/switchery.min.css">
    <!-- switch -->
    <link rel="stylesheet" href="./Plugins/switchbootstrap4/switch.css">
    
    <link rel="stylesheet" href="./Plugins/colorpicker2/css/colorpicker.css" type="text/css" />

    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.7/css/jquery.dataTables.css">

    <link rel="stylesheet" href="./Css/select.min.css">   
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="./Css/style.css">   


  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
  
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

  <?php
        require_once 'Includes/Cabecera.php';
        
        require_once 'Menus/Menu.php';
  ?>

  

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <?php
            if($func::siAcceso2($permisos["permisos"])){
              if (file_exists($path_modulo))include($path_modulo);
              else die ('Error al cargar el modulo <b>'.$modulo.'</b>. No existe el archivo <b>'.$conf[$modulo]['archivo'].'</b>');
            }else{
              include(MODULO_PATH.'/Error/Controller.php');
            }
	?>
      
  </div>
  <?php
        require_once 'Includes/Pie.php';
        require_once 'Includes/Siderbar.php';
  ?>

  
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="./Plugins/jquery/jquery.min.js"></script>
<!-- select2 -->
<script src="./Plugins/select2/select2.full.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
<!-- Highchart -->
<script src="https://code.highcharts.com/highcharts.js"></script>
<!--angular-->
<script src="./Js/angular/angular.min.js"></script>
<script src="./Js/angular/angular-datatables.min.js"></script>
<script src="./Js/angular/angular-recaptcha.min.js"></script>
<script src="./Js/angular/angular-ui-select.min.js"></script>
<script src="./Js/angular/first.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="./Plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- Morris.js charts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script src="./Plugins/morris/morris.min.js"></script>
<!-- Sparkline -->
<script src="./Plugins/sparkline/jquery.sparkline.min.js"></script>
<!-- jvectormap -->
<script src="./Plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
<script src="./Plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>
<!-- jQuery Knob Chart -->
<script src="./Plugins/knob/jquery.knob.js"></script>
<!-- daterangepicker -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js"></script>
<script src="./Plugins/daterangepicker/daterangepicker.js"></script>
<!-- datepicker -->
<script src="./Plugins/datepicker/bootstrap-datepicker.js"></script>
<!-- Bootstrap WYSIHTML5 -->
<script src="./Plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.js"></script>
<!-- Slimscroll -->
<script src="./Plugins/slimScroll/jquery.slimscroll.min.js"></script>
<!-- FastClick -->
<script src="./Plugins/fastclick/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="./Js/adminlte.js"></script>
<!--toastr-->
<script type="text/javascript" src="./Plugins/toastr/toastr.js"></script>
<!--pacejs-->
<script type="text/javascript" src="./Plugins/pace/pace.js"></script>
<!-- Switchery -->
<script type="text/javascript" src="./Plugins/switchery/dist/switchery.min.js"></script>
<!-- Colorpicker -->
<script type="text/javascript" src="./Plugins/colorpicker2/js/colorpicker.js"></script>
<!-- Numeric -->
<script type="text/javascript" src="./Plugins/numeric/jquery.numeric.js"></script>

<script type="text/javascript">
        $(document).ajaxStart(function() { Pace.restart(); });
</script>
<!-- App Tsuruvolks -->
<?php
    if(isset($_GET["mod"])){
        switch($_GET["mod"]){
            case 'usuarios':
                echo "<script src='./Modulo/Configuracion/Usuarios/Js/".$_GET["mod"].".js'></script>";
                break;
            case 'Categorias':
            case 'Marcas':
            case 'Modelos':
            case 'Cenvios':
            case 'Proveedores':
            case 'Perfil':
            case 'Correo':
                echo "<script src='./Modulo/Configuracion/{$_GET["mod"]}/Js/".$_GET["mod"].".js'></script>";
                break;
            case 'Refacciones':
            case 'Pedidos':
            case 'Clientes':
            case 'Contacto':
            case 'Cenvios':
                echo "<script src='./Modulo/Control/{$_GET["mod"]}/Js/".$_GET["mod"].".js'></script>";
                break;
            case 'webprincipal':
            case 'Blog':
                echo "<script src='./Modulo/Secciones/{$_GET["mod"]}/Js/".$_GET["mod"].".js'></script>";
              break;
            case 'Actualizarpre':
                echo "<script src='./Modulo/Respaldo/Precios/Js/".$_GET["mod"].".js'></script>";
            break;
            case 'Pruebas':
                echo "<script src='./Modulo/Respaldo/{$_GET["mod"]}/Js/".$_GET["mod"].".js'></script>";
              break;
            case 'CPostales':
            case 'IRefacciones':
              echo "<script src='./Modulo/Importar/{$_GET["mod"]}/Js/".$_GET["mod"].".js'></script>";
            break;
            case 'repRefacciones':
                echo "<script src='./Modulo/Mantenimiento/{$_GET["mod"]}/Js/".$_GET["mod"].".js'></script>";
              break;
            case 'RepProductos':
                echo "<script src='./Modulo/Reportes/{$_GET["mod"]}/Js/".$_GET["mod"].".js'></script>";
              break;
            default:
                echo "<script src='./Modulo/Home/Js/Home.js'></script>";
            break;
        }
    }else{
      echo "<script src='./Modulo/Home/Js/Home.js'></script>";
    }
?>



</body>
</html>

