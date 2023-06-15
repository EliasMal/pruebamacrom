<?php
    
    require_once("nusoap/lib/nusoap.php");

    class redpack{
        private $sw;
        private $url = "https://ws.redpack.com.mx/RedpackAPI_WS/services/RedpackWS?wsdl";

        public function __construct() {
            $this->sw = new nusoap_client($this->url, 'wsdl');
        }

        public function ____destruct(){
           
        }

        public function cotizacion($params){
           $result =  $this->sw->call('cotizacionNacional',$params);
           
            /* if($this->sw->fault){
                return '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>'; print_r($result); echo '</pre>';
            }else{
               return $this->sw->getError();
            } */

           return $result;
            
            
        }


    }