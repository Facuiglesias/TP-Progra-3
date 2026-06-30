
using System;
using MySql.Data.MySqlClient;

namespace Progra3Card.Administrativo
{
    class Program
    {
        private static string connectionString = "Server=localhost;Database=mi_banco_db;Uid=root;Pwd=;";

        static void Main(string[] args)
        {
            bool salir = false;
            while (!salir)
            {
                Console.Clear();
                Console.WriteLine("========================================");
                Console.WriteLine("    SISTEMA ADMINISTRATIVO PROGRA3CARD");
                Console.WriteLine("========================================");
                Console.WriteLine("1. Emitir Nueva Tarjeta (Alta de Cliente)");
                Console.WriteLine("2. Listar Tarjetas");
                Console.WriteLine("3. Ver Detalle de una Tarjeta / Cliente");
                Console.WriteLine("4. Eliminar Tarjeta (Baja de Sistema)");
                Console.WriteLine("5. Emitir Nueva Liquidación Mensual");
                Console.WriteLine("6. Salir");
                Console.Write("Seleccione una opción: ");

                switch (Console.ReadLine())
                {
                    case "1": MenuEmitirTarjeta(); break;
                    case "2": MenuListarTarjetas(); break;
                    case "3": MenuVerDetalleTarjeta(); break;
                    case "4": MenuEliminarTarjeta(); break;
                    case "5": MenuEmitirLiquidacion(); break;
                    case "6": salir = true; break;
                }
            }
        }

        static void MenuEmitirTarjeta()
        {
            Console.Clear();
            Console.Write("DNI: ");
            string documento = Console.ReadLine();

            Console.Write("Tipo (DNI/PASAPORTE): ");
            string tipo = Console.ReadLine().ToUpper();

            Console.Write("Nombre: ");
            string nombre = Console.ReadLine();

            Console.Write("Apellido: ");
            string apellido = Console.ReadLine();

            Console.Write("Fecha nacimiento (YYYY-MM-DD): ");
            string fecha = Console.ReadLine();

            Console.Write("Email: ");
            string email = Console.ReadLine();

            Console.Write("Número de tarjeta (16 dígitos): ");
            string tarjeta = Console.ReadLine();

            string[] bancos = {
                "Banco Nación", "Banco Provincia", "Banco Galicia",
                "Banco Santander", "Banco BBVA", "Banco Macro"
            };

            for (int i = 0; i < bancos.Length; i++)
                Console.WriteLine($"{i + 1}. {bancos[i]}");

            int op = int.Parse(Console.ReadLine());
            string banco = bancos[op - 1];

            using (var conn = new MySqlConnection(connectionString))
            {
                conn.Open();

                string q1 = @"INSERT INTO usuarios(documento,tipo_doc,nombre,apellido,fecha_nacimiento,email)
                              VALUES(@d,@t,@n,@a,@f,@e)";

                var c1 = new MySqlCommand(q1, conn);
                c1.Parameters.AddWithValue("@d", documento);
                c1.Parameters.AddWithValue("@t", tipo);
                c1.Parameters.AddWithValue("@n", nombre);
                c1.Parameters.AddWithValue("@a", apellido);
                c1.Parameters.AddWithValue("@f", fecha);
                c1.Parameters.AddWithValue("@e", email);
                c1.ExecuteNonQuery();

                string q2 = @"INSERT INTO tarjetas(numero_tarjeta,banco_emisor,dni_titular)
                              VALUES(@nt,@b,@dni)";

                var c2 = new MySqlCommand(q2, conn);
                c2.Parameters.AddWithValue("@nt", tarjeta);
                c2.Parameters.AddWithValue("@b", banco);
                c2.Parameters.AddWithValue("@dni", documento);
                c2.ExecuteNonQuery();
            }

            Console.WriteLine("Cliente y tarjeta creados correctamente.");
            Console.ReadKey();
        }

