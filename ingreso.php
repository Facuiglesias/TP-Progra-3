<?php
session_start();
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $tipo_doc = $_POST["tipo_doc"];
    $documento = $_POST["documento"];
    $usuario = $_POST["usuario"];
    $password = $_POST["password"];

    $sql = "
        SELECT *
        FROM usuarios
        WHERE documento = ?
        AND tipo_doc = ?
        AND usuario = ?
        AND password = ?
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ssss",
        $documento,
        $tipo_doc,
        $usuario,
        $password
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {

        $datos = $resultado->fetch_assoc();

        $_SESSION["documento"] = $datos["documento"];
        $_SESSION["nombre"] = $datos["nombre"];
        $_SESSION["apellido"] = $datos["apellido"];
        $_SESSION["usuario"] = $datos["usuario"];

        header("Location: resumen.php");
        exit();
    } else {
        echo "Credenciales incorrectas.";
    }
}
?>