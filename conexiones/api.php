<?php
require 'basedatosconexion.php';

class Usuario {
    private $conn;
    
    public function __construct()
    {
       $this->conn = connection::dbConnection();    
    }

    public function getUsuarios() {
        $sql = "SELECT * FROM usuario";
        $resultado = $this->conn->query($sql);
    
        if ($resultado->num_rows > 0) {
            $usuarios = array();
    
            while ($columna = $resultado->fetch_assoc()) {
                $usuarios[] = $columna;
            }
            echo json_encode($usuarios);
        } else {
            echo json_encode(array('mensaje' => 'No se encontraron usuarios'));
        }
    }
}

class Cliente {
    private $conn;

    public function __construct() {
        $this->conn = connection::dbConnection();
    }

    public function getClientes() {
        $sql = "SELECT c.id, c.nombre, c.apellidos, c.email, c.telefono FROM Cliente c";
    
        $resultado = $this->conn->query($sql);
    
        if ($resultado->num_rows > 0) {
            $clientes = array();
    
            while ($columna = $resultado->fetch_assoc()) {
                $clientes[] = $columna;
            }
            echo json_encode($clientes);
        } else {
            echo json_encode(array('mensaje' => 'No se encontraron clientes'));
        }
    }
    
    public function postCliente($nombreCliente, $apellidos, $email, $telefono) {
        $sql = "INSERT INTO cliente (nombre, apellidos, email, telefono) VALUES (?, ?, ?, ?)";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("ssss", $nombreCliente, $apellidos, $email, $telefono);
        
        if ($statement->execute()) {
            header('Content-Type: application/json');
            echo json_encode(array('mensaje' => 'Cliente registrado correctamente'));
        } else {
            header('Content-Type: application/json');
            echo json_encode(array('mensaje' => 'Error al registrar cliente: ' . $statement->error));
        }
    }

    public function deleteCliente($id) {
        $sql = "DELETE FROM cliente WHERE id = ?";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("i", $id);
        
        if ($statement->execute()) {
            header('Content-Type: application/json', true, 200);
            echo json_encode(array('mensaje' => 'Cliente eliminado correctamente'));
        } else {
            header('Content-Type: application/json', true, 500);
            echo json_encode(array('mensaje' => 'Error al eliminar cliente: ' . $statement->error));
        }
    }

    public function getClientePorId($clienteId) {
        $sql = "SELECT id, nombre, apellidos, email, telefono FROM cliente WHERE id = ?";
    
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("i", $clienteId);
        $statement->execute();
        
        $resultado = $statement->get_result();
        
        if ($resultado->num_rows > 0) {
            $cliente = $resultado->fetch_assoc();
            header('Content-Type: application/json');
            echo json_encode($cliente);
        } else {
            header('Content-Type: application/json', true, 404);
            echo json_encode(array('mensaje' => 'Cliente no encontrado'));
        }
    }

    public function putCliente($id, $nombreCliente, $apellidos, $email, $telefono) {
        $sql = "UPDATE cliente SET nombre = ?, apellidos = ?, email = ?, telefono = ? WHERE id = ?";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("ssssi", $nombreCliente, $apellidos, $email, $telefono, $id);
        
        if ($statement->execute()) {
            header('Content-Type: application/json', true, 200);
            echo json_encode(array('mensaje' => 'Cliente actualizado correctamente'));
        } else {
            header('Content-Type: application/json', true, 500);
            echo json_encode(array('mensaje' => 'Error al actualizar cliente: ' . $statement->error));
        }
    }
}

class Producto {
    private $conn;

    public function __construct() {
        $this->conn = connection::dbConnection();
    }

    public function getProductos() {
        $sql = "SELECT p.id, p.sku, p.nombre, p.descripcion, c.nombre AS categoria, p.stock, p.precio 
                FROM Producto p
                INNER JOIN Categoria c ON p.categoriaID = c.id";
    
        $resultado = $this->conn->query($sql);
    
        if ($resultado->num_rows > 0) {
            $productos = array();
    
            while ($columna = $resultado->fetch_assoc()) {
                $productos[] = $columna;
            }
            echo json_encode($productos);
        } else {
            echo json_encode(array('mensaje' => 'No se encontraron productos'));
        }
    }

