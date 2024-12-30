<?php
// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "almacen1");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Recorremos los productos y actualizamos la cantidad
foreach ($_POST as $key => $value) {
    if (strpos($key, 'cantidad_') === 0 && is_numeric($value)) {
        $id = str_replace('cantidad_', '', $key);
        $cantidad_a_añadir = intval($value);

        // Actualizar la cantidad en la base de datos
        $sql = "UPDATE productos SET cantidad = cantidad + $cantidad_a_añadir WHERE id = $id";
        $conn->query($sql);
    }
}

$conn->close();

// Redirigir de vuelta a la página de añadir productos
header("Location: añadir_productos.php");
exit;
?>