        static void MenuEmitirLiquidacion()
        {
            Console.Clear();
            Console.Write("Número de cuenta: ");
            int cuenta = int.Parse(Console.ReadLine());

            Console.Write("Período (YYYY-MM): ");
            string periodo = Console.ReadLine();

            Console.Write("Fecha vencimiento (YYYY-MM-DD): ");
            string venc = Console.ReadLine();

            Console.Write("Total a pagar: ");
            decimal total = decimal.Parse(Console.ReadLine());

            Console.Write("Pago mínimo: ");
            decimal minimo = decimal.Parse(Console.ReadLine());

            using (var conn = new MySqlConnection(connectionString))
            {
                conn.Open();
                string q = @"INSERT INTO liquidaciones(num_cuenta,periodo,fecha_vencimiento,total_a_pagar,pago_minimo)
                             VALUES(@c,@p,@v,@t,@m)";

                var cmd = new MySqlCommand(q, conn);
                cmd.Parameters.AddWithValue("@c", cuenta);
                cmd.Parameters.AddWithValue("@p", periodo);
                cmd.Parameters.AddWithValue("@v", venc);
                cmd.Parameters.AddWithValue("@t", total);
                cmd.Parameters.AddWithValue("@m", minimo);
                cmd.ExecuteNonQuery();
            }

            Console.WriteLine("Liquidación emitida correctamente.");
            Console.ReadKey();
        }

        static void MenuListarTarjetas()
        {
            Console.Clear();
            ObtenerYMostrarTarjetas();
            Console.ReadKey();
        }

        static void MenuVerDetalleTarjeta()
        {
            Console.Write("Número de cuenta: ");
            int cuenta = int.Parse(Console.ReadLine());
            MostrarDetalleCompleto(cuenta);
            Console.ReadKey();
        }

        static void MenuEliminarTarjeta()
        {
            Console.Write("Número de cuenta: ");
            int cuenta = int.Parse(Console.ReadLine());
            Console.WriteLine(DarDeBajaTarjeta(cuenta)
                ? "Eliminada correctamente"
                : "No se encontró la cuenta");
            Console.ReadKey();
        }

        static void ObtenerYMostrarTarjetas()
        {
            using var conn = new MySqlConnection(connectionString);
            conn.Open();

            var cmd = new MySqlCommand("SELECT * FROM tarjetas", conn);
            var reader = cmd.ExecuteReader();

            while (reader.Read())
            {
                Console.WriteLine($"{reader["num_cuenta"]} - {reader["numero_tarjeta"]} - {reader["banco_emisor"]} - {reader["dni_titular"]}");
            }
        }

        static void MostrarDetalleCompleto(int cuenta)
        {
            using var conn = new MySqlConnection(connectionString);
            conn.Open();

            string q = @"SELECT * FROM usuarios u
                         INNER JOIN tarjetas t ON u.documento=t.dni_titular
                         WHERE t.num_cuenta=@c";

            var cmd = new MySqlCommand(q, conn);
            cmd.Parameters.AddWithValue("@c", cuenta);
            var reader = cmd.ExecuteReader();

            if (reader.Read())
            {
                Console.WriteLine($"Nombre: {reader["nombre"]} {reader["apellido"]}");
                Console.WriteLine($"DNI: {reader["documento"]}");
                Console.WriteLine($"Email: {reader["email"]}");
                Console.WriteLine($"Tarjeta: {reader["numero_tarjeta"]}");
                Console.WriteLine($"Banco: {reader["banco_emisor"]}");
                Console.WriteLine($"Saldo: {reader["saldo"]}");
            }
        }

        static bool DarDeBajaTarjeta(int cuenta)
        {
            using var conn = new MySqlConnection(connectionString);
            conn.Open();

            var cmd = new MySqlCommand("DELETE FROM tarjetas WHERE num_cuenta=@c", conn);
            cmd.Parameters.AddWithValue("@c", cuenta);

            return cmd.ExecuteNonQuery() > 0;
        }
    }
}
