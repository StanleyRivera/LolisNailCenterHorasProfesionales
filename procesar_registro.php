<?php
// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "opos_db";

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener los datos del formulario
$nombre = $_POST['nombre'];
$apellido = $_POST['apellido'];
$telefono = $_POST['telefono'];
$direccion = $_POST['direccion'];
$email = $_POST['email'];
$nombre_usuario = $_POST['nombre_usuario'];
$clave = password_hash($_POST['clave'], PASSWORD_BCRYPT); // Encriptar la contraseña

// Empezar una transacción
$conn->begin_transaction();

try {
    // Insertar los datos en la tabla de clientes
    $sql_cliente = "INSERT INTO clientes (Nombre, Apellido, Telefono, Direccion, Email) VALUES (?, ?, ?, ?, ?)";
    $stmt_cliente = $conn->prepare($sql_cliente);
    if ($stmt_cliente === false) {
        throw new Exception('Error en la preparación de la consulta: ' . $conn->error);
    }
    $stmt_cliente->bind_param("sssss", $nombre, $apellido, $telefono, $direccion, $email);
    if (!$stmt_cliente->execute()) {
        throw new Exception('Error en la ejecución de la consulta: ' . $stmt_cliente->error);
    }
    $cliente_id = $conn->insert_id; // Obtener el ID del cliente recién insertado

    // Insertar los datos en la tabla de usuarios
    $sql_usuario = "INSERT INTO usuarios (NombreUsuario, Clave, TipoUsuario, ClienteID) VALUES (?, ?, 'cliente', ?)";
    $stmt_usuario = $conn->prepare($sql_usuario);
    if ($stmt_usuario === false) {
        throw new Exception('Error en la preparación de la consulta: ' . $conn->error);
    }
    $stmt_usuario->bind_param("ssi", $nombre_usuario, $clave, $cliente_id);
    if (!$stmt_usuario->execute()) {
        throw new Exception('Error en la ejecución de la consulta: ' . $stmt_usuario->error);
    }

    // Confirmar la transacción
    $conn->commit();

    // Enviar respuesta de éxito
    echo 'success';

} catch (Exception $e) {
    // Revertir la transacción si hay un error
    $conn->rollback();
    echo "Error al registrar el usuario: " . $e->getMessage();
}

// Cerrar la conexión
$conn->close();
?>
