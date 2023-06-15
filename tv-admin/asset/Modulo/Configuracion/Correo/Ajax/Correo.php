<?php
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    require_once "../../../../Clases/dbconectar.php";
    require_once "../../../../Clases/ConexionMySQL.php";
    require_once "../../../../Clases/PHPMailer/Exception.php";
    require_once "../../../../Clases/PHPMailer/PHPMailer.php";
    require_once "../../../../Clases/PHPMailer/SMTP.php";
    /* require_once "../../../../Clases/PHPMailer/class.phpmailer.php";
    require_once "../../../../Clases/PHPMailer/class.smtp.php"; */

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;

    class email{
        private $conn;
        private $correo;
        private $jsonData = array("Bandera"=>false, "Mensaje"=>"", "Data"=>array());

        public function __construct($array) {
            #$this->conn = new HelperMySql($array["server"], $array["user"], $array["pass"], $array["db"]);
            $this->correo = new PHPMailer();
        }
    
        public function __destruct() {
            //unset($this->correo);
            unset($this->conn);
        }

        public function sendmail($subject="", $address = array(), $body="" ){
            if(count($address)!=0){
                $this->correo->IsSMTP();
                $this->correo->IsHTML(true);
                $this->correo->SMTPAuth = true;
                $this->correo->SMTPSecure = "ssl";
                $this->correo->SMTPDebug = SMTP::DEBUG_SERVER;
            
                /*Configuracion del host */
                //$this->correo->Host = "ssl://mail.macromautopartes.com";
                $this->correo->Host = "mail.macromautopartes.com";
                $this->correo->Username = "no-responder@macromautopartes.com";
                $this->correo->Password = "S1Oq07}L@k3-";
                $this->correo->Port = 465;

                /*Cuerpo del correo */
                $this->correo->setFrom( "no-responder@macromautopartes.com", "Macromautopartes");
                $this->correo->Subject = 'PHPMailer SMTP test';
                
                
                $this->correo->Body = "<h1>Correo de registro</h1>
                    <p>Gracias por registrarte </p>";
                
                foreach ($address as $key => $value) {
                    $this->correo->addAddress($value["email"],$value["nombre"]);
                    try{
                        if (!$this->correo->send()) {
                            return 'Mailer Error: ' . $this->correo->ErrorInfo;
                        } else {
                            
                        }
                        $this->correo->clearAddresses();
                    }catch(Exception $e){
                        return $e;
                    }
                    
                }
                return 'Message sent!';
            }else{

            }
            
        }

        public function main(){
            $correos = array();
            array_push($correos, array("email"=>"webmaster@macromautopartes.com", "nombre"=>"webmaster macromautopartes"));
            $this->jsonData["Bandera"] = true;
            $this->jsonData["Mensaje"] = $this->sendmail("",$correos,"");
            print json_encode($this->jsonData);
        }
    }

    $app = new email($array_principal);
    $app->main();