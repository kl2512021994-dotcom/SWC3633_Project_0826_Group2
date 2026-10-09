<?php
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

switch ($method) {
    case 'GET':
        if ($id) {
            $stmt = $pdo->prepare("SELECT b.*, u.full_name AS customer_name, t.type_name, e.title AS event_title 
                                   FROM bookings b
                                   JOIN users u ON b.customer_id = u.user_id
                                   JOIN tickets t ON b.ticket_type_id = t.ticket_type_id
                                   JOIN events e ON t.event_id = e.event_id
                                   WHERE b.booking_id = ?");
            $stmt->execute([$id]);
            $booking = $stmt->fetch();
            echo $booking ? json_encode(["status" => "success", "data" => $booking]) : (http_response_code(404) . json_encode(["status" => "error", "message" => "Booking not found"]));
        } else {
            $stmt = $pdo->query("SELECT b.*, u.full_name AS customer_name, t.type_name, e.title AS event_title 
                                 FROM bookings b
                                 JOIN users u ON b.customer_id = u.user_id
                                 JOIN tickets t ON b.ticket_type_id = t.ticket_type_id
                                 JOIN events e ON t.event_id = e.event_id");
            echo json_encode(["status" => "success", "data" => $stmt->fetchAll()]);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['customer_id']) || empty($data['ticket_type_id']) || empty($data['quantity_booked'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Missing required fields"]);
            break;
        }

        // Fetch ticket price to calculate total price
        $ticketStmt = $pdo->prepare("SELECT price, quantity_available FROM tickets WHERE ticket_type_id = ?");
        $ticketStmt->execute([$data['ticket_type_id']]);
        $ticket = $ticketStmt->fetch();

        if (!$ticket || $ticket['quantity_available'] < $data['quantity_booked']) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Not enough tickets available"]);
            break;
        }

        $total_price = $ticket['price'] * $data['quantity_booked'];

        /// Insert booking and deduct available tickets
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO bookings (customer_id, ticket_type_id, quantity_booked, total_price) VALUES (?, ?, ?, ?)");
    $stmt->execute([$data['customer_id'], $data['ticket_type_id'], $data['quantity_booked'], $total_price]);

    // 1. Capture the ID immediately after the INSERT query
    $booking_id = $pdo->lastInsertId();

    $updateTicket = $pdo->prepare("UPDATE tickets SET quantity_available = quantity_available - ? WHERE ticket_type_id = ?");
    $updateTicket->execute([$data['quantity_booked'], $data['ticket_type_id']]);

    // 2. Commit the transaction afterwards
    $pdo->commit();

    http_response_code(201);
    // 3. Use the stored variable in your response
    echo json_encode([
        "status" => "success", 
        "message" => "Booking confirmed", 
        "booking_id" => (int)$booking_id, 
        "total_price" => $total_price
    ]);
    break;

    case 'PUT':
        if (!$id) break;
        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $pdo->prepare("UPDATE bookings SET booking_status = ? WHERE booking_id = ?");
        $stmt->execute([$data['booking_status'], $id]);
        echo json_encode(["status" => "success", "message" => "Booking status updated"]);
        break;

    case 'DELETE':
        if (!$id) break;
        $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'Cancelled' WHERE booking_id = ?");
        $stmt->execute([$id]);
        echo json_encode(["status" => "success", "message" => "Booking cancelled successfully"]);
        break;
}
?>