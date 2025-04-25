<?php

class TravelDiaryController {

  private $db;

  private $errorMessage = "";

  public function __construct($input) {
    session_start();
    $this->db = new Database();
    $this->input = $input;

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
  }

  public function run() {
    $command = "welcome";
    if (isset($this->input["command"]) && ($this->input["command"] == "login" ||
        $this->input["command"] == "create_account" || $this->input["command"] == "signup" || isset($_SESSION["user_id"])))
      $command = $this->input["command"];

    switch($command) {
      case "login":
        $this->login();
        break;
      case "signup":
        $this->signup();
        break;
      case "create_account":
        $this->showSignup();
        break;
      case "home":
        $this->showHome();
        break;
      case "trips":
        $this->showTrips();
        break;
      case "entries":
        $this->showEntries();
        break;
      case "stats":
        $this->showStats();
        break;
      case "addtrip":
        $this->showAddTrip();
        break;
      case "create_trip":
        $this->addTrip();
        break;
      case "create_entry":
        $this->addEntry();
        break;
      case "addentry":
        $this->showAddEntry();
        break;
      case "delete_trip":
        $this->deleteTrip();
        break;
      case "delete_entry":
        $this->deleteEntry();
        break;
      case "edit_entry":
        $this->showEditEntry();
        break;
      case "save_entry_edits":
        $this->saveEntryEdits();
        break;
      case "edit_trip":
        $this->showEditTrip();
        break;
      case "save_trip_edits":
        $this->saveTripEdits();
        break;
      case "add_bucketlist":
        $this->addBucketListTrip();
        break;
      case "fetch_users":
        $this->fetchUsers();
        break;
      case "logout":
        $this->logout();
        break;
      case "welcome":
      default:
        $this->showLogin();
        break;
    }
  }

  public function login() {
    if (isset($_POST["email"]) && isset($_POST["password"]) && !empty($_POST["password"]) && !empty($_POST["email"])) {
        $results = $this->db->query("select * from project_users where email = $1;", $_POST["email"]);

        // if user email not found, prompt to create an account
        if (empty($results)) {
            $message = "<p class='alert alert-danger'>Email not found. Please create an account.</p>"; 
        } else {        // check that the user's password is correct
            $hashed_password = $results[0]["password"];
            $correct = password_verify($_POST["password"], $hashed_password);
            if ($correct) {          // if password correct, go to home page
                $_SESSION["user_id"] = $results[0]["id"];
                header("Location: ?command=home");
                exit;
            } else {          // if password incorrect, display message
                $message = "<p class='alert alert-danger'>Incorrect password</p>"; 
            }
        }
        $this->showLogin($message);
        return;
    }
    $message = "<p class='alert alert-danger'>Please fill out all fields</p>";
    $this->showLogin($message);
  }

  public function signup() {
    if (isset($_POST["name"]) && isset($_POST["email"]) && isset($_POST["password"]) && isset($_POST["confirmPassword"]) 
    && !empty($_POST["name"]) && !empty($_POST["email"]) && !empty($_POST["password"]) && !empty($_POST["confirmPassword"])) {
        // check if email already has account
        $results = $this->db->query("select * from project_users where email = $1;", $_POST["email"]);
        if (!empty($results)) {
            $message = "<p class='alert alert-danger'>An account already exists with that email</p>"; 
        } elseif (!preg_match("/^[\w\-.]+@([\w\-]+\.)+[\w\-]{2,4}$/", $_POST["email"])) {            // check if valid email
            $message = "<p class='alert alert-danger'>Please enter a valid email address</p>";
        } else {            // check passwords match
            if ($_POST["password"] == $_POST["confirmPassword"]) {
                // add user to user table
                $result = $this->db->query("insert into project_users (name, email, password) values ($1, $2, $3) returning id;",
                    $_POST["name"], $_POST["email"], password_hash($_POST["password"], PASSWORD_DEFAULT));
                $_SESSION["user_id"] = $result[0]["id"];
                // add entry for user in stats table
                $stat = $this->db->query("insert into project_stats (user_id) values ($1);", $_SESSION["user_id"]);
                // redirect to home page
                header("Location: ?command=home");
                exit;
            } else {
                $message = "<p class='alert alert-danger'>Passwords do not match</p>";
            }
        }
        $this->showSignup($message);
        return;
    }
    $message = "<p class='alert alert-danger'>Please fill out all fields</p>";
    $this->showSignup($message);
  }

