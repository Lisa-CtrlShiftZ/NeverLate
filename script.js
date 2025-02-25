// Check if user is logged in
function checkSession() {
    fetch('api/check_session.php')
        .then(response => response.json())
        .then(data => {
            if (!data.logged_in) {
                window.location.href = 'loginregisterform.html';
            }
        });
}

// Run session check when page loads
checkSession();

document.querySelectorAll('.nav-btn').forEach(button => {
    button.addEventListener('click', () => {
        // Remove active class from all buttons and sections
        document.querySelectorAll('.nav-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.section').forEach(section => section.classList.remove('active'));
        
        // Add active class to clicked button
        button.classList.add('active');
        
        // Show corresponding section
        const sectionId = button.id.split('-')[0] + '-section';
        document.getElementById(sectionId).classList.add('active');
    });
});

// Morning Routine Functionality
const routineList = [];
const routineListElement = document.getElementById('routine-list');
const savedRoutinesSelect = document.getElementById('saved-routines');
const noRoutineMsg = document.getElementById('no-routine-msg');
const totalRoutineTimeElement = document.getElementById('total-routine-time');
const timeAdjustmentElement = document.getElementById('time-adjustment');

document.getElementById('add-routine-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const activity = document.getElementById('activity').value;
    const time = parseInt(document.getElementById('time').value);
    
    if (activity && time) {
        routineList.push({
            id: Date.now() + Math.floor(Math.random() * 1000),
            activity: activity,
            time: time
        });
        
        updateRoutineDisplay();
        document.getElementById('activity').value = '';
        document.getElementById('time').value = '15';
        
        // Trigger routine updated event
        document.dispatchEvent(new Event('routineUpdated'));
    }
});

// Save Routine Form Submission
document.getElementById('save-routine-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const routineName = document.getElementById('routine-name').value;
    
    if (routineName && routineList.length > 0) {
        // Create form data
        const formData = new FormData();
        formData.append('name', routineName);
        formData.append('activities', JSON.stringify(routineList));
        
        // Submit form
        fetch('save_routine.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Routine saved successfully!');
                document.getElementById('routine-name').value = '';
                loadSavedRoutines();
            } else {
                alert('Error saving routine: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving the routine.');
        });
    } else {
        alert('Please enter a routine name and add at least one activity.');
    }
});

// Load Saved Routines
function loadSavedRoutines() {
    fetch('get_routines.php')
        .then(response => response.json())
        .then(data => {
            // Clear the select options except the first one
            savedRoutinesSelect.innerHTML = '<option value="">-- Select a routine --</option>';
            
            // Add each routine as an option
            data.forEach(routine => {
                const option = document.createElement('option');
                option.value = routine.id;
                option.textContent = routine.name;
                option.dataset.activities = JSON.stringify(routine.activities);
                savedRoutinesSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Load Routine Button Click
document.getElementById('load-routine-btn').addEventListener('click', function() {
    const selectedOption = savedRoutinesSelect.options[savedRoutinesSelect.selectedIndex];
    
    if (selectedOption && selectedOption.value) {
        const activities = JSON.parse(selectedOption.dataset.activities);
        
        // Clear current routine
        routineList.length = 0;
        
        // Add activities from saved routine
        activities.forEach(activity => {
            routineList.push({
                id: Date.now() + Math.floor(Math.random() * 1000),
                activity: activity.activity,
                time: activity.time
            });
        });
        
        updateRoutineDisplay();
        updateTotalRoutineTime();
        updateWakeUpTime();
    } else {
        alert('Please select a routine to load.');
    }
});

// Delete Routine Button Click
document.getElementById('delete-routine-btn').addEventListener('click', function() {
    const selectedOption = savedRoutinesSelect.options[savedRoutinesSelect.selectedIndex];
    
    if (selectedOption && selectedOption.value) {
        if (confirm('Are you sure you want to delete this routine?')) {
            // Create form data
            const formData = new FormData();
            formData.append('id', selectedOption.value);
            
            // Submit form
            fetch('delete_routine.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Routine deleted successfully!');
                    loadSavedRoutines();
                } else {
                    alert('Error deleting routine: ' + data.message);
                }
            });
        }
    } else {
        alert('Please select a routine to delete.');
    }
});

function updateRoutineDisplay() {
    if (routineList.length === 0) {
        noRoutineMsg.style.display = 'block';
        timeAdjustmentElement.style.display = 'none';
    } else {
        noRoutineMsg.style.display = 'none';
        timeAdjustmentElement.style.display = 'block';
        
        // Clear the routine list and time adjustment sections
        routineListElement.innerHTML = '';
        timeAdjustmentElement.innerHTML = '<h3>Adjust Time Allocation</h3>';
        
        // Add each routine item to the display
        routineList.forEach(item => {
            const routineItem = document.createElement('div');
            routineItem.className = 'routine-item';
            routineItem.innerHTML = `
                <span>${item.activity}</span>
                <span>${item.time} minutes</span>
                <button class="delete-btn" data-id="${item.id}">Remove</button>
            `;
            routineListElement.appendChild(routineItem);
            
            // Create a slider for time adjustment
            const sliderRow = document.createElement('div');
            sliderRow.className = 'slider-row';
            sliderRow.innerHTML = `
                <label for="slider-${item.id}">${item.activity}:</label>
                <input type="range" id="slider-${item.id}" data-id="${item.id}" 
                       min="1" max="60" value="${item.time}">
                <span class="time-display" id="time-${item.id}">${item.time} min</span>
            `;
            timeAdjustmentElement.appendChild(sliderRow);
            
            // Add event listener to the slider
            sliderRow.querySelector(`#slider-${item.id}`).addEventListener('input', function() {
                const id = this.getAttribute('data-id');
                const newTime = parseInt(this.value);
                document.getElementById(`time-${id}`).textContent = `${newTime} min`;
                
                // Update the time in the routineList
                const itemIndex = routineList.findIndex(i => i.id == id);
                if (itemIndex !== -1) {
                    routineList[itemIndex].time = newTime;
                    updateTotalRoutineTime();
                    updateWakeUpTime();
                }
            });
        });
        
        // Add delete button functionality
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const index = routineList.findIndex(item => item.id == id);
                if (index !== -1) {
                    routineList.splice(index, 1);
                    updateRoutineDisplay();
                    updateTotalRoutineTime();
                    updateWakeUpTime();
                }
            });
        });
        
        updateTotalRoutineTime();
    }
}

