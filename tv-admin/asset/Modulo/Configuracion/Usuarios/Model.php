<?php
class Model{
    private $conn;
    public function __construct($array=array()) {
        $this->conn = new HelperMySql($array["server"],$array["user"],$array["pass"],$array["db"]);
    }
    
    public function __destruct() {
        unset($this->conn);
    }
    
   
}