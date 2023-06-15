<?php
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    require_once "PHPMailer/Exception.php";
    require_once "PHPMailer/PHPMailer.php";
    require_once "PHPMailer/SMTP.php";

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;

    class SendMail{
        
        var $correo;

        public function __construct() {
            $this->correo = new PHPMailer();
        }

        public function __destruct() {
            unset($this->correo);
        }

        static function ejemplo(){
            return "hola mundo";
        }

        public function Send($subject="", $address = array(), $body="" ){
            if(count($address)!=0){
                $this->correo->IsSMTP();
                $this->correo->IsHTML(true);
                $this->correo->SMTPAuth = true;
                $this->correo->SMTPSecure = "ssl";
                #$this->correo->SMTPDebug = SMTP::DEBUG_SERVER;
            
                /*Configuracion del host */
                //$this->correo->Host = "ssl://mail.macromautopartes.com";
                $this->correo->Host = "mail.macromautopartes.com";
                $this->correo->Username = "no-responder@macromautopartes.com";
                $this->correo->Password = "S1Oq07}L@k3-";
                $this->correo->Port = 465;

                /*Cuerpo del correo */
                $this->correo->setFrom( "no-responder@macromautopartes.com", "Macromautopartes");
                $this->correo->Subject = $subject;
                
                
                $this->correo->Body = $body;
                
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
    }