function updateTotalRoutineTime() {
    const totalMinutes = routineList.reduce((total, item) => total + item.time, 0);
    totalRoutineTimeElement.textContent = totalMinutes;
    document.getElementById('routine-display').textContent = totalMinutes;
}

// Arrival Time Functionality
const arrivalForm = document.getElementById('arrival-form');
const arrivalInfo = document.getElementById('arrival-info');

arrivalForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const destination = document.getElementById('destination').value;
    const arrivalTime = document.getElementById('arrival-time').value;
    const commuteTime = parseInt(document.getElementById('commute-time').value);
    const prepBuffer = parseInt(document.getElementById('prep-buffer').value);
    
    if (destination && arrivalTime && commuteTime) {
        // Display the info
        document.getElementById('dest-display').textContent = destination;
        document.getElementById('arrival-display').textContent = formatTime(arrivalTime);
        document.getElementById('commute-display').textContent = commuteTime;
        document.getElementById('buffer-display').textContent = prepBuffer;
        
        // Show the info section
        arrivalInfo.style.display = 'block';
        
        // Update wake-up and leave times
        updateWakeUpTime();
        
        // Schedule arrival check
        scheduleArrivalCheck(destination, arrivalTime);
        
        // Save to localStorage
        const arrivalSettings = {
            destination,
            arrivalTime,
            commuteTime,
            prepBuffer,
            date: new Date().toISOString().split('T')[0]
        };
        localStorage.setItem('arrivalSettings', JSON.stringify(arrivalSettings));
    }
});

function updateWakeUpTime() {
    const arrivalTime = document.getElementById('arrival-time').value;
    if (!arrivalTime) return;
    
    const commuteTime = parseInt(document.getElementById('commute-time').value) || 0;
    const prepBuffer = parseInt(document.getElementById('prep-buffer').value) || 0;
    const routineTime = routineList.reduce((total, item) => total + item.time, 0);
    
    const [hours, minutes] = arrivalTime.split(':').map(Number);
    
    // Calculate leave time (arrival time - commute time)
    let leaveHour = hours;
    let leaveMinute = minutes - commuteTime;
    
    while (leaveMinute < 0) {
        leaveMinute += 60;
        leaveHour -= 1;
    }
    
    if (leaveHour < 0) {
        leaveHour += 24;
    }
    
    // Calculate wake-up time (leave time - (routine time + buffer))
    let wakeHour = leaveHour;
    let wakeMinute = leaveMinute - (routineTime + prepBuffer);
    
    while (wakeMinute < 0) {
        wakeMinute += 60;
        wakeHour -= 1;
    }
    
    if (wakeHour < 0) {
        wakeHour += 24;
    }
    
    document.getElementById('leave-time').textContent = formatTimeFromHourMinute(leaveHour, leaveMinute);
    document.getElementById('wakeup-time').textContent = formatTimeFromHourMinute(wakeHour, wakeMinute);
}

function formatTimeFromHourMinute(hour, minute) {
    const period = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour % 12 || 12;
    return `${displayHour}:${minute.toString().padStart(2, '0')} ${period}`;
}

function formatTime(timeString) {
    const [hours, minutes] = timeString.split(':').map(Number);
    const period = hours >= 12 ? 'PM' : 'AM';
    const displayHour = hours % 12 || 12;
    return `${displayHour}:${minutes.toString().padStart(2, '0')} ${period}`;
}

