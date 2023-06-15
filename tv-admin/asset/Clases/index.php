<?php
session_destroy();
if($_SESSION["autentificacion"]!=1){
        header("Location: ../index.php");
}
