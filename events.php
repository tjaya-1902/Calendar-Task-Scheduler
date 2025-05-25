<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Not authorized');
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT e.id, e.title, e.start, e.end, u.username AS creator
    FROM events e
    JOIN users u ON e.created_by = u.id
    JOIN event_participants ep ON ep.event_id = e.id
    WHERE ep.user_id = ?
    GROUP BY e.id
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$events = [];

while ($row = $result->fetch_assoc()) {
    $event_id = $row['id'];

    // Get participants
    $stmt2 = $conn->prepare("
        SELECT u.username 
        FROM event_participants ep
        JOIN users u ON ep.user_id = u.id
        WHERE ep.event_id = ?
    ");
    $stmt2->bind_param("i", $event_id);
    $stmt2->execute();
    $participantsResult = $stmt2->get_result();

    $participants = [];
    while ($participantRow = $participantsResult->fetch_assoc()) {
        $participants[] = $participantRow['username'];
    }

    $events[] = [
        'id' => $event_id,
        'title' => $row['title'],
        'start' => $row['start'],
        'end' => $row['end'],
        'creator' => $row['creator'],
        'participants' => $participants
    ];
}

echo json_encode($events);
?>