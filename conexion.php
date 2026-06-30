<?php

$host = "localhost";
$usuario = "root";
$password = "";
$base_datos = "mi_banco_db";

$conn = new mysqli($host, $usuario, $password, $base_datos);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>