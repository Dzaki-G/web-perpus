<?php
function getConnection(): PDO{
    $host = "localhost";
    $port = 3308;
    $database = "db_perpus";
    $username = "root";
    $password = "";

    return new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
}
?>


