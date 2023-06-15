<?php
    session_name("loginCliente");
    session_start();
    $formulario = json_decode(file_get_contents('php://input'));
    if(isset($formulario->modelo->id)){
        $id = intval($formulario->modelo->id);
        
        if(isset($_SESSION['cart'][$id])){
            $_SESSION["cart"][$id]["cantidad"] = $formulario->modelo->cantidad;
        }else{
            
            $_SESSION["cart"][$id]=array(
                "cantidad"=>$formulario->modelo->cantidad,
                "Existencias"=>$formulario->modelo->Existencias, 
                "precio"=>$formulario->modelo->datos->RefaccionOferta? $formulario->modelo->datos->Precio2 : $formulario->modelo->precio, 
                "articulo"=>$formulario->modelo->datos->Producto, 
                "largo"=>$formulario->modelo->datos->Largo,
                "ancho"=> $formulario->modelo->datos->Ancho,
                "noparte"=> $formulario->modelo->datos->No_parte,
                "alto" => $formulario->modelo->datos->Alto,
                "peso" => $formulario->modelo->datos->Peso,
                "Enviogratis" =>$formulario->modelo->datos->Enviogratis,
                "id"=>$id);
        }
    }
    class carrito{
        private function setCSeguridad ($id){
            $sql = "INSERT INTO Carrito(_clienteid, Clave, Producto, No_parte, Cantidad, precio) values "
                    . "('{$this->formulario->Registro->username}',SHA('{$this->formulario->Registro->pass}'),'"
                    . date("Y-m-d", strtotime($this->formulario->Registro->FechaCreacion))."','".date("Y-m-d", strtotime($this->formulario->Registro->FechaModificacion))."',1,'$id',0)";
            return $this->conn->query($sql) ? true: false;
             
        }
    }

    if(isset($formulario->modelo->erase) && $formulario->modelo->erase == 1 ){
        $id = intval($formulario->modelo->id);
        unset($_SESSION['cart'][$id]);
    }

    if(isset($formulario->modelo->costo)){
       
        $_SESSION["Cenvio"]["costo"] = floatval($formulario->modelo->costo) ;
        $_SESSION["Cenvio"]["Servicio"] = $formulario->modelo->Servicio;
    }
    print json_encode($_SESSION);