    public function postProducto($sku, $nombreProducto, $descripcion, $categoriaID, $stock, $precio) {
        $sql = "INSERT INTO producto (sku, nombre, descripcion, categoriaID, stock, precio) VALUES (?, ?, ?, ?, ?, ?)";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("issiii", $sku, $nombreProducto, $descripcion, $categoriaID, $stock, $precio);
        
        if ($statement->execute()) {
            header('Content-Type: application/json');
            echo json_encode(array('mensaje' => 'Producto registrado correctamente'));
        } else {
            header('Content-Type: application/json');
            echo json_encode(array('error' => 'Error al registrar producto: ' . $statement->error));
        }
    }
    
    private function verificarCategoriaExistente($categoriaID) {
        $sql = "SELECT id FROM categoria WHERE id = ?";
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("i", $categoriaID);
        $statement->execute();
        $statement->store_result();
        return $statement->num_rows > 0;
    }

    public function deleteProducto($id) {
        $sql = "DELETE FROM producto WHERE id = ?";
        
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("i", $id);
        
        if ($statement->execute()) {
            header('Content-Type: application/json', true, 200);
            echo json_encode(array('mensaje' => 'Producto eliminado correctamente'));
        } else {
            header('Content-Type: application/json', true, 500);
            echo json_encode(array('mensaje' => 'Error al eliminar producto: ' . $statement->error));
        }
    }

    public function putStockProducto($productoID, $nuevoStock) {
        // Preparar la consulta SQL para actualizar el stock del producto
        $sql = "UPDATE producto SET stock = ? WHERE id = ?";
        
        // Preparar la declaración y enlazar los parámetros
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("ii", $nuevoStock, $productoID);
        
        // Ejecutar la consulta
        if ($statement->execute()) {
            // Devolver una respuesta JSON de éxito
            header('Content-Type: application/json');
            echo json_encode(array('mensaje' => 'Stock del producto actualizado correctamente'));
        } else {
            // Devolver una respuesta JSON de error
            header('Content-Type: application/json', true, 500);
            echo json_encode(array('error' => 'Error al actualizar el stock del producto: ' . $statement->error));
        }
    }
    
}

class Categoria{
    private $conn;

    public function __construct() {
        $this->conn = connection::dbConnection();
    }

    public function getCategorias() {
        $sql = "SELECT c.id, c.nombre FROM Categoria c";
    
        $resultado = $this->conn->query($sql);
    
        if ($resultado->num_rows > 0) {
            $categorias = array();
    
            while ($columna = $resultado->fetch_assoc()) {
                $categorias[] = $columna;
            }
            echo json_encode($categorias);
        } else {
            echo json_encode(array('mensaje' => 'No se encontraron categorias'));
        }
    }
}

class Carrito{
    private $conn;

    public function __construct() {
        $this->conn = connection::dbConnection();
    }

    public function getCarrito() {
        $sql = "SELECT c.id, cl.nombre AS cliente, p.nombre AS producto, e.estado AS estado, c.cantidad, c.precio_total
                FROM Carrito c
                INNER JOIN Cliente cl ON c.clienteID = cl.id
                INNER JOIN Producto p ON c.productoID = p.id
                INNER JOIN Estado e ON c.estadoID = e.id";
    
        $resultado = $this->conn->query($sql);
    
        if ($resultado->num_rows > 0) {
            $carrito = array();
    
            while ($columna = $resultado->fetch_assoc()) {
                $carrito[] = $columna;
            }
            echo json_encode($carrito);
        } else {
            echo json_encode(array('mensaje' => 'Carrito está vacío'));
        }
    }

    public function postCarrito($clienteID, $productoID, $estadoID, $cantidad, $precioTotal) {
        // Preparar la consulta SQL para insertar el producto en el carrito
        $clienteExistente = $this->verificarClienteExistente($clienteID);
        if (!$clienteExistente) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(array('mensaje' => 'La categoría especificada no existe'));
            return;
        }

