document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');

  let currentSelection = null;

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    selectable: true,
    editable: true,
    events: 'events.php',

    select: function (info) {
        currentSelection = info;
        openModal();
    },

    eventDrop: function (info) {
        fetch('update_event.php', {
            method: 'POST',
            headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${info.event.id}&start=${info.event.startStr}&end=${info.event.endStr}`
        })
        .catch(err => {
            console.error('Error updating event:', err); 
            alert('Failed to update event.');
        });
    },

    eventClick: function (info) {
        // Fill modal with event details
        document.getElementById('detailTitle').textContent = info.event.title;
        document.getElementById('detailStart').textContent = info.event.start.toLocaleString();
        document.getElementById('detailEnd').textContent = info.event.end ? info.event.end.toLocaleString() : 'N/A';
        document.getElementById('detailCreator').textContent = info.event.extendedProps.creator || 'Unknown';

        const participants = info.event.extendedProps.participants || [];
        document.getElementById('detailParticipants').textContent = participants.join(', ');

        // Show modal
        document.getElementById('eventDetailsModal').style.display = 'block';
        document.getElementById('modalBackdrop').style.display = 'block';

        // Set delete button action
        const deleteBtn = document.getElementById('deleteEventBtn');
        deleteBtn.onclick = function () {
            fetch('delete_event.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `id=${info.event.id}`
            }).then(() => {
            closeDetailsModal();
            calendar.refetchEvents();
            });
        };
    }



    });

    calendar.render();

    function loadUserCheckboxes() {
    fetch('get_other_users.php')
        .then(res => res.json())
        .then(users => {
        const container = document.getElementById('userCheckboxes');
        container.innerHTML = ''; // Clear previous content
        users.forEach(user => {
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'user_ids[]';
            checkbox.value = user.id;
            const label = document.createElement('label');
            label.appendChild(checkbox);
            label.appendChild(document.createTextNode(' ' + user.username));
            container.appendChild(label);
            container.appendChild(document.createElement('br'));
            });
        })
        .catch(err => {
        console.error('Error loading users:', err); // *** CHANGE: Added error handling ***
        });
    }



    window.closeModal = function () {
        document.getElementById('eventModal').style.display = 'none';
        document.getElementById('modalBackdrop').style.display = 'none';
    };

    window.openModal = function () {
        document.getElementById('eventModal').style.display = 'block';
        document.getElementById('modalBackdrop').style.display = 'block';
        loadUserCheckboxes(); // load checkboxes every time modal opens
    };

    document.getElementById('eventForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const info = currentSelection;

        const title = this.title.value;
        const startTime = this.startTime.value;
        const endTime = this.endTime.value;
        const checkedBoxes = Array.from(document.querySelectorAll('input[name="user_ids[]"]:checked'));
        const selectedUserIds = Array.from(document.querySelectorAll('input[name="user_ids[]"]:checked'))
        .map(cb => cb.value)
        .join(',');

        const sendEmail = document.getElementById('sendEmailCheckbox').checked ? 1 : 0;

        if (!title || !startTime || !endTime) {
        alert("All fields are required.");
        return;
        }

        const startDateTime = info.startStr.slice(0, 10) + 'T' + startTime;
        const endDateTime = info.startStr.slice(0, 10) + 'T' + endTime;

        if (endDateTime <= startDateTime) {
            alert('End time must be after start time.');
            return;
        }

        if (checkedBoxes.length === 0) { 
            alert("At least one user must be invited.");
            return;
        }

        fetch('add_event.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `title=${encodeURIComponent(title)}&start=${startDateTime}&end=${endDateTime}&user_ids=${encodeURIComponent(selectedUserIds)}&sendEmail=${sendEmail}`
        }).then(() => {
        closeModal();
        calendar.refetchEvents();
        }).catch(err => {
        console.error('Error adding event:', err); // *** CHANGE: Added error handling ***
        alert('Failed to add event.');
        });
    });




    // View toggle
    const toggleBtn = document.getElementById('viewToggle');
    const tableView = document.getElementById('tableView');
    const tableBody = document.getElementById('eventTableBody');

    if (toggleBtn) {
        let isCalendarView = true;

        toggleBtn.addEventListener('click', () => {
            if (isCalendarView) {
                // Switch to table view
                calendarEl.style.display = 'none';
                tableView.style.display = 'block';
                toggleBtn.textContent = 'Switch to Calendar View';
                loadTableView();
            } else {
                // Switch to calendar view
                tableView.style.display = 'none';
                calendarEl.style.display = 'block';
                toggleBtn.textContent = 'Switch to Table View';
            }
            isCalendarView = !isCalendarView;
        });
    }

    function loadTableView() {
        fetch('events.php')
            .then(res => res.json())
            .then(events => {
                tableBody.innerHTML = '';
                events.forEach(event => {
                    const participants = event.participants?.join(', ') || 'None';
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${event.title}</td>
                        <td>${event.start}</td>
                        <td>${event.end}</td>
                        <td>${event.creator}</td>
                        <td>${participants}</td>
                    `;
                    tableBody.appendChild(row);
                });
            });
    }

    window.closeDetailsModal = function () {
        document.getElementById('eventDetailsModal').style.display = 'none';
        document.getElementById('modalBackdrop').style.display = 'none';
    };

});