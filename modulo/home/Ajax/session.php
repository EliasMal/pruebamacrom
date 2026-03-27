<?php
  session_name("loginCliente");
  session_start();

  // Connect to the database
  $conn = mysqli_connect('macromautopartes.com', 'u619477378_root','jSJLK6AqN%fwUOskf5@R','u619477378_macromau');
  
  // Le decimos al navegador que vamos a responder con JSON puro
  header('Content-Type: application/json');

  if (!$conn) {
    echo json_encode(["Bandera" => 0, "Mensaje" => "Connection failed: " . mysqli_connect_error()]);
    exit;
  }

  $formulario = json_decode(file_get_contents('php://input'));
  
  //Atender la petición rápida del mini-carrito
  if (isset($formulario->opc) && $formulario->opc == "obtener_carrito_actualizado") {
      echo json_encode([
          "Bandera" => 1,
          "Data" => [
              "Carrito" => isset($_SESSION["CarritoPrueba"]) ? $_SESSION["CarritoPrueba"] : []
          ]
      ]);
      exit; // Cortamos la ejecución aquí
  }
  
  // Variables para controlar la respuesta AJAX
  $banderaRespuesta = 0;
  $mensajeRespuesta = "";

  // Evitar errores de PHP si CarritoPrueba aún no existe
  if(!isset($_SESSION["CarritoPrueba"])) {
      $_SESSION["CarritoPrueba"] = [];
  }

  $iguales = 1;
  if(count($_SESSION["CarritoPrueba"]) == 0){
    $iguales = 1;
  }

  // Validación para evitar errores cuando no viene un "modelo" completo
  if(isset($formulario->modelo->datos->Clave)) {
      foreach($_SESSION["CarritoPrueba"] as $key => $value){
        if($value["Clave"] == $formulario->modelo->datos->Clave){
          $ncantidad = intval($value["Cantidad"] + $formulario->modelo->cantidad);

          if ($ncantidad > $formulario->modelo->datos->stock){
            $ncantidad = $formulario->modelo->datos->stock;
          }
          $iguales = 0;
          break; // Optimizamos saliendo del bucle
        }
      }
  }
  
  if($iguales != 0){
    $iguales=1;
  }

  switch($iguales){
    case 0: // Ya existe, actualizamos cantidad
      $sql = "UPDATE Carrito SET Cantidad = $ncantidad, Existencias=".$formulario->modelo->datos->stock." WHERE Clave =".$formulario->modelo->datos->Clave." AND _clienteid =".$_SESSION["iduser"];

      if (mysqli_query($conn, $sql)) {
        $banderaRespuesta = 1;
        $mensajeRespuesta = "Cantidad actualizada en el carrito";
        
        // Actualizar la cantidad en la sesión al instante
        foreach($_SESSION["CarritoPrueba"] as $key => $value){
            if($value["Clave"] == $formulario->modelo->datos->Clave){
                $_SESSION["CarritoPrueba"][$key]["Cantidad"] = $ncantidad;
                break;
            }
        }
      } else {
        $mensajeRespuesta = "Error actualizando: " . mysqli_error($conn);
      }
    break;

    case 1: // Es nuevo, lo insertamos
      if(isset($formulario->modelo->id)){
        $id = intval($formulario->modelo->id);
      
        $sql = "INSERT INTO Carrito (_clienteid, Clave, Producto, No_parte, Cantidad, Precio, Precio2, Alto, Largo, Ancho, Peso, imagenid, Existencias) 
        VALUES ('{$_SESSION["iduser"]}','{$formulario->modelo->datos->Clave}','{$formulario->modelo->datos->Producto}','{$formulario->modelo->datos->No_parte}','{$formulario->modelo->cantidad}','{$formulario->modelo->datos->Precio1}','{$formulario->modelo->datos->Precio2}','{$formulario->modelo->datos->Alto}',
        '{$formulario->modelo->datos->Largo}','{$formulario->modelo->datos->Ancho}','{$formulario->modelo->datos->Peso}','{$id}','{$formulario->modelo->datos->stock}')";
      
        if (mysqli_query($conn, $sql)) {
          $banderaRespuesta = 1;
          $mensajeRespuesta = "Producto agregado al carrito exitosamente";
          
          // Agregar a la variable de sesión al instante para que getSoloCarrito() lo pueda ver
          $_SESSION["CarritoPrueba"][] = [
              "Clave" => $formulario->modelo->datos->Clave,
              "Producto" => $formulario->modelo->datos->Producto,
              "_producto" => $formulario->modelo->datos->Producto, // JS usa este campo
              "No_parte" => $formulario->modelo->datos->No_parte,
              "Cantidad" => $formulario->modelo->cantidad,
              "Precio" => $formulario->modelo->datos->Precio1,
              "Precio2" => $formulario->modelo->datos->Precio2,
              "imagenid" => $id,
              "Existencias" => $formulario->modelo->datos->stock,
              "RefaccionOferta" => isset($formulario->modelo->datos->RefaccionOferta) ? $formulario->modelo->datos->RefaccionOferta : '0',
              "Kit" => isset($formulario->modelo->datos->Kit) ? $formulario->modelo->datos->Kit : 0 
          ];
        } else {
          $mensajeRespuesta = "Error insertando: " . mysqli_error($conn);
        }
      }
    break;
  }
  
  /*Eliminar una pieza del carrito*/
  if(isset($formulario->modelo->erase) && $formulario->modelo->erase == 1 ){
    $id = intval($formulario->modelo->borrar);
    
    // Mejor usar un foreach seguro para eliminar de la sesión
    foreach($_SESSION["CarritoPrueba"] as $key => $value){
      if(intval($value["Clave"]) == $id){
        unset($_SESSION["CarritoPrueba"][$key]);
      }
    }
    // Reindexamos el arreglo para que Angular no sufra con espacios vacíos
    $_SESSION["CarritoPrueba"] = array_values($_SESSION["CarritoPrueba"]);
    
    $sql = "DELETE FROM Carrito WHERE Clave =".$id." AND _clienteid =".$_SESSION["iduser"];
    
    if (mysqli_query($conn, $sql)) {
      $banderaRespuesta = 1;
      $mensajeRespuesta = "Producto eliminado del carrito";
    } else {
      $mensajeRespuesta = "Error eliminando: " . mysqli_error($conn);
    }
  }

  /*Agregar o quitar 1 pieza del carrito desde el checkout*/
  if(isset($formulario->modelo->upd) && $formulario->modelo->upd == 1 ){
    $id = intval($formulario->modelo->updCLV);
    
    foreach($_SESSION["CarritoPrueba"] as $key => $value){
      if(intval($value["Clave"]) == $id){
        $_SESSION["CarritoPrueba"][$key]["Cantidad"] = $formulario->modelo->Cantidad;
      }
    }

    $sql = "UPDATE Carrito SET Cantidad =".$formulario->modelo->Cantidad." WHERE Clave =".$id." AND _clienteid =".$_SESSION["iduser"];
    if (mysqli_query($conn, $sql)) {
      $banderaRespuesta = 1;
      $mensajeRespuesta = "Cantidad actualizada correctamente";
    } else {
      $mensajeRespuesta = "Error actualizando: " . mysqli_error($conn);
    }
  }

  if(isset($formulario->modelo->costo)){
    $_SESSION["Cenvio"]["costo"] = floatval($formulario->modelo->costo) ;
    $_SESSION["Cenvio"]["Servicio"] = $formulario->modelo->Servicio;
  }

  // Preparamos la respuesta final sumando la Sesión y nuestras Banderas
  $respuestaFinal = $_SESSION;
  $respuestaFinal["Bandera"] = $banderaRespuesta;
  $respuestaFinal["Mensaje"] = $mensajeRespuesta;

  echo json_encode($respuestaFinal);
  
  // Close the connection
  mysqli_close($conn);
?>