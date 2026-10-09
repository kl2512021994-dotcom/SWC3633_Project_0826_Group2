<?php
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

switch ($method) {
    case 'GET':
        if ($id) {
            $stmt = $pdo->prepare("SELECT * FROM venues WHERE venue_id = ?");
            $stmt->execute([$id]);
            $venue = $stmt->fetch();
            echo $venue ? json_encode(["status" => "success", "data" => $venue]) : (http_response_code(404) . json_encode(["status" => "error", "message" => "Venue not found"]));
        } else {
            $stmt = $pdo->query("SELECT * FROM venues");
            echo json_encode(["status" => "success", "data" => $stmt->fetchAll()]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['venue_name']) || empty($data['address']) || empty($data['capacity'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Missing required fields"]);
            break;
        }
        $stmt = $pdo->prepare("INSERT INTO venues (venue_name, address, capacity, contact_phone) VALUES (?, ?, ?, ?)");
        $stmt->execute([$data['venue_name'], $data['address'], $data['capacity'], $data['contact_phone'] ?? null]);
        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "Venue created", "venue_id" => $pdo->lastInsertId()]);
        break;

    case 'PUT':
        if (!$id) break;
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $pdo->prepare("UPDATE venues SET venue_name = ?, address = ?, capacity = ?, contact_phone = ? WHERE venue_id = ?");
        $stmt->execute([$data['venue_name'], $data['address'], $data['capacity'], $data['contact_phone'], $id]);
        echo json_encode(["status" => "success", "message" => "Venue updated"]);
        break;

    case 'DELETE':
        if (!$id) break;
        $stmt = $pdo->prepare("DELETE FROM venues WHERE venue_id = ?");
        $stmt->execute([$id]);
        echo json_encode(["status" => "success", "message" => "Venue deleted"]);
        break;
}
?>