  public function logout() {
    session_destroy();
    session_start();
    $this->showLogin();
  }

  public function addTrip() {
    $userId = $_SESSION["user_id"];
    if (isset($_POST["trip-name"]) && isset($_POST["start-date"]) && isset($_POST["location"]) && isset($_POST["latitude"]) && isset($_POST["longitude"]) 
    && !empty($_POST["trip-name"]) && !empty($_POST["start-date"]) && !empty($_POST["location"]) && !empty($_POST["latitude"]) && !empty($_POST["longitude"])) {
      $location = $_POST["location"];
      $start_date = $_POST["start-date"];
      $end_date = empty($_POST["end-date"]) ? null : $_POST["end-date"];
      $duration = $this->getDuration($start_date, $end_date);

      $collaborators = null;
      if (!empty($_POST["collaborators"])) {
          $ids = array_map('intval', $_POST["collaborators"]);
          $collaborators = '{' . implode(',', $ids) . '}';
      }
      
      // update bucket list visited status if location is not visited + in bucket list
      if (!$this->inTrips($location) && $this->onBucketList($location)) {
        $this->db->query("update project_bucketlist set visited = true where user_id = $1 and location = $2;", $userId, $location);
      }
      
      // insert new trip - collaborators and description are optional
      $result = $this->db->query(
        "insert into project_trips 
        (user_id, name, start_date, end_date, duration, location, latitude, longitude, collaborators, notes) 
        values ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10) returning id;",
        $userId,
        $_POST["trip-name"],
        $start_date,
        $end_date,
        $duration,
        $location,
        $_POST["latitude"],
        $_POST["longitude"],
        $collaborators,
        empty($_POST["trip-description"]) ? null : $_POST["trip-description"]
      );

      //update stats
      $this->updateStats();

      header("Location: ?command=trips");
      exit;
    } else {
      $message = "<p class='alert alert-danger'>Please fill out all required fields</p>";
      $this->showAddTrip();
    }
  }

  public function deleteTrip() {
    if (!isset($_SESSION["user_id"]) || !isset($this->input["id"])) {
        header("Location: ?command=trips");
        exit;
    }
    $userId = $_SESSION["user_id"];
    $tripId = $this->input["id"];
    $trip = $this->db->query("select * from project_trips where id = $1 and user_id = $2;", $tripId, $userId);
    $location = $trip[0]["location"];

    // delete trip
    $this->db->query("DELETE FROM project_trips WHERE id = $1 AND user_id = $2;", $tripId, $userId);

    // update stats
    $this->updateStats();

    // update bucket list visited status if location is in bucket list + was unique 
    if (!$this->inTrips($location) && $this->onBucketList($location)) {
      $this->db->query("update project_bucketlist set visited = false where user_id = $1 and location = $2;", $userId, $location);
    }

    header("Location: ?command=trips");
    exit;
  }

  public function saveTripEdits() {
    $userId = $_SESSION["user_id"];
    if (isset($_POST["trip-id"]) && isset($_POST["trip-name"]) && isset($_POST["start-date"]) && isset($_POST["location"]) && isset($_POST["latitude"]) && isset($_POST["longitude"]) 
        && !empty($_POST["trip-id"]) && !empty($_POST["trip-name"]) && !empty($_POST["start-date"]) && !empty($_POST["location"]) && !empty($_POST["latitude"]) && !empty($_POST["longitude"])) {
        $tripId = $_POST["trip-id"];
        $location = $_POST["location"];
        $start_date = $_POST["start-date"];
        $end_date = empty($_POST["end-date"]) ? null : $_POST["end-date"];
        $duration = $this->getDuration($start_date, $end_date);

        $collaborators = null;
        if (!empty($_POST["collaborators"])) {
            $ids = array_map('intval', $_POST["collaborators"]);
            $collaborators = '{' . implode(',', $ids) . '}';
        }

        $old_location = $this->db->query("SELECT location from project_trips WHERE user_id = $1 and id = $2;", $userId, $tripId)[0]["location"];

        // update trip - collaborators and description are optional
        $result = $this->db->query(
                  "UPDATE project_trips 
                  SET name = $2, start_date = $3, end_date = $4, duration = $5, location = $6, latitude = $7, longitude = $8, collaborators = $9, notes = $10
                  WHERE id = $1 AND user_id = $11;", 
                  $tripId, 
                  $_POST["trip-name"], 
                  $start_date,
                  $end_date, 
                  $duration,
                  $location, 
                  $_POST["latitude"], 
                  $_POST["longitude"], 
                  $collaborators,
                  empty($_POST["trip-description"]) ? null : $_POST["trip-description"],
                  $_SESSION["user_id"]
        );
        // update stats
        $this->updateStats();

        // if old location was unique in trips + on bucket list, update visited status
        if (!$this->inTrips($old_location) && $this->onBucketList($old_location)) {
            $this->db->query("update project_bucketlist set visited = false where user_id = $1 and location = $2;", $userId, $old_location);
        }

        // if new location is unique in trips + on bucket list, update visited status
        if (!$this->inTrips($location) && $this->onBucketList($location)) {
          $this->db->query("update project_bucketlist set visited = true where user_id = $1 and location = $2;", $userId, $location);
        }
    }

    header("Location: ?command=trips");
    exit;
  }

  public function addEntry() {
    if (isset($_POST["title"]) && isset($_POST["date"]) && isset($_POST["trip"]) && isset($_POST["entry"]) 
    && !empty($_POST["title"]) && !empty($_POST["date"]) && !empty($_POST["trip"]) && !empty($_POST["entry"])) {
      $result = $this->db->query("insert into project_entries (user_id, trip_id, date, title, entry) values ($1, $2, $3, $4, $5) returning id;",
        $_SESSION["user_id"], $_POST["trip"], $_POST["date"], $_POST["title"], $_POST["entry"]);

      $stat = $this->db->query("UPDATE project_stats SET num_entries = num_entries + 1 WHERE user_id = $1;", $_SESSION["user_id"]);

      header("Location: ?command=entries");
      exit;

      $this->showEntries();

      return;
    } else {
      $message = "<p class='alert alert-danger'>Please fill out all required fields</p>";
      $this->showAddEntry();
    }
  }
  
  public function deleteEntry() {
    if (!isset($_SESSION["user_id"]) || !isset($this->input["id"])) {
        header("Location: ?command=entries");
        exit;
    }
    $entryId = $this->input["id"];
    $userId = $_SESSION["user_id"];
    $result = $this->db->query("DELETE FROM project_entries WHERE id = $1 AND user_id = $2;", $entryId, $userId);

    // update stats
    $this->db->query("update project_stats set num_entries = num_entries - 1 where user_id = $1;", $userId);

    header("Location: ?command=entries");
    exit;
  }

  public function saveEntryEdits() {
    if (!isset($_SESSION["user_id"]) || !isset($_POST["entry_id"])) {
        header("Location: ?command=entries");
        exit;
    }

    $entryId = $_POST["entry_id"];
    $userId = $_SESSION["user_id"];

    $result = $this->db->query("SELECT * FROM project_entries WHERE id = $1 AND user_id = $2;", $entryId, $userId);

    if (empty($result)) {
        header("Location: ?command=entries");
        exit;
    }

    if (isset($_POST["title"]) && isset($_POST["date"]) && isset($_POST["trip"]) && isset($_POST["entry"]) 
      && !empty($_POST["title"]) && !empty($_POST["date"]) && !empty($_POST["trip"]) && !empty($_POST["entry"])) {

        $title = $_POST["title"];
        $date = $_POST["date"];
        $trip = $_POST["trip"];
        $entry = $_POST["entry"];
        $imageUrl = $_POST["image_url"] ?? null;

        $result = $this->db->query("UPDATE project_entries 
                          SET title = $1, date = $2, trip_id = $3, entry = $4, image_url = $5 
                          WHERE id = $6 AND user_id = $7;", 
                          $title, $date, $trip, $entry, $imageUrl, $entryId, $userId);
        header("Location: ?command=entries");
        exit;
    } else {
        $message = "<p class='alert alert-danger'>Please fill out all required fields</p>";
        $this->showEditEntry();
    }
  }

  function addBucketListTrip() {
    $userId = $_SESSION["user_id"];
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['display_name'], $input['lat'], $input['lon'])) {
        echo json_encode(['message' => 'Invalid location data']);
        return;
    }

    $location = htmlspecialchars($input['display_name']);
    $latitude = floatval($input['lat']);
    $longitude = floatval($input['lon']);

    // check if location already in bucket list
    $results = $this->db->query("select location from project_bucketlist where user_id = $1;", $userId);
    foreach ($results as $record) {
      if ($record["location"] === $location) {
        echo json_encode(['status' => 'error', 'message' => 'Location already on bucket list.']);
        return;
      }
    }

    // check if location already in trips
    $results = $this->db->query("select location from project_trips where user_id = $1;", $userId);
    foreach ($results as $record) {
      if ($record["location"] === $location) {
        echo json_encode(['status' => 'error', 'message' => 'You already have a trip to that location.']);
        return;
      }
    }

    // insert bucket list destination
    $this->db->query("insert into project_bucketlist (user_id, location, latitude, longitude) values ($1, $2, $3, $4);", $userId, $location, $latitude, $longitude);
    // update number of bucket list destinations in user stats
    $this->db->query("update project_stats set num_bucketlist = num_bucketlist + 1 where user_id = $1;", $userId);
    echo json_encode(['status' => 'success', 'message' => 'Location added successfully!']);
  }

  public function updateStats() {
    $userId = $_SESSION["user_id"];
    $numTrips = $this->db->query("SELECT COUNT(*) FROM project_trips WHERE user_id = $1;", $userId)[0]["count"];
    $daysTraveled = $this->db->query("SELECT COALESCE(SUM(duration), 0) AS total_days FROM project_trips WHERE user_id = $1;", $userId)[0]["total_days"];
    $numCities = $this->db->query("SELECT COUNT(DISTINCT location) FROM project_trips WHERE user_id = $1;", $userId)[0]["count"];
    $numCountries = $this->db->query("SELECT COUNT(DISTINCT TRIM(SPLIT_PART(location, ',', array_length(string_to_array(location, ','), 1)))) FROM project_trips WHERE user_id = $1;", $userId)[0]["count"];
    
    $this->db->query("UPDATE project_stats SET num_trips = $2 , num_cities = $3, num_countries = $4, days_traveled = $5 WHERE user_id = $1;", $userId, $numTrips, $numTrips, $numCountries, $daysTraveled);
  }

  public function getDuration($start_date, $end_date) {
    if (empty($end_date)) {
      $duration = 1;
    } else {
      $startDate = new DateTime($start_date);
      $endDate = new DateTime($end_date);
      $duration = $startDate->diff($endDate)->days + 1;
    }
    return $duration;
  }

  // checks if a location is in user's trips
  public function inTrips($location) {
    $userId = $_SESSION["user_id"];
    $results = $this->db->query("SELECT * FROM project_trips WHERE user_id = $1 AND location = $2;", $userId, $location);
    if (empty($results)) return false;
    return true;
  }

  // checks if a location is on user's bucketlist
  public function onBucketList($location) {
    $userId = $_SESSION["user_id"];
    $results = $this->db->query("SELECT * FROM project_bucketlist WHERE user_id = $1 AND location = $2;", $userId, $location);
    if (empty($results)) return false;
    return true;
  }
  
  public function showLogin($message = "") {
    include("/opt/src/project/templates/login.php");
  }

  public function showSignup($message = "") {
    include("/opt/src/project/templates/signup.php");
  }

  public function showHome($message = "") {
    $userId = $_SESSION['user_id'];
    $trips = json_encode($this->db->query("select * from project_trips where user_id = $1;", $userId));
    $bucketlist = json_encode($this->db->query("select * FROM project_bucketlist where user_id = $1 and visited = false;", $userId));

    include("/opt/src/project/templates/home.php");
  }

  public function showTrips($message = "") {
    $userId = $_SESSION["user_id"];
    $userName = $this->db->query("SELECT name FROM project_users WHERE id = $1;", $userId)[0]["name"];
    $trips = $this->db->query("SELECT project_trips.*, project_users.name AS creator 
                               FROM project_trips 
                               JOIN project_users ON project_trips.user_id = project_users.id 
                               WHERE project_trips.user_id = $1 OR $1 = ANY(project_trips.collaborators);", 
                               $userId);
    include("/opt/src/project/templates/trips.php");
  }

  public function showEntries($message = "") {
    $entries = $this->db->query(
      "SELECT e.*, t.location 
       FROM project_entries e 
       JOIN project_trips t ON e.trip_id = t.id 
       WHERE e.user_id = $1 
       ORDER BY e.date DESC;",
      $_SESSION["user_id"]
    );
    include("/opt/src/project/templates/entries.php");
  }

  public function showAddTrip($message = "") {
    $users = $this->db->query("select name from project_users where id != $1", $_SESSION["user_id"]);
    include("/opt/src/project/templates/add_trip.php");
  }

  public function showAddEntry($message = "") {
    $userTrips = $this->db->query("select * from project_trips where user_id = $1", $_SESSION["user_id"]);
    include("/opt/src/project/templates/add_entry.php");
  }

  
  public function showEditEntry() {
    if (!isset($_SESSION["user_id"]) || !isset($this->input["id"])) {
        header("Location: ?command=entries");
        exit;
    }
    $entryId = $this->input["id"];
    $userId = $_SESSION["user_id"];
    $result = $this->db->query("select * from project_entries where id = $1 and user_id = $2;", $entryId, $userId);

    if (empty($result)) {
        header("Location: ?command=entries");
        exit;
    }
    $userTrips = $this->db->query("select * from project_trips where user_id = $1", $userId);
    $trip = $this->db->query("select * from project_trips where id = $1", $result[0]["trip_id"]);
    include("/opt/src/project/templates/edit_entry.php");
  }

  public function showEditTrip() {
    if (!isset($_SESSION["user_id"]) || !isset($this->input["id"])) {
        header("Location: ?command=trips");
        exit;
    }
    $tripId = $this->input["id"];
    $userId = $_SESSION["user_id"];
    $result = $this->db->query("select * from project_trips where id = $1 and user_id = $2;", $tripId, $userId);

    if (empty($result)) {
        header("Location: ?command=trips");
        exit;
    }
    $users = $this->db->query("select name from project_users where id != $1", $userId);
    include("/opt/src/project/templates/edit_trip.php");
  }

  public function showStats($message = "") {
    $userId = $_SESSION["user_id"];
    $stats = $this->db->query("select * from project_stats where user_id = $1", $userId);
    $firstTrip = $this->db->query("select start_date from project_trips where user_id = $1 order by start_date asc limit 1;", $userId);
    if(!empty($firstTrip)) {
        $firstTripDate = date("F j, Y", strtotime($firstTrip[0]['start_date']));
    }

    $trips = json_encode($this->db->query("select * from project_trips where user_id = $1;", $userId));
    $bucketlist = json_encode($this->db->query("select * FROM project_bucketlist where user_id = $1;", $userId));
    $bucketlistComplete = json_encode($this->db->query("select * FROM project_bucketlist where user_id = $1 and visited = true;", $userId));

    include("/opt/src/project/templates/stats.php");
  }

  public function fetchUsers() {
    $userId = $_SESSION["user_id"];
    $users = $this->db->query("SELECT * from project_users WHERE id != $1;", $userId);
    echo(json_encode($users));
  }
}
