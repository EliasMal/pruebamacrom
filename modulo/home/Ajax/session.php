<?php
  require_once $_SERVER['DOCUMENT_ROOT'] . "/core/bootstrap.php";
  $conn = mysqli_connect('macromautopartes.com', 'u619477378_root','jSJLK6AqN%fwUOskf5@R','u619477378_macromau');
  header('Content-Type: application/json');

  if (!$conn) {
    echo json_encode(["Bandera" => 0, "Mensaje" => "Connection failed: " . mysqli_connect_error()]);
    exit;
  }

  $formulario = json_decode(file_get_contents('php://input'));

  function limpiarSesionDuplicada() {
      if(isset($_SESSION["CarritoPrueba"])) {
          $unicos = [];
          $vistos = [];
          foreach($_SESSION["CarritoPrueba"] as $item) {
              $clv = is_object($item) ? $item->Clave : (isset($item["Clave"]) ? $item["Clave"] : null);
              if($clv && !in_array($clv, $vistos)) {
                  $vistos[] = $clv;
                  $unicos[] = $item;
              }
          }
          $_SESSION["CarritoPrueba"] = array_values($unicos);
      }
  }
  
  if (isset($formulario->opc) && $formulario->opc == "obtener_carrito_actualizado") {
      limpiarSesionDuplicada();
      echo json_encode([
          "Bandera" => 1,
          "Data" => [
              "Carrito" => isset($_SESSION["CarritoPrueba"]) ? $_SESSION["CarritoPrueba"] : []
          ]
      ]);
      exit; 
  }
  
  $banderaRespuesta = 0;
  $mensajeRespuesta = "";

  if(!isset($_SESSION["CarritoPrueba"])) {
      $_SESSION["CarritoPrueba"] = [];
  }

  $iguales = 1;
  $posicion = null;
  
  if(count($_SESSION["CarritoPrueba"]) == 0){
    $iguales = 1;
  }

  if(isset($formulario->modelo->datos->Clave)) {
      foreach($_SESSION["CarritoPrueba"] as $key => $value){
        $clave_item = is_object($value) ? $value->Clave : $value["Clave"];
        
        if((string)$clave_item === (string)$formulario->modelo->datos->Clave){
          $cantidad_actual = is_object($value) ? $value->Cantidad : $value["Cantidad"];
          $ncantidad = intval($cantidad_actual) + intval($formulario->modelo->cantidad);

          if ($ncantidad > $formulario->modelo->datos->stock){
            $ncantidad = $formulario->modelo->datos->stock;
          }
          $iguales = 0;
          $posicion = $key;
          break; 
        }
      }
  }
  
  if($iguales != 0){
    $iguales=1;
  }

  switch($iguales){
    case 0:
      $sql = "UPDATE Carrito SET Cantidad = $ncantidad, Existencias=".$formulario->modelo->datos->stock." WHERE Clave ='".$formulario->modelo->datos->Clave."' AND _clienteid =".$_SESSION["iduser"];

      if (mysqli_query($conn, $sql)) {
        $banderaRespuesta = 1;
        $mensajeRespuesta = "Cantidad actualizada en el carrito";
        
        if(is_object($_SESSION["CarritoPrueba"][$posicion])){
            $_SESSION["CarritoPrueba"][$posicion]->Cantidad = $ncantidad;
        }else{
            $_SESSION["CarritoPrueba"][$posicion]["Cantidad"] = $ncantidad;
        }
      } else {
        $mensajeRespuesta = "Error actualizando: " . mysqli_error($conn);
      }
    break;

    case 1:
      if(isset($formulario->modelo->id)){
        $id = intval($formulario->modelo->id);
      
        $sql = "INSERT INTO Carrito (_clienteid, Clave, Producto, No_parte, Cantidad, Precio, Precio2, Alto, Largo, Ancho, Peso, imagenid, Existencias) 
        VALUES ('{$_SESSION["iduser"]}','{$formulario->modelo->datos->Clave}','{$formulario->modelo->datos->Producto}','{$formulario->modelo->datos->No_parte}','{$formulario->modelo->cantidad}','{$formulario->modelo->datos->Precio1}','{$formulario->modelo->datos->Precio2}','{$formulario->modelo->datos->Alto}',
        '{$formulario->modelo->datos->Largo}','{$formulario->modelo->datos->Ancho}','{$formulario->modelo->datos->Peso}','{$id}','{$formulario->modelo->datos->stock}')";
      
        if (mysqli_query($conn, $sql)) {
          $banderaRespuesta = 1;
          $mensajeRespuesta = "Producto agregado al carrito exitosamente";
          
          $_SESSION["CarritoPrueba"][] = [
              "Clave" => $formulario->modelo->datos->Clave,
              "Producto" => $formulario->modelo->datos->Producto,
              "_producto" => $formulario->modelo->datos->Producto, 
              "No_parte" => $formulario->modelo->datos->No_parte,
              "Cantidad" => $formulario->modelo->cantidad,
              "Precio" => $formulario->modelo->datos->Precio1,
              "Precio2" => $formulario->modelo->datos->Precio2,
              "imagenid" => $id,
              "Existencias" => $formulario->modelo->datos->stock,
              "RefaccionOferta" => isset($formulario->modelo->datos->RefaccionOferta) ? $formulario->modelo->datos->RefaccionOferta : '0',
              "Kit" => isset($formulario->modelo->datos->Kit) ? $formulario->modelo->datos->Kit : 0,
              "Alto" => $formulario->modelo->datos->Alto,
              "Largo" => $formulario->modelo->datos->Largo,
              "Ancho" => $formulario->modelo->datos->Ancho,
              "Peso" => $formulario->modelo->datos->Peso,
              "Enviogratis" => isset($formulario->modelo->datos->Enviogratis) ? $formulario->modelo->datos->Enviogratis : 0
          ];
        } else {
          $mensajeRespuesta = "Error insertando: " . mysqli_error($conn);
        }
      }
    break;
  }
  
  /*Eliminar una pieza del carrito*/
  if(isset($formulario->modelo->erase) && $formulario->modelo->erase == 1 ){
    $id = $formulario->modelo->borrar;
    
    foreach($_SESSION["CarritoPrueba"] as $key => $value){
      $clave_item = is_object($value) ? $value->Clave : $value["Clave"];
      if((string)$clave_item === (string)$id){
        unset($_SESSION["CarritoPrueba"][$key]);
      }
    }
    $_SESSION["CarritoPrueba"] = array_values($_SESSION["CarritoPrueba"]);
    
    $sql = "DELETE FROM Carrito WHERE Clave ='".$id."' AND _clienteid =".$_SESSION["iduser"];
    
    if (mysqli_query($conn, $sql)) {
      $banderaRespuesta = 1;
      $mensajeRespuesta = "Producto eliminado del carrito";
    }
  }

  /*Agregar o quitar 1 pieza del carrito desde el checkout*/
  if(isset($formulario->modelo->upd) && $formulario->modelo->upd == 1 ){
    $id = $formulario->modelo->updCLV;
    
    foreach($_SESSION["CarritoPrueba"] as $key => $value){
      $clave_item = is_object($value) ? $value->Clave : $value["Clave"];
      if((string)$clave_item === (string)$id){
        if(is_object($_SESSION["CarritoPrueba"][$key])){
            $_SESSION["CarritoPrueba"][$key]->Cantidad = $formulario->modelo->Cantidad;
        }else{
            $_SESSION["CarritoPrueba"][$key]["Cantidad"] = $formulario->modelo->Cantidad;
        }
      }
    }

    $sql = "UPDATE Carrito SET Cantidad =".$formulario->modelo->Cantidad." WHERE Clave ='".$id."' AND _clienteid =".$_SESSION["iduser"];
    if (mysqli_query($conn, $sql)) {
      $banderaRespuesta = 1;
      $mensajeRespuesta = "Cantidad actualizada correctamente";
    }
  }

  if(isset($formulario->modelo->costo)){
    $_SESSION["Cenvio"]["costo"] = floatval($formulario->modelo->costo) ;
    $_SESSION["Cenvio"]["Servicio"] = $formulario->modelo->Servicio;
  }

  limpiarSesionDuplicada();
  $respuestaFinal = $_SESSION;
  $respuestaFinal["Bandera"] = $banderaRespuesta;
  $respuestaFinal["Mensaje"] = $mensajeRespuesta;

  echo json_encode($respuestaFinal);
  mysqli_close($conn);
?>