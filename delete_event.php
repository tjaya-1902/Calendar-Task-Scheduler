<?php
include 'db.php';

$id = $_POST['id'];

// delete associated participants
$stmt_part = $conn->prepare("DELETE FROM event_participants WHERE event_id = ?");
$stmt_part->bind_param("i", $id);
$stmt_part->execute();

// delete the event
$stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

echo "Event deleted.";
?>