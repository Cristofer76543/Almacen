<?php
// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "almacen1");

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Consultar los productos
$sql = "SELECT id, nombre, descripcion, precio, imagen, cantidad FROM capacitores";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Añadir Productos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .product-container { display: flex; flex-wrap: wrap; gap: 20px; }
        .product { border: 1px solid #ddd; padding: 10px; border-radius: 5px; width: calc(33.33% - 20px); box-sizing: border-box; }
        .product img { max-width: 100%; height: auto; display: block; margin-bottom: 10px; }
        .product h2 { margin: 10px 0; }
        .product p { margin: 5px 0; }
        button { background-color: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #45a049; }
    </style>
</head>
<body>
    <h1>Productos Disponibles</h1>
    <div class="product-container">
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="product">
                <img src="<?= htmlspecialchars($row['imagen']) ?>" alt="<?= htmlspecialchars($row['nombre']) ?>" width="100" height="100"> <!-- Ajusta el tamaño según sea necesario -->
                <h2><?= htmlspecialchars($row['nombre']) ?></h2>
                <p><?= htmlspecialchars($row['descripcion']) ?></p>
                <p><strong>Precio:</strong> $<?= number_format($row['precio'], 2) ?></p>
                <p><strong>Cantidad disponible:</strong> <?= $row['cantidad'] ?></p>
                <button onclick="addToCart(<?= $row['id'] ?>, <?= $row['cantidad'] ?>)">Añadir al Carrito</button>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No hay productos disponibles.</p>
    <?php endif; ?>
    </div>
    
    <script>
    function addToCart(productId, maxQuantity) {
        // Solicitar la cantidad al usuario
        const quantity = prompt(`Cantidad disponible: ${maxQuantity}\n¿Cuántas unidades quieres añadir?`, "1");
        if (quantity !== null) {
            // Validar la cantidad ingresada
            const parsedQuantity = parseInt(quantity, 10);
            if (isNaN(parsedQuantity) || parsedQuantity <= 0 || parsedQuantity > maxQuantity) {
                alert("Por favor, introduce una cantidad válida.");
            } else {
                // Realizar la solicitud al servidor para añadir al carrito
                const tableName = 'capacitores';
                
                // Usar fetch para enviar la solicitud al servidor
                fetch(`carrito.php?action=add&id=${productId}&tabla=${tableName}&quantity=${parsedQuantity}`)
                    .then(response => response.text())
                    .then(data => {
                        alert(data); // Muestra la respuesta del servidor (por ejemplo, "Producto añadido al carrito")
                    })
                    .catch(error => {
                        console.error("Error al añadir al carrito:", error);
                        alert("Hubo un error al añadir el producto al carrito.");
                    });
            }
        }
    }
    </script>
</body>
</html>
