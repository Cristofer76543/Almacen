<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root"; // Cambia esto si tienes un usuario diferente
$password = "";     // Cambia esto si tienes una contraseña
$dbname = "almacen1";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consulta para obtener los datos de productos
$sql = "SELECT datos_producto.nombre_producto, datos_producto.nombre_marca, 
        datos_producto.precio, categorias_producto.estante, categorias_producto.demanda, 
        categorias_producto.proveedor 
        FROM datos_producto 
        JOIN categorias_producto ON datos_producto.id_producto = categorias_producto.id_producto";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Productos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        h1 {
            text-align: center;
            padding: 20px;
            color: #333;
        }

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            background-color: #fff;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        table th {
            background-color: #4CAF50;
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        .no-data {
            text-align: center;
            color: #999;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <h1>Lista de Productos</h1>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Marca</th>
                    <th>Precio (MXN)</th>
                    <th>Estante</th>
                    <th>Demanda</th>
                    <th>Proveedor</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nombre_producto']); ?></td>
                        <td><?php echo htmlspecialchars($row['nombre_marca']); ?></td>
                        <td><?php echo htmlspecialchars($row['precio']); ?></td>
                        <td><?php echo htmlspecialchars($row['estante']); ?></td>
                        <td><?php echo htmlspecialchars($row['demanda']); ?></td>
                        <td><?php echo htmlspecialchars($row['proveedor']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-data">No hay productos registrados en la base de datos.</p>
    <?php endif; ?>

    <?php $conn->close(); ?>
</body>
</html>
