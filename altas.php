<?php

include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $tipo_doc = $_POST["tipo_doc"];
    $documento = $_POST["documento"];
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $fecha_nacimiento = $_POST["fecha_nacimiento"];
    $email = $_POST["email"];
    $usuario = $_POST["usuario"];
    $passwordA = $_POST["passwordA"];
    $passwordB = $_POST["passwordB"];

    if ($passwordA != $passwordB) {
        die("Las contraseñas no coinciden.");
    }

    if ($tipo_doc != "DNI" && $tipo_doc != "PASAPORTE") {
        die("Tipo de documento inválido.");
    }

    $sql = "
        SELECT *
        FROM usuarios
        WHERE documento = ?
        AND tipo_doc = ?
        AND nombre = ?
        AND apellido = ?
        AND fecha_nacimiento = ?
        AND email = ?
        AND usuario IS NULL
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ssssss",
        $documento,
        $tipo_doc,
        $nombre,
        $apellido,
        $fecha_nacimiento,
        $email
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 0) {
        die("No existe un cliente con esos datos o la cuenta ya fue activada.");
    }

    $update = "
        UPDATE usuarios
        SET usuario = ?, password = ?
        WHERE documento = ?
    ";

    $stmtUpdate = $conn->prepare($update);
    $stmtUpdate->bind_param(
        "sss",
        $usuario,
        $passwordA,
        $documento
    );

    if ($stmtUpdate->execute()) {
        echo "
        <h2>Cuenta activada correctamente</h2>
        <a href='ingreso.html'>Ir al inicio de sesión</a>
        ";
    } else {
        echo "Error al activar la cuenta.";
    }
}
?>