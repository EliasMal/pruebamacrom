<!DOCTYPE html>
<html lang="es" class="Macrom_page">
<head>
	<title>Macrom autopartes</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="language" content="es-MX" />
    <meta name="country" content="MEX" />
    <meta name="currency" content="$" />
    <meta name="description" content="¡Compra en línea! La mejor calidad en refacciones y auto partes a precios competitivos. Nos especializamos en Nissan, Volkswagen y Chevrolet. Tenemos envios a toda la republica." />
    <meta name="Abstract" content="Refaccionaría en linea, Autopartes y Accesorios | Macrom" />                
    <meta name="keywords" content="Refacciones en línea 24/7 con envios a toda la republica" />
    <meta name="keywords" content="Colima, Macrom, Jalisco, Tamaulipas, Cancun, Refaccionaria, Refacciones, Auto Partes, Nissan, Chevrolet, Volkswagen, Vocho, VW, Tsuru, Chevy"/>
    <meta name="Monterrey" />
    <meta name="Colima" />
    <meta name="Jalisco" />
    <meta name="Tamaulipas" />
    <meta name="Cancun" />
    <meta name="Refacciones" />
    <meta name="autopartes" />
    <meta name="Nissan" />
    <meta name="Chevrolet" />
    <meta name="Volkswagen" />
    <meta name="Ciudad de Mexico" />
    <meta name="Vocho" />
    <meta name="Tsuru" />
    <meta name="Chevy" />
    <meta name="macrom" />
    <meta name="Mecanicos" />
    <meta name="robots" content="index" />
    <meta name="robots" content="follow" />
    <!-- End Google Analytics -->
    <meta name="google-site-verification" content="qCM6XimrT8ue8qAcveloUw">
    <!-- OpenGraph Metas -->
    <meta property="og:type" content="business.business">
    <meta property="og:title" content="Macromautopartes - Macrom - Refaccionaria en Línea">
    <meta property="og:description" content="Refacciones en línea 24/7 con envios a toda la republica">
    <meta property="og:image" itemprop="image" content="https://macromautopartes.com/images/icons/previwmacrom.png">
    <meta property="og:url" content="https://macromautopartes.com/">
    <meta property="og:site_name" content="Macromautopartes - Macrom">
    <meta property="og:locale" content="es_MX">
    <meta property="business:contact_data:street_address" content="Av. Benito Juárez #164 Col. La Gloria">
    <meta property="business:contact_data:locality" content="Villa de alvarez">
    <meta property="business:contact_data:region" content="Colima">
    <meta property="business:contact_data:postal_code" content="28980">
    <meta property="business:contact_data:country_name" content="Mexico">

<!--===============================================================================================-->
	<link rel="icon" type="image/png" href="images/icons/FaviconM.png"/>
<!--===============================================================================================-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="fonts/fontawesome-5/css/all.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="fonts/themify/themify-icons.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="fonts/Linearicons-Free-v1.0.0/icon-font.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="fonts/elegant-font/html-css/style.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/animate/animate.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/animsition/css/animsition.min.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/daterangepicker/daterangepicker.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="vendor/lightbox2/css/lightbox.min.css">
<!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="vendor/noui/nouislider.min.css">
<!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="vendor/toastr/build/toastr.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.7/css/jquery.dataTables.css">
<!--===============================================================================================-->
	<link rel="stylesheet" type="text/css" href="css/util.css">
    <link rel="preload" href="css/main" as ="styles">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <link rel="stylesheet" type="text/css" href="css/otra.css">
    <link rel="stylesheet" href="css/normalize.css">
    <script src="//code.jivosite.com/widget/MRXulua5n3" async></script>
<script async src="https://www.googletagmanager.com/gtag/js?id=G-T0GT52FN43"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-T0GT52FN43');
</script>

</head>
<body class="animsition body__theme--light" ng-app="tsuruVolks" ng-cloak id="BodyDark">

    <?php 
        include("./includes/cabecera.php");
       
        if (file_exists($path_modulo)){
            include($path_modulo);
        }else{
            die ('Error al cargar el modulo <b>'.$modulo.'</b>. No existe el archivo <b>'.$conf[$modulo]['archivo'].'</b>');
        }
        
        include("./includes/footer.php");
    ?>
<!--===============================================================================================-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
    <script type="text/javascript" src="vendor/momentjs/moment.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.7/js/jquery.dataTables.min.js"></script>
    <script src="tv-admin/asset/Plugins/numeric/jquery.numeric.js"></script>
    <!--==============================================================================================-->
        <!-- Angular -->
        <script type="text/javascript" src="tv-admin/asset/Js/angular/angular.min.js"></script>
        <script src="tv-admin/asset/Js/angular/angular-datatables.min.js"></script>
        <!--=======================================reCaptchat===============================================-->
        <script src="tv-admin/asset/Js/angular/angular-recaptcha.min.js"></script>
        <script type="text/javascript" src="tv-admin/asset/Js/angular/first.js"></script>
        <script type="text/javascript" src="js/Cabecera.js"></script>
        <script type="text/javascript">$_SESSION = <?php print json_encode($_SESSION)?>;</script>
<!--===============================================================================================-->
	<script type="text/javascript" src="vendor/animsition/js/animsition.min.js"></script>
<!--===============================================================================================-->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js" integrity="sha384-fbbOQedDUMZZ5KreZpsbe1LCZPVmfTnH7ois6mU1QK+m14rQ1l2bGBq41eYeM/fS" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.8/FileSaver.js"></script>
    <script type="text/javascript" src="vendor/JsZip/dist/jszip.min.js"></script>
<!--===============================================================================================-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
<!--===============================================================================================-->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        
<!--===============================================================================================-->
	<script type="text/javascript" src="vendor/parallax100/parallax100.js"></script>
	<script type="text/javascript">
        $('.parallax100').parallax100();
	</script>
<!--========================================tostr mensajes=======================================================-->
<script type="text/javascript" src="vendor/toastr/build/toastr.min.js"></script>

<!--===============================================================================================-->
	<script src="js/main.js"></script>
        <?php
        
            $mod = isset($_GET["mod"])? $_GET["mod"]:"home";
            switch ($mod){
                case 'home':
                    echo "<script type='text/javascript' src='modulo/home/Js/$mod.js'></script>";
                    break;
                case 'catalogo':
                        echo "<script type='text/javascript' src='vendor/noui/nouislider.min.js'></script>";
                        echo "<script type='text/javascript' src='modulo/Catalogo/Js/$mod.js'></script>";
                        
                    break;
                case 'login':
                        echo "<script type='text/javascript' src='modulo/Login/Js/Login.js'></script>";
                    break;
                    case 'register':
                        echo "<script type='text/javascript' src='modulo/Login/Js/Login.js'></script>";
                        break;
                case 'ProcesoCompra':
                case 'Compras':
                        echo "<script type='text/javascript' src='modulo/$mod/Js/$mod.js'></script>";
                    break;
                case 'Blog':
                        echo "<script type='text/javascript' src='modulo/$mod/Js/$mod.js'></script>";
                    break;
                case 'Profile':
                    echo "<script type='text/javascript' src='modulo/$mod/Js/$mod.js'></script>";
                break;
                
            }
        ?>
        <!-- Load Facebook SDK for JavaScript -->

</body>
</html>