        $estadoExistente = $this->verificarEstadoExistente($estadoID);
        if (!$estadoExistente) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(array('mensaje' => 'La categoría especificada no existe'));
            return;
        }

        $productoExistente = $this->verificarProductoExistente($productoID);
        if (!$productoExistente) {
            header('Content-Type: application/json', true, 400);
            echo json_encode(array('mensaje' => 'La categoría especificada no existe'));
            return;
        }

        $sql = "INSERT INTO carrito (clienteID, productoID, estadoID, cantidad, precio_total) 
                VALUES (?, ?, ?, ?, ?)";
        
        $statement = $this->conn->prepare($sql);

        $statement->bind_param("iiidd", $clienteID, $productoID, $estadoID, $cantidad, $precioTotal);

        if ($statement->execute()) {
            header('Content-Type: application/json');
            echo json_encode(array('mensaje' => 'Producto registrado correctamente'));
        } else {
            header('Content-Type: application/json');
            echo json_encode(array('error' => 'Error al registrar producto: ' . $statement->error));
        }
    }

    private function verificarClienteExistente($clienteID) {
        $sql = "SELECT id FROM cliente WHERE id = ?";
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("i", $clienteID);
        $statement->execute();
        $statement->store_result();
        return $statement->num_rows > 0;
    }
    
    private function verificarProductoExistente($productoID) {
        $sql = "SELECT id FROM producto WHERE id = ?";
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("i", $productoID);
        $statement->execute();
        $statement->store_result();
        return $statement->num_rows > 0;
    }
    
    private function verificarEstadoExistente($estadoID) {
        $sql = "SELECT id FROM estado WHERE id = ?";
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("i", $estadoID);
        $statement->execute();
        $statement->store_result();
        return $statement->num_rows > 0;
    }

    public function deleteCarrito($id) {
        // Obtener la cantidad eliminada del carrito y el ID del producto asociado
        $sql = "SELECT productoID, cantidad FROM carrito WHERE id = ?";
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("i", $id);
        $statement->execute();
        $statement->bind_result($productoID, $cantidadEliminada);
        $statement->fetch();
        $statement->close();
        
        // Eliminar el elemento del carrito
        $sql = "DELETE FROM carrito WHERE id = ?";
        $statement = $this->conn->prepare($sql);
        $statement->bind_param("i", $id);
        
        if ($statement->execute()) {
            // Revertir la cantidad eliminada del carrito al stock del producto
            $sql = "UPDATE producto SET stock = stock + ? WHERE id = ?";
            $statement = $this->conn->prepare($sql);
            $statement->bind_param("ii", $cantidadEliminada, $productoID);
            $statement->execute();
    
            header('Content-Type: application/json', true, 200);
            echo json_encode(array('mensaje' => 'Carrito eliminado correctamente'));
        } else {
            header('Content-Type: application/json', true, 500);
            echo json_encode(array('mensaje' => 'Error al eliminar carrito: ' . $statement->error));
        }
    }
    
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($_SERVER['REQUEST_URI'] === '/QuibixPC/conexiones/api.php/Usuario') {
        $usuario = new Usuario();
        $usuario->getUsuarios();
    } elseif ($_SERVER['REQUEST_URI'] === '/QuibixPC/conexiones/api.php/Cliente') {
        $cliente = new Cliente();
        $cliente->getClientes();
    } elseif ($_SERVER['REQUEST_URI'] === '/QuibixPC/conexiones/api.php/Producto') {
        $producto = new Producto();
        $producto->getProductos();
    } elseif ($_SERVER['REQUEST_URI'] === '/QuibixPC/conexiones/api.php/Categoria') {
        $categoria = new Categoria();
        $categoria->getCategorias();
    } elseif ($_SERVER['REQUEST_URI'] === '/QuibixPC/conexiones/api.php/Carrito') {
        $categoria = new Carrito();
        $categoria->getCarrito();
    } else {
        $uriSegments = explode('/', $_SERVER['REQUEST_URI']);
        $clienteId = end($uriSegments);
        
        $productoId = end($uriSegments);
        
        if (is_numeric($clienteId)) {
            $cliente = new Cliente();
            $cliente->getClientePorId($clienteId);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SERVER['REQUEST_URI'] === '/QuibixPC/conexiones/api.php/Cliente') {
        $nombreCliente = $_POST['nombreCliente'];
        $apellidos = $_POST['apellidos'];
        $email = $_POST['email'];
        $telefono = $_POST['telefono'];
        $cliente = new Cliente(); 
        $cliente->postCliente($nombreCliente, $apellidos, $email, $telefono);
    } elseif ($_SERVER['REQUEST_URI'] === '/QuibixPC/conexiones/api.php/Producto') {
        $sku = $_POST['sku'];
        $nombreProducto = $_POST['nombreProducto'];
        $descripcion = $_POST['descripcion'];
        $categoriaID = $_POST['categoriaID'];
        $stock = $_POST['stock'];
        $precio = $_POST['precio'];
        $producto = new Producto(); 
        $producto->postProducto($sku, $nombreProducto, $descripcion, $categoriaID, $stock, $precio);
    }elseif ($_SERVER['REQUEST_URI'] === '/QuibixPC/conexiones/api.php/Carrito') {
        // Obtener los datos del carrito de la solicitud POST
        $clienteID = $_POST['clienteID'];
        $productoID = $_POST['productoID'];
        $estadoID = 1; // Estado inicial: Pendiente de pago
        $cantidad = $_POST['cantidad'];
        $precioTotal = $_POST['precioTotal'];
    
        // Instanciar un objeto Carrito y llamar al método correspondiente para agregar el producto al carrito
        $carrito = new Carrito();
        $carrito->postCarrito($clienteID, $productoID, $estadoID, $cantidad, $precioTotal);
    }    
} if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $uriSegments = explode('/', $_SERVER['REQUEST_URI']);
    $lastSegment = end($uriSegments);

    if (is_numeric($lastSegment)) {
        // Verificar si la URI es para la actualización de un cliente
        if (strpos($_SERVER['REQUEST_URI'], '/QuibixPC/conexiones/api.php/Cliente') !== false) {
            // Procesar la actualización del cliente
            $datosCliente = json_decode(file_get_contents("php://input"), true);
            
            if (isset($datosCliente['nombreCliente'], $datosCliente['apellidos'], $datosCliente['email'], $datosCliente['telefono'])) {
                $nombre = $datosCliente['nombreCliente'];
                $apellidos = $datosCliente['apellidos'];
                $email = $datosCliente['email'];
                $telefono = $datosCliente['telefono'];
                
                $cliente = new Cliente();
                $cliente->putCliente($lastSegment, $nombre, $apellidos, $email, $telefono);
            } else {
                // Devolver un mensaje de error si faltan datos en la solicitud
                header('Content-Type: application/json', true, 400);
                echo json_encode(array('mensaje' => 'Datos incompletos en la solicitud'));
            }
        } elseif (strpos($_SERVER['REQUEST_URI'], '/QuibixPC/conexiones/api.php/Producto') !== false) {
            // Procesar la actualización de stock del producto
            parse_str(file_get_contents("php://input"), $putData);
            $nuevoStock = $putData['nuevoStock'];

            $producto = new Producto();
            $producto->putStockProducto($lastSegment, $nuevoStock);
        } else {
            // Devolver un mensaje de error si la URI no es reconocida
            header('Content-Type: application/json', true, 400);
            echo json_encode(array('mensaje' => 'URI no válida para solicitud PUT'));
        }
    } else {
        // Devolver un mensaje de error si el último segmento de la URI no es numérico
        header('Content-Type: application/json', true, 400);
        echo json_encode(array('mensaje' => 'ID no válido en la URI'));
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $uriSegments = explode('/', $_SERVER['REQUEST_URI']);

    $lastSegment = end($uriSegments);

    if (is_numeric($lastSegment)) {
        if ($_SERVER['REQUEST_URI'] === '/QuibixPC/conexiones/api.php/Cliente/' . $lastSegment) {
            $cliente = new Cliente();
            $cliente->deleteCliente($lastSegment);
        } elseif ($_SERVER['REQUEST_URI'] === '/QuibixPC/conexiones/api.php/Producto/' . $lastSegment) {
            $producto = new Producto();
            $producto->deleteProducto($lastSegment);
        } elseif ($_SERVER['REQUEST_URI'] === '/QuibixPC/conexiones/api.php/Carrito/' . $lastSegment) {
            $producto = new Carrito();
            $producto->deleteCarrito($lastSegment);
        }else {
            header('Content-Type: application/json', true, 400);
            echo json_encode(array('mensaje' => 'ID de cliente o producto no válido'));
        }
    } else {
        header('Content-Type: application/json', true, 400);
        echo json_encode(array('mensaje' => 'ID de cliente o producto no válido'));
    }
}

