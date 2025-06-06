<?php
  session_name("loginUsuario");
  session_start();
  date_default_timezone_set('America/Mexico_City');
  // Connect to the database
  $conn = mysqli_connect('macromautopartes.com', 'u619477378_root','jSJLK6AqN%fwUOskf5@R','u619477378_macromau');
  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }

  $clave = $_POST["clave"];$idmarca = $_POST["idmarca"];$idmodelo = $_POST["idmodelo"];$generacion = $_POST["generacion"];$ainicial = $_POST["ainicial"];
  $afinal = $_POST["afinal"];$motor = $_POST["motor"];$transmision = $_POST["transmision"];$especificaciones = $_POST["especificaciones"];$id_imagen = $_POST["id_imagen"];

  $sql = "INSERT INTO compatibilidad (clave, idmarca, idmodelo, generacion, ainicial, afinal, motor, transmision, especificaciones, id_imagen) 
  VALUES ('$clave', '$idmarca','$idmodelo', '$generacion', '$ainicial','$afinal','$motor','$transmision','$especificaciones', '$id_imagen')";
    
  if (mysqli_query($conn, $sql)) {
    $sql1= "INSERT INTO actividad (clavepr, usuario, datosdiff, fecha_modificacion) VALUES ('$id_imagen', '{$_SESSION["nombre"]}', 'Agrego nuevo vehiculo a compatibilidades.', '".date("Y-m-d H:i:s")."');";
    if(mysqli_query($conn, $sql1)){
      echo "<h4>Data inserted successfully</h4>";
      echo '<meta http-equiv="refresh" content="0;url=../../../../?mod=Refacciones&opc=edit&id=',$id_imagen,'">';
      exit;
    } else{
      echo "Error inserting data in actividad: " . mysqli_error($conn);
    }
  } else {
    echo "Error inserting in compatibilidad data: " . mysqli_error($conn);
  }
    
  // Close the connection
  mysqli_close($conn);