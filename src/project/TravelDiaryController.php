<?php

class TravelDiaryController {

  private $db;

  private $errorMessage = "";

  public function __construct($input) {
    session_start();
    $this->db = new Database();
    $this->input = $input;
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
      case "userinfo":
        $this->userInfoAPI();
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

  public function userInfoAPI() {
    $result = $this->db->query("select * from project_users where id = $1", $_SESSION["user_id"]);
    $userInfo = [
        "name" => $result[0]["name"],
        "email" => $result[0]["email"],
        "date_joined" => $result[0]["date_joined"]
    ];
    header("Content-Type: application/json");
    echo(json_encode($userInfo, JSON_PRETTY_PRINT));
  }

  public function deleteTrip() {
    if (!isset($_SESSION["user_id"]) || !isset($this->input["id"])) {
        header("Location: ?command=trips");
        exit;
    }
    $userId = $_SESSION["user_id"];
    $tripId = $this->input["id"];
    $trip_match = $this->db->query("select location from project_trips where id = $1 and user_id = $2;", $tripId, $userId);
    $location = $trip_match[0]["location"];

    // delete trip
    $result = $this->db->query("DELETE FROM project_trips WHERE id = $1 AND user_id = $2;", $tripId, $userId);

    // update number of trips in stats
    $this->db->query("update project_stats set num_trips = num_trips - 1 where user_id = $1;", $userId);

    // update number of cities visited in stats
    $city_match = $this->db->query("select 1 from project_trips where user_id = $1 and location = $2;", $userId, $location);
    if (empty($city_match)) {
      $this->db->query("update project_stats set num_cities = num_cities - 1 where user_id = $1;", $userId);
    }      
    // update number of countries visited in stats
    $new_country = trim(explode(',', $location)[2]);
    $country_match = $this->db->query("select 1 from project_trips where user_id = $1 and trim(split_part(location, ',', 3)) ilike $2;", $userId, $new_country);    
    if (empty($country_match)) {
      $this->db->query("update project_stats set num_countries = num_countries - 1 where user_id = $1;", $userId);
    }
    // update number of vistied bucket list destinations in stats
    $bucketlist_match = $this->db->query("select 1 from project_bucketlist where user_id = $1 and location = $2;", $userId, $location);
    if (!empty($bucketlist_match)) {
      $this->db->query("update project_stats set num_visited = num_visited - 1 where user_id = $1;", $userId);
      $this->db->query("update project_bucketlist set visited = false where user_id = $1 and location = $2;", $userId, $location);
    }


    header("Location: ?command=trips");
    exit;
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

  public function saveEntryEdits() {
      if (!isset($_SESSION["user_id"]) || !isset($this->input["entry_id"])) {
          header("Location: ?command=entries");
          exit;
      }
  
      $entryId = $this->input["entry_id"];
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

          $this->db->query("UPDATE project_entries 
                            SET title = $1, date = $2, trip_id = $3, entry = $4, image_url = $5 
                            WHERE id = $6 AND user_id = $7;", 
                            $title, $date, $trip, $entry, $imageUrl, $entryId, $userId);
  
          $this->db->query("UPDATE project_stats SET num_entries = num_entries + 1 WHERE user_id = $1;", $userId);
  
          header("Location: ?command=entries");
          exit;
      } else {
          $message = "<p class='alert alert-danger'>Please fill out all required fields</p>";
          $this->showEditTrip();
      }
  }

  public function saveTripEdits() {
      if (isset($_POST["trip-name"]) && isset($_POST["start-date"]) && isset($_POST["country"]) && isset($_POST["city"]) && isset($_POST["collaborators"]) && isset($_POST["trip-description"]) 
          && !empty($_POST["trip-name"]) && !empty($_POST["start-date"]) && !empty($_POST["country"]) && !empty($_POST["city"]) && !empty($_POST["collaborators"]) && !empty($_POST["trip-description"])) {
          
          if (isset($_POST["trip-id"]) && !empty($_POST["trip-id"])) {
              $trip_id = $_POST["trip-id"];
              $result = $this->db->query("UPDATE project_trips 
                                          SET name = $2, start_date = $3, end_date = $4, country = $5, city = $6, collaborators = $7, notes = $8
                                          WHERE id = $1 AND user_id = $9;", 
                                          $trip_id, $_POST["trip-name"], $_POST["start-date"], $_POST["end-date"] ?? null, $_POST["country"], $_POST["city"], "{".$_POST['collaborators']."}", $_POST["trip-description"], $_SESSION["user_id"]);
          }

          header("Location: ?command=trips");
          exit;

      } else {
          $message = "<p class='alert alert-danger'>Please fill out all required fields</p>";
          $this->showAddTrip();
      }
  }


  public function addTrip() {
    $userId = $_SESSION["user_id"];
    if (isset($_POST["trip-name"]) && isset($_POST["start-date"]) && isset($_POST["location"]) && isset($_POST["latitude"]) && isset($_POST["longitude"]) 
    && !empty($_POST["trip-name"]) && !empty($_POST["start-date"]) && !empty($_POST["location"]) && !empty($_POST["latitude"]) && !empty($_POST["longitude"])) {
      
      // update number of cities visited in stats
      $city_match = $this->db->query("select 1 from project_trips where user_id = $1 and location = $2;", $userId, $_POST["location"]);
      if (empty($city_match)) {
        $this->db->query("update project_stats set num_cities = num_cities + 1 where user_id = $1;", $userId);
      }      
      // update number of countries visited in stats
      $new_country = trim(explode(',', $_POST["location"])[2]);
      $country_match = $this->db->query("select 1 from project_trips where user_id = $1 and trim(split_part(location, ',', 3)) ilike $2;", $userId, $new_country);    
      if (empty($country_match)) {
        $this->db->query("update project_stats set num_countries = num_countries + 1 where user_id = $1;", $userId);
      }

      // update number of trips in stats 
      $stat = $this->db->query("update project_stats set num_trips = num_trips + 1 where user_id = $1;", $userId);

      // if location matches bucket list destination, update visited stats and mark bucket list destination as visited
      $bucketlist_match = $this->db->query("select 1 from project_bucketlist where user_id = $1 and location = $2;", $userId, $_POST["location"]);
      if (!empty($bucketlist_match)) {
        $this->db->query("update project_stats set num_visited = num_visited + 1 where user_id = $1;", $userId);
        $this->db->query("update project_bucketlist set visited = true where user_id = $1 and location = $2;", $userId, $_POST["location"]);
      }
      
      // insert new trip - collaborators and description are optional
      $result = $this->db->query(
        "insert into project_trips 
        (user_id, name, start_date, end_date, location, latitude, longitude, collaborators, notes) 
        values ($1, $2, $3, $4, $5, $6, $7, $8, $9) returning id;",
        $userId,
        $_POST["trip-name"],
        $_POST["start-date"],
        empty($_POST["end-date"]) ? null : $_POST["end-date"],
        $_POST["location"],
        $_POST["latitude"],
        $_POST["longitude"],
        empty($_POST["collaborators"]) ? null : "{" . $_POST["collaborators"] . "}",
        empty($_POST["trip-description"]) ? null : $_POST["trip-description"]
      );      

      header("Location: ?command=trips");
      exit;

      $this->showTrips();

      return;
    } else {
      $message = "<p class='alert alert-danger'>Please fill out all required fields</p>";
      $this->showAddTrip();
    }
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

    // insert bucket list destination
    $this->db->query("insert into project_bucketlist (user_id, location, latitude, longitude) values ($1, $2, $3, $4);", $userId, $location, $latitude, $longitude);
    // update number of bucket list destinations in user stats
    $this->db->query("update project_stats set num_bucketlist = num_bucketlist + 1 where user_id = $1;", $userId);
    echo json_encode(['status' => 'success', 'message' => 'Location added successfully!']);
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
    $trips = $this->db->query("select * from project_trips where user_id = $1 order by start_date desc;", $_SESSION["user_id"]);
    include("/opt/src/project/templates/trips.php");
  }

  public function showEntries($message = "") {
    $entries = $this->db->query("select * from project_entries where user_id = $1 order by date desc;", $_SESSION["user_id"]);
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

  public function showStats($message = "") {
    $userId = $_SESSION["user_id"];
    $stats = $this->db->query("select * from project_stats where user_id = $1", $userId);
    $firstTrip = $this->db->query("select start_date from project_trips where user_id = $1 order by start_date asc limit 1;", $userId);
    if(!empty($firstTrip)) {
        $firstTripDate = date("F j, Y", strtotime($firstTrip[0]['start_date']));
    }
    include("/opt/src/project/templates/stats.php");
  }
}
