<?php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // for development only
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit();

require_once __DIR__ . '/../config/config.php';


$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => 'DB_CONNECTION_FAILED']));
}


$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$body = json_decode(file_get_contents('php://input'), true);


if (preg_match('#^/api/v1/subjects/?$#', $path)) {
    handleSubjects($method, $body);
} elseif (preg_match('#^/api/v1/entries/?$#', $path)) {
    handleEntries($method, $body);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'ROUTE_NOT_FOUND']);
    exit();
}


function handleSubjects($method, $body)
{
    global $conn;

    switch ($method) {

        // @GET /api/v1/subjects
        // @returns array of all subjects
        //
        case 'GET':
            $result = $conn->query("SELECT * FROM subjects ORDER BY id ASC");
            if (!$result) {
                http_response_code(500);
                echo json_encode(['error' => $conn->error]);
                return;
            }
            echo json_encode($result->fetch_all(MYSQLI_ASSOC));
            break;

        // @POST /api/v1/subjects
        // @reqBody { name: "subjectName" }
        // @returns { id: insert_id, name: "subjectName" } 
        //
        case 'POST':
            $name = trim($body['name'] ?? '');
            if (!$name) {
                http_response_code(500);
                echo json_encode(['error' => 'NAME_IS_REQUIRED']);
                return;
            }

            $stmt = $conn->prepare("INSERT INTO subjects (name) VALUES (?)");
            $stmt->bind_param('s', $name);

            if ($stmt->execute()) {
                echo json_encode(['id' => $stmt->insert_id, 'name' => $name]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => $stmt->error]);
            }

            $stmt->close();
            break;

        // @DELETE /api/v1/subjects/ || /api/v1/subjects?id=subjectId
        // @reqBody null || { id: "subjectId" }
        // @returns { deleted: subjectId }
        //
        case 'DELETE':
            $id = intval($_GET['id'] ?? $body['id'] ?? 0);
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID_IS_REQUIRED']);
                return;
            }

            $stmt = $conn->prepare("DELETE FROM subjects WHERE id = ?");
            $stmt->bind_param('i', $id);

            if ($stmt->execute()) {
                echo json_encode(['deleted' => $id]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => $stmt->error]);
            }

            $stmt->close();
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'METHOD_NOT_ALLOWED']);
    }
}


function handleEntries($method, $body)
{
    global $conn;

    switch ($method) {

        // @GET /api/v1/entries?subject_id=subjectId
        // @returns { id: "entryId", subject_id: "subjectId", topic: "entryTopic", notes: "entryNotes", date: "entryDate" }
        // 
        case 'GET':
            $subject_id = intval($_GET['subject_id'] ?? 0);
            if (!$subject_id) {
                http_response_code(400);
                echo json_encode(['error' => '[subject_id]_IS_REQUIRED']);
                return;
            }

            $stmt = $conn->prepare("SELECT * FROM learning_entries WHERE subject_id = ? ORDER BY id DESC");
            $stmt->bind_param('i', $subject_id);

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                echo json_encode($result->fetch_all(MYSQLI_ASSOC));
            } else {
                http_response_code(500);
                echo json_encode(['error' => $stmt->error]);
            }

            $stmt->close();
            break;

        // @POST /api/v1/entries
        // @reqBody { subject_id: "subjectId", topic: "entryTopic", note: "entryNotes", date: "entryDate" }
        // @returns { id: "entryId", subject_id: "subjectId", topic: "entryTopic" }
        //
        case 'POST':
            $subject_id = intval($body['subject_id'] ?? 0);
            $topic = trim($body['topic'] ?? '');
            $notes = trim($body['notes'] ?? '');
            $date = date('Y-m-d');

            if (!$subject_id || !$topic) {
                http_response_code(400);
                echo json_encode(['error' => 'SUBJECT_AND_TOPIC_ARE_REQUIRED']);
                return;
            }

            $stmt = $conn->prepare("INSERT INTO learning_entries (subject_id, topic, notes, date) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('isss', $subject_id, $topic, $notes, $date);

            if ($stmt->execute()) {
                echo json_encode([
                    'id' => $subject_id,
                    'subject_id' => $subject_id,
                    'topic' => $topic
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => $stmt->error]);
            }

            $stmt->close();
            break;

        // @DELETE /api/v1/entries
        // @reqBody { id: "entryId" }
        // @returns { deleted: "entryId"}
        // 
        case 'DELETE':
            $id = intval($_GET['id'] ?? $body['id'] ?? 0);
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID_IS_REQUIRED']);
                return;
            }

            $stmt = $conn->prepare("DELETE FROM learning_entries WHERE id = ?");
            $stmt->bind_param('i', $id);

            if ($stmt->execute()) {
                echo json_encode(['deleted' => $id]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => $stmt->error]);
            }

            $stmt->close();
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'METHOD_NOT_ALLOWED']);
    }
}
