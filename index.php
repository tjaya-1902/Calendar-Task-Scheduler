<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task Scheduler</title>

    <!-- FULLCALENDAR -->
    <script src="fullcalendar/core/index.global.min.js"></script>
    <script src="fullcalendar/daygrid/index.global.min.js"></script>
    <script src="fullcalendar/interaction/index.global.min.js"></script>

    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>

<body>

    <!-- HEADER -->
    <header>
        <h2>Task Scheduler</h2>
        <?php if (isset($_SESSION['username'])): ?>
            <p>
                Logged in as: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> |
                <a href="logout.php">Logout</a>
            </p>
        <?php endif; ?>
    </header>

    <!-- CONTROLS -->
    <section id="controls">
        <button id="viewToggle">Switch to Table View</button>
    </section>

    <!-- CALENDAR VIEW -->
    <section id="calendarView">
        <div id="calendar"></div>
    </section>

    <!-- TABLE VIEW -->
    <section id="tableView">
        <h3 style="text-align: center;">Your Events</h3>
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Creator</th>
                    <th>Participants</th>
                </tr>
            </thead>
            <tbody id="eventTableBody"></tbody>
        </table>
    </section>

    <!-- ADD EVENT MODAL -->
    <section id="eventModal" class="modal">
        <h3>Add Event</h3>
        <form id="eventForm">
            <label for="title">Title:</label>
            <input id="title" type="text" name="title" required><br><br>

            <label for="startTime">Start Time:</label>
            <input id="startTime" type="time" name="startTime" required><br><br>

            <label for="endTime">End Time:</label>
            <input id="endTime" type="time" name="endTime" required><br><br>

            <label>Invite Users:</label><br>
            <div id="userCheckboxes"></div><br>

            <label for="sendEmailCheckbox">Send Invitation Email:</label><br>
            <input type="checkbox" name="sendEmail" id="sendEmailCheckbox"> Yes<br><br>

            <button type="submit">Add Event</button>
            <button type="button" onclick="closeModal()">Cancel</button>
        </form>
    </section>
    <div id="modalBackdrop" onclick="closeModal()"></div>

    <!-- EVENT DETAILS MODAL -->
    <section id="eventDetailsModal" class="modal">
        <h3>Event Details</h3>
        <p><strong>Title:</strong> <span id="detailTitle"></span></p>
        <p><strong>Start:</strong> <span id="detailStart"></span></p>
        <p><strong>End:</strong> <span id="detailEnd"></span></p>
        <p><strong>Created by:</strong> <span id="detailCreator"></span></p>
        <p><strong>Participants:</strong> <span id="detailParticipants"></span></p>
        <button id="deleteEventBtn">Delete</button>
        <button type="button" onclick="closeDetailsModal()">Close</button>
    </section>
    <div id="modalBackdrop" onclick="closeDetailsModal()"></div>

</body>
</html>