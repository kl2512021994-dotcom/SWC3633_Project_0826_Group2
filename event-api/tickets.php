<?php
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$eventId = isset($_GET['event_id']) ? intval($_GET['event_id']) : null;

switch ($method) {
    case 'GET':
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM tickets WHERE ticket_type_id = ?");
            $stmt->execute([$id]);
            $ticket = $stmt->fetch();
            if ($ticket) {
                echo json_encode(["status" => "success", "data" => $ticket]);
            } else {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "Ticket type not found"]);
            }
        } else {
            if ($eventId) {
                $stmt = $pdo->prepare("SELECT * FROM tickets WHERE event_id = ?");
                $stmt->execute([$eventId]);
            } else {
                $stmt = $pdo->query("SELECT * FROM tickets");
            }
            $tickets = $stmt->fetchAll();
            echo json_encode(["status" => "success", "count" => count($tickets), "data" => $tickets]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $eId = $data['event_id'] ?? null;
        $name = $data['type_name'] ?? $data['name'] ?? null;
        $price = $data['price'] ?? null;
        $qty = $data['quantity_available'] ?? $data['quantity'] ?? null;

        if (!$eId || !$name || $price === null || $qty === null) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Missing required fields"]);
            exit();
        }

        $stmt = $pdo->prepare("INSERT INTO tickets (event_id, type_name, price, quantity_available) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$eId, $name, $price, $qty])) {
            http_response_code(201);
            echo json_encode(["status" => "success", "message" => "Ticket tier created successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to create ticket tier"]);
        }
        break;

    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Missing ticket ID"]);
            exit();
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $name = $data['type_name'] ?? $data['name'] ?? null;
        $price = $data['price'] ?? null;
        $qty = $data['quantity_available'] ?? $data['quantity'] ?? null;

        $stmt = $pdo->prepare("UPDATE tickets SET type_name = ?, price = ?, quantity_available = ? WHERE ticket_type_id = ?");
        if ($stmt->execute([$name, $price, $qty, $id])) {
            echo json_encode(["status" => "success", "message" => "Ticket tier updated"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to update ticket tier"]);
        }
        break;

    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Missing ticket ID"]);
            exit();
        }
        $stmt = $pdo->prepare("DELETE FROM tickets WHERE ticket_type_id = ?");
        if ($stmt->execute([$id])) {
            echo json_encode(["status" => "success", "message" => "Ticket tier deleted"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to delete ticket tier"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method not allowed"]);
        break;
}
?>