<?php
    
    class Funciones{
        static function siAcceso($string=""){
            $arrayTemp = explode(",", $string);
            return in_array($_SESSION["rol"], $arrayTemp, true);
        }

        static function siAcceso2($array){
            return in_array($_SESSION["rol"], $array, true);
        }
    }