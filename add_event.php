<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit('Not authorized');
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

$user_id = $_SESSION['user_id'];

$title = $_POST['title'];
$start = $_POST['start'];
$end = $_POST['end'];
$user_ids = isset($_POST['user_ids']) ? explode(',', $_POST['user_ids']) : [];
$sendEmail = isset($_POST['sendEmail']) && $_POST['sendEmail'] == '1';

// Include the current user/event creator
if (!in_array($user_id, $user_ids)) {
    $user_ids[] = $user_id;
}

// Insert the event 
$stmt = $conn->prepare("INSERT INTO events (title, start, end, created_by) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssi", $title, $start, $end, $user_id);
$stmt->execute();
$event_id = $stmt->insert_id;

// Insert participants into event_participants table
$stmt_part = $conn->prepare("INSERT INTO event_participants (event_id, user_id) VALUES (?, ?)");
foreach ($user_ids as $uid) {
    $stmt_part->bind_param("ii", $event_id, $uid);
    $stmt_part->execute();
}


if ($sendEmail) {
    foreach ($user_ids as $participant_id) {
        // Get email from user ID
        $stmt = $conn->prepare("SELECT email, username FROM users WHERE id = ?");
        $stmt->bind_param("i", $participant_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && !empty($user['email'])) {
            $mail = new PHPMailer(true);

         try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'your email';
            $mail->Password = 'your password';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('oneandyy@gmail.com', 'Task Scheduler');
            $mail->addAddress($user['email']);

            $mail->isHTML(true);
            $mail->Subject = 'New Task Invitation';

            // Format date and time
            $startDateTime = new DateTime($start);
            $endDateTime = new DateTime($end);
            $date = $startDateTime->format('F j, Y'); 
            $startTime = $startDateTime->format('H:i');
            $endTime = $endDateTime->format('H:i');

            // Email Content
            $mail->Body = '
                <p>Hello <strong>' . htmlspecialchars($user['username']) . '</strong>,</p>
                <p>You have been invited to the event: <strong>' . htmlspecialchars($title) . '</strong></p>
                <p>Date: ' . $date . '<br>
                Time:' . $startTime . ' - ' . $endTime . '</p>
            ';

            $mail->send();
        } catch (Exception $e) {
            error_log("Email error: " . $mail->ErrorInfo);
            echo "Mailer Error: " . $mail->ErrorInfo;
        }
    }
}
}

echo $event_id;
?>
