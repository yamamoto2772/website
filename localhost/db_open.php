<?php
   #山本
   $dbserver = "localhost";
   #$dbserver = "mysql310.phy.lolipop.lan";
   $dbname = "Corelista";
   $dbuser = "LAA1617845";
   $dbpasswd ="dbpasswd";

   #$dbserver = "localhost";
   #$dbname = "team4";
   #$dbuser = "root";
   #$dbpasswd = ""; 


$opt = [
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_EMULATE_PREPARES => false,
PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
];
$pdo = new PDO('mysql:host=' . $dbserver . ';dbname='.$dbname,
                $dbuser, $dbpasswd, $opt );
?>