// Arrival check modal functionality
const arrivalModal = document.getElementById('arrival-modal');
const modalDestination = document.getElementById('modal-destination');
const onTimeBtn = document.getElementById('on-time-btn');
const lateBtn = document.getElementById('late-btn');

function scheduleArrivalCheck(destination, arrivalTime) {
    // For demo purposes, set a short timeout
    // In a real app, you would calculate this based on the current time and arrival time
    setTimeout(() => {
        modalDestination.textContent = destination;
        arrivalModal.style.display = 'block';
    }, 10000); // Show after 10 seconds for demo
}

onTimeBtn.addEventListener('click', () => {
    logArrival(true);
    arrivalModal.style.display = 'none';
});

lateBtn.addEventListener('click', () => {
    logArrival(false);
    arrivalModal.style.display = 'none';
});

function logArrival(onTime) {
    // Get current statistics
    let stats = JSON.parse(localStorage.getItem('punctualityStats') || '{"onTime": 0, "late": 0, "history": []}');
    
    // Update statistics
    if (onTime) {
        stats.onTime += 1;
    } else {
        stats.late += 1;
    }
    
    // Add to history
    const arrivalSettings = JSON.parse(localStorage.getItem('arrivalSettings') || '{}');
    stats.history.push({
        date: new Date().toISOString().split('T')[0],
        destination: arrivalSettings.destination || 'Unknown',
        arrivalTime: arrivalSettings.arrivalTime || 'Unknown',
        onTime: onTime
    });
    
    // Save updated statistics
    localStorage.setItem('punctualityStats', JSON.stringify(stats));
    
    // Update the statistics display
    updateStatisticsDisplay();
    
    // In a real app, you would also send this data to the server
    /* Example AJAX call:
    fetch('save_arrival.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            'action': 'log_arrival',
            'onTime': onTime,
            'destination': arrivalSettings.destination,
            'arrivalTime': arrivalSettings.arrivalTime
        })
    })
    .then(response => response.json())
    .then(data => console.log(data))
    .catch(error => console.error('Error:', error));
    */
}

// Statistics functionality
function updateStatisticsDisplay() {
    const stats = JSON.parse(localStorage.getItem('punctualityStats') || '{"onTime": 0, "late": 0, "history": []}');
    
    // Update the statistics values
    document.getElementById('ontime-count').textContent = stats.onTime;
    document.getElementById('late-count').textContent = stats.late;
    
    const total = stats.onTime + stats.late;
    const percentage = total > 0 ? Math.round((stats.onTime / total) * 100) : 0;
    document.getElementById('ontime-percentage').textContent = `${percentage}%`;
    
    // Update the table
    const tableBody = document.querySelector('#arrivals-table tbody');
    tableBody.innerHTML = '';
    
    // Show the most recent 10 entries
    const recentHistory = stats.history.slice(-10).reverse();
    
    recentHistory.forEach(entry => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${entry.date}</td>
            <td>${entry.destination}</td>
            <td>${formatTime(entry.arrivalTime)}</td>
            <td style="color: ${entry.onTime ? '#2ecc71' : '#e74c3c'}">${entry.onTime ? 'On Time' : 'Late'}</td>
        `;
        tableBody.appendChild(row);
    });
    
    // In a real app, you would update the chart here
    // For simplicity, we're skipping chart implementation in this example
}

// Initialize the app
function initApp() {
    // Load saved routine
    const savedRoutine = localStorage.getItem('morningRoutine');
    if (savedRoutine) {
        routineList.push(...JSON.parse(savedRoutine));
        updateRoutineDisplay();
    }
    
    // Load saved arrival settings
    const savedArrival = localStorage.getItem('arrivalSettings');
    if (savedArrival) {
        const settings = JSON.parse(savedArrival);
        // Only use if it's from today
        if (settings.date === new Date().toISOString().split('T')[0]) {
            document.getElementById('destination').value = settings.destination;
            document.getElementById('arrival-time').value = settings.arrivalTime;
            document.getElementById('commute-time').value = settings.commuteTime;
            document.getElementById('prep-buffer').value = settings.prepBuffer;
            
            // Update display
            document.getElementById('dest-display').textContent = settings.destination;
            document.getElementById('arrival-display').textContent = formatTime(settings.arrivalTime);
            document.getElementById('commute-display').textContent = settings.commuteTime;
            document.getElementById('buffer-display').textContent = settings.prepBuffer;
            
            arrivalInfo.style.display = 'block';
            updateWakeUpTime();
        }
    }
    
    // Initialize statistics display
    updateStatisticsDisplay();
}

// Call init when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', initApp);

// Save routine when changes are made
function saveRoutine() {
    localStorage.setItem('morningRoutine', JSON.stringify(routineList));
}

// Add event listeners for routine updates
document.addEventListener('routineUpdated', saveRoutine);
