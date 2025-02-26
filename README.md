# NeverLate
A project for school. This is an app that let's users create a routine, set a destination, and receive the time they should depart. (Disclaimer: this was made as part of a 'Vibe Coding' exercise. Most of the code was made with AI. Manual changes were made to UI and API). 

# Save_routine.php
Receives input from this HTML form
```

      <h3>Save Current Routine</h3>
      <form id="save-routine-form">
          <div>
              <label for="routine-name">Routine Name:</label>
              <input type="text" id="routine-name" placeholder="e.g., Weekday Morning, Weekend, etc." required>
          </div>
          <button type="submit" class="save-routine-btn">Save Routine</button>
      </form>

```
API is called from script.js
(text insead of json is still in place for now for debuggin purposes)
```
document.getElementById('save-routine-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const routineName = document.getElementById('routine-name').value;
    const userId = sessionStorage.getItem('user_id');
    
    if (routineName && routineList.length > 0) {
        // Create form data
        const formData = new FormData();
        formData.append('name', routineName);
        formData.append('activities', JSON.stringify(routineList));
        formData.append('user_id', userId);
        
        // Submit form
        fetch('save_routine.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(text => {
            const data = JSON.parse(text); 
            console.log('Response from server:', data)
            if (data.success) {
                alert('Routine saved successfully!');
                document.getElementById('routine-name').value = '';
                loadSavedRoutines();
            } else {
                alert('Error saving routine: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error parsing JSON:', error);
            console.error('Raw server response:', text);
            alert('An error occurred while saving the routine.');
        });
    } else {
        alert('Please enter a routine name and add at least one activity.');
    }
});
```
save_routine.php sends form info to DB

```
<?php
// save_routine.php - Save a routine to the database
session_start();
include 'config/database.php';
include 'models/routine.php';
include 'models/routine_activity.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    // name = routinename, not username
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $activities = isset($_POST['activities']) ? json_decode($_POST['activities'], true) : [];

    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    if (empty($name) || empty($activities)) {
        echo json_encode(['success' => false, 'message' => 'Missing required data']);
        exit();
    }
    
    // Create routine
    $routine = new Routine();
    $routine->user_id = $_SESSION['user_id'];
    $routine->name = $name;
    
    if (!$user_id) {
        echo "Error: User ID not found.";
    }

    if ($name && !empty($activities) && $user_id) {
        // Insert routine into the 'routines' table
        echo "Name: " . var_export($name, true) . "<br>";
        echo "Activities: " . var_export($activities, true) . "<br>";
        echo "User ID: " . var_export($user_id, true) . "<br>";
        $stmt = $pdo->prepare("INSERT INTO routines (routine_name, user_id) VALUES (?, ?)");
        $stmt->execute([$name, $user_id]);
        $routine_id = $pdo->lastInsertId();  // Get the last inserted routine_id

        // Insert activities into the 'activities' table
        $stmt = $pdo->prepare("INSERT INTO activities (routine_id, activity_name, user_id) VALUES (?, ?, ?)");
        foreach ($activities as $activity) {
            $stmt->execute([$routine_id, $activity['name'], $user_id]);
        }

        // Respond with success
        echo json_encode(['success' => true]);
    } else {
        // Respond with an error
        echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    }
}
?>
```
