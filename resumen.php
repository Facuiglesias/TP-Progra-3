<?php
session_start();

if (!isset($_SESSION["documento"])) {
    header("Location: ingreso.html");
    exit();
}

include("conexion.php");

$documento = $_SESSION["documento"];

$sqlActual = "
SELECT l.*, t.numero_tarjeta, t.banco_emisor
FROM liquidaciones l
INNER JOIN tarjetas t
    ON l.num_cuenta = t.num_cuenta
WHERE t.dni_titular = ?
ORDER BY l.periodo DESC
LIMIT 1
";

$stmt = $conn->prepare($sqlActual);
$stmt->bind_param("s", $documento);
$stmt->execute();

$actual = $stmt->get_result()->fetch_assoc();


$sqlHistorial = "
SELECT l.*
FROM liquidaciones l
INNER JOIN tarjetas t
    ON l.num_cuenta = t.num_cuenta
WHERE t.dni_titular = ?
ORDER BY l.periodo DESC
";

$stmt2 = $conn->prepare($sqlHistorial);
$stmt2->bind_param("s", $documento);
$stmt2->execute();

$historial = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Tarjetas - Resumen</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 font-sans min-h-screen flex flex-col">

    <header class="bg-[#004691] text-white text-center py-4 shadow-md">
        <h1 class="text-xl font-semibold">
            Mis <span class="font-bold">Tarjetas</span>
        </h1>
    </header>

    <main class="flex-grow max-w-6xl mx-auto p-6 w-full">

        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">
                    ¡Hola, <?php echo $_SESSION["nombre"]; ?>!
                </h2>

                <p class="text-gray-500">
                    Bienvenido nuevamente a tu portal de liquidaciones.
                </p>
            </div>

            <a href="logout.php"
               class="bg-red-500 hover:bg-red-600 text-white px-5 py-2 rounded-full transition">
                Cerrar sesión
            </a>
        </div>

        <!-- ÚLTIMA LIQUIDACIÓN -->
        <section class="mb-10">

            <h3 class="text-2xl font-bold text-[#004691] mb-4">
                Última Liquidación
            </h3>

            <?php if($actual) { ?>

            <div class="bg-white rounded-xl shadow-lg p-8 border-l-8 border-[#004691]">

                <div class="grid md:grid-cols-2 gap-6">

                    <div>
                        <p class="text-sm text-gray-500 uppercase">
                            Período
                        </p>

                        <p class="text-2xl font-bold text-gray-800">
                            <?php echo $actual["periodo"]; ?>
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 uppercase">
                            Banco Emisor
                        </p>

                        <p class="text-xl text-gray-700">
                            <?php echo $actual["banco_emisor"]; ?>
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 uppercase">
                            Total a pagar
                        </p>

                        <p class="text-3xl font-bold text-red-600">
                            $<?php echo number_format($actual["total_a_pagar"],2,",","."); ?>
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 uppercase">
                            Pago mínimo
                        </p>

                        <p class="text-3xl font-bold text-green-600">
                            $<?php echo number_format($actual["pago_minimo"],2,",","."); ?>
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500 uppercase">
                            Fecha de vencimiento
                        </p>

                        <p class="text-xl text-gray-700">
                            <?php echo $actual["fecha_vencimiento"]; ?>
                        </p>
                    </div>

                </div>

            </div>

            <?php } else { ?>

                <div class="bg-yellow-100 border border-yellow-300 p-6 rounded-lg">
                    No existen liquidaciones disponibles.
                </div>

            <?php } ?>

        </section>

        <!-- HISTORIAL -->

        <section>

            <h3 class="text-2xl font-bold text-[#004691] mb-4">
                Historial de Liquidaciones
            </h3>

            <div class="bg-white rounded-xl shadow-lg overflow-hidden">

                <table class="w-full">

                    <thead class="bg-[#004691] text-white">

                        <tr>
                            <th class="p-4 text-left">Período</th>
                            <th class="p-4 text-left">Vencimiento</th>
                            <th class="p-4 text-left">Total</th>
                            <th class="p-4 text-left">Pago Mínimo</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php while($fila = $historial->fetch_assoc()) { ?>

                        <tr class="border-b hover:bg-gray-50 transition">

                            <td class="p-4">
                                <?php echo $fila["periodo"]; ?>
                            </td>

                            <td class="p-4">
                                <?php echo $fila["fecha_vencimiento"]; ?>
                            </td>

                            <td class="p-4 font-semibold text-red-600">
                                $<?php echo number_format($fila["total_a_pagar"],2,",","."); ?>
                            </td>

                            <td class="p-4 font-semibold text-green-600">
                                $<?php echo number_format($fila["pago_minimo"],2,",","."); ?>
                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

    <footer class="bg-gray-50 text-[10px] text-gray-500 text-center p-4 border-t border-gray-200">
        Portal Oficial de Consultas de Liquidaciones Progra3card.
    </footer>

</body>
</html>