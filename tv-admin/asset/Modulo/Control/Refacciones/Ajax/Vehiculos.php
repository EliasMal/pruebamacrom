<?php
    session_name("loginUsuario");
    session_start();

    // Connect to the database
    $conn = mysqli_connect('tsuruvolks.com.mx', 'macromau_admin','8nd$^&4m,Xjn','macromau_database');
    if (!$conn) {
      die("Connection failed: " . mysqli_connect_error());
    }

    $clave = $_POST["clave"];$idmarca = $_POST["idmarca"];$idmodelo = $_POST["idmodelo"];$generacion = $_POST["generacion"];$ainicial = $_POST["ainicial"];
    $afinal = $_POST["afinal"];$motor = $_POST["motor"];$transmision = $_POST["transmision"];$especificaciones = $_POST["especificaciones"];$id_imagen = $_POST["id_imagen"];

    $sql = "INSERT INTO compatibilidad (clave, idmarca, idmodelo, generacion, ainicial, afinal, motor, transmision, especificaciones, id_imagen) 
    VALUES ('$clave', '$idmarca','$idmodelo', '$generacion', '$ainicial','$afinal','$motor','$transmision','$especificaciones', '$id_imagen')";
    
    if (mysqli_query($conn, $sql)) {
      echo "<h4>Data inserted successfully</h4>";
      echo '<meta http-equiv="refresh" content="0;url=../../../../?mod=Refacciones&opc=edit&id=',$id_imagen,'">';
      exit;
    } else {
      echo "Error inserting data: " . mysqli_error($conn);
    }
  
    // Close the connection
    mysqli_close($conn);
