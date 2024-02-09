<?php
  session_name("loginCliente");
  session_start();

  // Connect to the database
  $conn = mysqli_connect('tsuruvolks.com.mx', 'macromau_admin','8nd$^&4m,Xjn','macromau_database');
  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }

  $formulario = json_decode(file_get_contents('php://input'));
  if($_SESSION["CarritoPrueba"].length == 0){
    $iguales = 1;
  }
    foreach($_SESSION["CarritoPrueba"] as $key=> $value){

      if($value["Clave"] == $formulario->modelo->datos->Clave){
        
        $ncantidad = intval($value["Cantidad"] + $formulario->modelo->cantidad);
        if ($ncantidad > $formulario->modelo->Existencias){
          $ncantidad = $formulario->modelo->Existencias;
        }
        $iguales = 0;

      }
    }
    if($iguales !=0){
      $iguales=1;
    }

    switch($iguales){
      case 0:
        $sql = "UPDATE Carrito SET Cantidad = $ncantidad, Existencias=".$formulario->modelo->Existencias." WHERE Clave =".$formulario->modelo->datos->Clave." AND _clienteid =".$_SESSION["iduser"];

          if (mysqli_query($conn, $sql)) {

          echo "<h4>Data inserted successfully</h4>";

          } else{
            echo "Error inserting data: " . mysqli_error($conn);

          }

      break;
      case 1:
        if(isset($formulario->modelo->id)){
          $id = intval($formulario->modelo->id);
      
            $sql = "INSERT INTO Carrito (_clienteid, Clave, Producto, No_parte, Cantidad, Precio, Alto, Largo, Ancho, Peso, imagenid, Existencias) 
            VALUES ('{$_SESSION["iduser"]}','{$formulario->modelo->datos->Clave}','{$formulario->modelo->datos->Producto}','{$formulario->modelo->datos->No_parte}','{$formulario->modelo->cantidad}','{$formulario->modelo->precio}','{$formulario->modelo->datos->Alto}',
            '{$formulario->modelo->datos->Largo}','{$formulario->modelo->datos->Ancho}','{$formulario->modelo->datos->Peso}','{$id}','{$formulario->modelo->Existencias}')";
      
            if (mysqli_query($conn, $sql)) {
      
              echo "<h4>Data inserted successfully</h4>";
      
            } else{
      
            echo "Error inserting data: " . mysqli_error($conn);
      
          }
          
        }
      break;
    }
    

  if(isset($formulario->modelo->erase) && $formulario->modelo->erase == 1 ){
    $id = intval($formulario->modelo->borrar);
    $n = intval($formulario->modelo->n);

    for($i=0; $i <= $n; $i++){
      $CL = intval($_SESSION["CarritoPrueba"][$i]["Clave"]);

      if ($CL == $id){
        unset($_SESSION["CarritoPrueba"][$i]);
      }
    
    }

    $sql = "DELETE FROM Carrito WHERE Clave =".$id." AND _clienteid =".$_SESSION["iduser"];
    if (mysqli_query($conn, $sql)) {

      echo "<h4>Data inserted successfully</h4>";

    } else{

      echo "Error inserting data: " . mysqli_error($conn);

    }

  }

  if(isset($formulario->modelo->costo)){
    $_SESSION["Cenvio"]["costo"] = floatval($formulario->modelo->costo) ;
    $_SESSION["Cenvio"]["Servicio"] = $formulario->modelo->Servicio;
  }

  print json_encode($_SESSION);
  // Close the connection
   mysqli_close($conn);
