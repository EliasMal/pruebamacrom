<?php
    require_once "../../../tv-admin/asset/Clases/dbconectar.php";
    require_once "../../../tv-admin/asset/Clases/ConexionMySQL.php";
    require_once '../../../vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;
    date_default_timezone_set('America/Mexico_City');

    Class Contacto{
        private $formulario;
        private $conn;
        private $jsonData = array("Bandera"=>0, "Mensaje"=>"");
        private $fecha;
        

        public function __construct($array) {
            $this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
            $this->fecha = date("Y-m-d H:i:s");
        }
    
        public function __destruct() {
            unset($this->conn);
        }

        private function setContacto(){
            $sql = "INSERT INTO Contacto(Nombre, Telefono, Email, Mensaje, Fecha, RemoteServer,leido) values ('{$this->formulario->nombre}',
            '{$this->formulario->telefono}','{$this->formulario->email}','{$this->formulario->mensaje}','{$this->fecha}',
            '{$_SERVER["REMOTE_ADDR"]}',0)";
            return $this->conn->query($sql)? TRUE:FALSE;
        }

        private function get_reCapchatV2(){
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = array(
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n", 
                'secret' => '6Lfp3qEkAAAAADeqjBwH83-cDfPuIhY0cBAl5VN0',
                'response' => $this->formulario->recapRespond
            );
            $options = array(
                'http' => array (
                    'method' => 'POST',
                    'content' => http_build_query($data)
                )
            );
            $context  = stream_context_create($options);
            $verify = json_decode(file_get_contents($url, false, $context));
            
            return $verify->success;
        }
        
        public function main(){
            
            $this->formulario = json_decode(file_get_contents('php://input'));
            
            if($this->get_reCapchatV2()){
                if($this->setContacto()){
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["Mensaje"] = "Gracias por enviar tu mensaje, a la brevedad uno de nuestros ejecutivos se pondran en contacto con usted";
                    //Envio de registro satisfactorio al Correo del usuario.
                    $nombre = $this->formulario->nombre;
                    $mail = new PHPMailer;
                    $mail->isSMTP();
                    $mail->SMTPDebug = 0;
                    $mail->Host = 'smtp.hostinger.com';
                    $mail->Port = 587;
                    $mail->SMTPAuth = true;
                    $mail->Username = 'soporte@macromautopartes.com';
                    $mail->Password = '.Pm{d6+GxjZb';
                    $mail->setFrom('soporte@macromautopartes.com', 'Soporte Macrom');
                    $mail->addAddress('soporte@macromautopartes.com', 'Soporte');
                    $mail->Subject = 'Nuevo mensaje de contacto';
                    $mail->IsHTML(true);
                    $mail->CharSet = 'utf-8';
                    $mail->Body ='Nuevo mensaje del cliente '.$nombre.' enviado desde la pagina web.';
                    if (!$mail->send()) {
                        echo 'Mailer Error: ' . $mail->ErrorInfo;
                    }
                    //Fin Envio de registro satisfactorio al Correo del usuario.
                }else{
                    $this->jsonData["Bandera"] = 0;
                    $this->jsonData["Mensaje"] = "Error: No se pudo enviar tu mensaje";
                }
            }else{
                $this->jsonData["Bandera"] = 0;
                $this->jsonData["Mensaje"] = "Lo sentimos no comprobaste que eres humano";
            }
            
            print json_encode($this->jsonData);
        }


    }
    
    $app = new Contacto($array_principal);
    $app->main();
    
   
