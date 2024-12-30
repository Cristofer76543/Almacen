<?php
// Habilitar la visualización de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "almacen1");
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Iniciar sesión y manejar el carrito
session_start();

// Si no existe el carrito, inicializarlo
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // Añadir producto al carrito
    if ($action == 'add' && isset($_GET['id']) && isset($_GET['tabla']) && isset($_GET['quantity'])) {
        $id = $_GET['id'];
        $tabla = $_GET['tabla']; // Obtener la tabla desde el GET
        $cantidad = (int)$_GET['quantity']; // Obtener la cantidad desde el GET

        // Validar la cantidad
        if ($cantidad <= 0) {
            echo "Por favor, ingresa una cantidad válida.";
            exit;
        }

        // Verificar si el producto ya está en el carrito
        if (!isset($_SESSION['carrito'][$tabla])) {
            $_SESSION['carrito'][$tabla] = []; // Si no existe la tabla, inicializamos el array
        }

        if (isset($_SESSION['carrito'][$tabla][$id])) {
            $_SESSION['carrito'][$tabla][$id] += $cantidad;
        } else {
            $_SESSION['carrito'][$tabla][$id] = $cantidad;
        }

        echo "Producto añadido al carrito.";
        exit;
    }

    // Eliminar producto del carrito
    if ($action == 'delete' && isset($_GET['id']) && isset($_GET['tabla'])) {
        $id = $_GET['id'];
        $tabla = $_GET['tabla'];
        unset($_SESSION['carrito'][$tabla][$id]);
        echo "Producto eliminado del carrito.";
        exit;
    }

    // Finalizar compra y generar el QR
    if ($action == 'finalize') {
        // Obtener los productos y calcular el total
        $productos = [];
        $total = 0;
        $fecha = date('Y-m-d H:i:s');

        foreach ($_SESSION['carrito'] as $tabla => $productosCarrito) {
            foreach ($productosCarrito as $id => $cantidad) {
                $sql = "SELECT nombre, precio FROM $tabla WHERE id = $id";
                $result = $conn->query($sql);
                if ($result && $row = $result->fetch_assoc()) {
                    $productos[] = [
                        'nombre' => $row['nombre'],
                        'precio' => $row['precio'],
                        'cantidad' => $cantidad,
                        'subtotal' => $row['precio'] * $cantidad
                    ];
                    $total += $row['precio'] * $cantidad;

                    // Actualizar stock
                    $conn->query("UPDATE $tabla SET cantidad = cantidad - $cantidad WHERE id = $id");
                }
            }
        }

        // Generar el contenido para el QR
        $contenidoQR = "Fecha: $fecha\n";
        foreach ($productos as $producto) {
            $contenidoQR .= "{$producto['nombre']} x{$producto['cantidad']} - {$producto['subtotal']}\n";
        }
        $contenidoQR .= "Total: $total\n";
        $contenidoQR .= "Gracias por tu compra. Para continuar con tu compra envía tu QR y un mensaje al número 5568895003.";

        // URL de la API de QRCode Monkey
        $url = "https://api.qr-code-generator.com/v1/create?access-token=YOUR_ACCESS_TOKEN";

        // Datos para generar el QR a través de la API de QRCode Monkey
        $data = [
            'qr_code_text' => $contenidoQR,
            'image_format' => 'PNG',  // Formato de imagen
            'qr_code_logo' => null,   // Si quieres un logo en el QR, lo puedes añadir aquí
            'frame_color' => '#ffffff',
            'qr_code_color' => '#000000'
        ];

        // Usar cURL para enviar la solicitud POST
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $qrImage = curl_exec($ch);
        curl_close($ch);

        // Limpiar carrito
        $_SESSION['carrito'] = [];

        // Mostrar mensaje de compra finalizada y el enlace de descarga del QR
        echo "<h1>Compra Finalizada</h1>";
        echo "<p>Total: $total</p>";
        echo "<p>Fecha: $fecha</p>";
        echo "<p><a href='data:image/png;base64," . base64_encode($qrImage) . "' download='compra_qr.png'>Descargar Código QR</a></p>";
        exit;
    }
}
?>

<!-- Página del carrito -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carrito de Compras</title>
</head>
<body>
    <h1>Carrito de Compras</h1>

    <?php
    // Verificar si el carrito está vacío
    if (empty($_SESSION['carrito']) || !array_filter($_SESSION['carrito'])) {
        echo "<p>Tu carrito está vacío. Agrega productos a tu carrito.</p>";
    } else {
        // Mostrar los productos en el carrito
        echo "<h2>Productos en el carrito:</h2>";
        foreach ($_SESSION['carrito'] as $tabla => $productos) {
            echo "<h3>Sección: " . ucfirst($tabla) . "</h3>";
            foreach ($productos as $id => $cantidad) {
                // Obtener los detalles del producto de la base de datos
                $sql = "SELECT nombre, precio, cantidad FROM $tabla WHERE id = $id";
                $result = $conn->query($sql);
                if ($result && $row = $result->fetch_assoc()) {
                    $subtotal = $row['precio'] * $cantidad;
                    echo "<p>{$row['nombre']} - Cantidad: $cantidad - Precio: {$row['precio']} - Subtotal: $subtotal";
                    echo " <a href='carrito.php?action=delete&id=$id&tabla=$tabla'>Eliminar</a></p>";
                } else {
                    echo "Error al obtener el producto.";
                }
            }
        }

        // Mostrar el total y el botón para finalizar la compra
        $total = 0;
        foreach ($_SESSION['carrito'] as $tabla => $productos) {
            foreach ($productos as $id => $cantidad) {
                $sql = "SELECT precio FROM $tabla WHERE id = $id";
                $result = $conn->query($sql);
                if ($result && $row = $result->fetch_assoc()) {
                    $total += $row['precio'] * $cantidad;
                }
            }
        }
        echo "<p>Total: $total</p>";
        echo "<a href='carrito.php?action=finalize'>Finalizar Compra</a>";
    }
    ?>
</body>
</html>
