<?php
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

switch ($method) {
    case 'GET':
        if ($id) {
            $stmt = $pdo->prepare("SELECT e.*, v.venue_name, u.full_name AS organiser_name 
                                   FROM events e 
                                   JOIN venues v ON e.venue_id = v.venue_id 
                                   JOIN users u ON e.organiser_id = u.user_id 
                                   WHERE e.event_id = ?");
            $stmt->execute([$id]);
            $event = $stmt->fetch();
            if ($event) {
                echo json_encode(["status" => "success", "data" => $event]);
            } else {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "Event not found"]);
            }
        } else {
            // SEARCH & FILTERING IMPLEMENTATION
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            $status = isset($_GET['status']) ? trim($_GET['status']) : '';

            $sql = "SELECT e.*, v.venue_name, u.full_name AS organiser_name 
                    FROM events e 
                    JOIN venues v ON e.venue_id = v.venue_id 
                    JOIN users u ON e.organiser_id = u.user_id 
                    WHERE 1=1";
            $params = [];

            if (!empty($search)) {
                $sql .= " AND (e.title LIKE ? OR e.description LIKE ? OR v.venue_name LIKE ?)";
                $searchTerm = "%{$search}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            if (!empty($status)) {
                $sql .= " AND e.status = ?";
                $params[] = $status;
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $events = $stmt->fetchAll();

            echo json_encode([
                "status" => "success",
                "count" => count($events),
                "data" => $events
            ]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['title']) || empty($data['event_date']) || empty($data['venue_id']) || empty($data['organiser_id'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Missing required fields"]);
            break;
        }
        $desc = $data['description'] ?? '';
        $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, venue_id, organiser_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['title'], $desc, $data['event_date'], $data['venue_id'], $data['organiser_id']]);
        
        http_response_code(201);
        echo json_encode(["status" => "success", "message" => "Event created successfully", "event_id" => $pdo->lastInsertId()]);
        break;

    case 'PUT':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Event ID required"]);
            break;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, event_date = ?, status = ? WHERE event_id = ?");
        $stmt->execute([$data['title'], $data['description'], $data['event_date'], $data['status'], $id]);
        echo json_encode(["status" => "success", "message" => "Event updated successfully"]);
        break;

    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Event ID required"]);
            break;
        }
        $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ?");
        $stmt->execute([$id]);
        echo json_encode(["status" => "success", "message" => "Event deleted successfully"]);
        break;
}
?>
