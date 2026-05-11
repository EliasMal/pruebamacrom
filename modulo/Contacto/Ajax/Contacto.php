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
            $nombre = addslashes(htmlspecialchars(strip_tags($this->formulario->nombre)));
            $telefono = addslashes(htmlspecialchars(strip_tags($this->formulario->telefono)));
            $email = addslashes(htmlspecialchars(strip_tags($this->formulario->email)));
            $mensaje = addslashes(htmlspecialchars(strip_tags($this->formulario->mensaje)));
            $ip = $_SERVER["REMOTE_ADDR"];

            $sql = "INSERT INTO Contacto(Nombre, Telefono, Email, Mensaje, Fecha, RemoteServer, leido) 
                    VALUES ('$nombre', '$telefono', '$email', '$mensaje', '{$this->fecha}', '$ip', 0)";
            
            return $this->conn->query($sql) ? TRUE : FALSE;
        }

        private function get_reCapchatV2(){
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = array(
                'secret' => '6Lfp3qEkAAAAADeqjBwH83-cDfPuIhY0cBAl5VN0',
                'response' => $this->formulario->recapRespond
            );
            $options = array(
                'http' => array (
                    'method' => 'POST',
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n", 
                    'content' => http_build_query($data)
                )
            );
            $context  = stream_context_create($options);
            $verify = json_decode(file_get_contents($url, false, $context));
            
            return $verify->success;
        }
        
        public function main(){
            
            $this->formulario = json_decode(file_get_contents('php://input'));
            
            if(!$this->formulario){
                $this->jsonData["Bandera"] = 0;
                $this->jsonData["Mensaje"] = "Datos no recibidos.";
                print json_encode($this->jsonData);
                return;
            }

            if($this->get_reCapchatV2()){
                if($this->setContacto()){
                    $this->jsonData["Bandera"] = 1;
                    $this->jsonData["Mensaje"] = "Gracias por enviar tu mensaje, a la brevedad uno de nuestros ejecutivos se pondrá en contacto con usted.";
                    
                    // Envio de correo
                    $nombre = htmlspecialchars($this->formulario->nombre);
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
                    $mail->Body = 'Nuevo mensaje del cliente <strong>'.$nombre.'</strong> enviado desde la pagina web.<br><br><strong>Teléfono:</strong> '.htmlspecialchars($this->formulario->telefono).'<br><strong>Email:</strong> '.htmlspecialchars($this->formulario->email).'<br><strong>Mensaje:</strong><br>'.nl2br(htmlspecialchars($this->formulario->mensaje));
                    
                    if (!$mail->send()) {
                        error_log('Mailer Error en Contacto: ' . $mail->ErrorInfo);
                    }

                }else{
                    $this->jsonData["Bandera"] = 0;
                    $this->jsonData["Mensaje"] = "Error: No se pudo registrar tu mensaje en la base de datos.";
                }
            }else{
                $this->jsonData["Bandera"] = 0;
                $this->jsonData["Mensaje"] = "Lo sentimos, no comprobaste que eres humano (reCAPTCHA falló).";
            }
            
            print json_encode($this->jsonData);
        }
    }
    
    $app = new Contacto($array_principal);
    $app->main();