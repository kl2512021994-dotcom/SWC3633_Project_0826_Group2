<?php
// event-api/users.php
require_once 'db.php';

header("Content-Type: application/json; charset=UTF-8");

$method = $_SERVER['REQUEST_METHOD'];

// Parse incoming request parameters or raw JSON body
$input = json_decode(file_get_contents('php://input'), true);

// Extract ID parameter from URL path if passed (e.g., users.php?id=1 or via htaccess rewrite)
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

switch ($method) {
    case 'GET':
        if ($id) {
            // Fetch single user by ID
            $stmt = $pdo->prepare("SELECT user_id, full_name, email, password, role, created_at FROM users WHERE user_id = :id");
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                http_response_code(200);
                echo json_encode(["status" => "success", "data" => $user]);
            } else {
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => "User not found"]);
            }
        } else {
            // Fetch all users
            $stmt = $pdo->query("SELECT user_id, full_name, email, password, role, created_at FROM users ORDER BY user_id ASC");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            http_response_code(200);
            echo json_encode(["status" => "success", "data" => $users]);
        }
        break;

    case 'POST':
        // Register / Create new user
        if (empty($input['full_name']) || empty($input['email']) || empty($input['password'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Full name, email, and password are required"]);
            exit;
        }

        $role = isset($input['role']) ? $input['role'] : 'Customer';

        // Check if email already exists
        $checkStmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
        $checkStmt->execute([':email' => $input['email']]);
        if ($checkStmt->fetch()) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Email address is already registered"]);
            exit;
        }

        // Insert new user
        $sql = "INSERT INTO users (full_name, email, password, role) VALUES (:full_name, :email, :password, :role)";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([
            ':full_name' => $input['full_name'],
            ':email'     => $input['email'],
            ':password'  => $input['password'],
            ':role'      => $role
        ]);

        if ($success) {
            http_response_code(201);
            echo json_encode([
                "status"  => "success",
                "message" => "User created successfully",
                "user_id" => $pdo->lastInsertId()
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to create user account"]);
        }
        break;

    case 'PUT':
        // Update user account details
        if (!$id) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "User ID is required for update"]);
            exit;
        }

        if (empty($input['full_name']) || empty($input['email'])) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Full name and email are required"]);
            exit;
        }

        $role = isset($input['role']) ? $input['role'] : 'Customer';

        $sql = "UPDATE users SET full_name = :full_name, email = :email, role = :role WHERE user_id = :id";
        $stmt = $pdo->prepare($sql);
        $success = $stmt->execute([
            ':full_name' => $input['full_name'],
            ':email'     => $input['email'],
            ':role'      => $role,
            ':id'        => $id
        ]);

        if ($success) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "User updated successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to update user"]);
        }
        break;

    case 'DELETE':
        // Delete user account
        if (!$id) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "User ID is required for deletion"]);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = :id");
        $success = $stmt->execute([':id' => $id]);

        if ($success && $stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => "User deleted successfully"]);
        } else {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "User not found or already deleted"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Method Not Allowed"]);
        break;
}
?>