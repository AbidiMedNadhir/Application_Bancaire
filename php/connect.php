<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $base = "banque_db";

    try {
       $connexion = new PDO("mysql:host=$servername;dbname=$base",$username,$password);
    }
    catch(PDOException $e)
    {
     echo "Connection failed: " . $e->getMessage(); 
    }
?>
