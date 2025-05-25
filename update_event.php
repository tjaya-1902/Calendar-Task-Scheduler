<?php
include 'db.php';
$id = $_POST['id'];
$start = $_POST['start'];
$end = $_POST['end'];
$stmt = $conn->prepare("UPDATE events SET start = ?, end = ? WHERE id = ?");
$stmt->bind_param("ssi", $start, $end, $id);
$stmt->execute();
?>