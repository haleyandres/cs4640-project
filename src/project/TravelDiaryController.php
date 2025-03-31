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
      case "addentry":
        $this->showAddEntry();
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
                $result = $this->db->query("insert into project_users (name, email, password) values ($1, $2, $3) returning id;",
                    $_POST["name"], $_POST["email"], password_hash($_POST["password"], PASSWORD_DEFAULT));
                $_SESSION["user_id"] = $result[0]["id"];
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
  
  public function showLogin($message = "") {
    include("/opt/src/project/templates/login.php");
  }

  public function showSignup($message = "") {
    include("/opt/src/project/templates/signup.php");
  }

  public function showHome($message = "") {
    include("/opt/src/project/templates/home.php");
  }

  public function showTrips($message = "") {
    include("/opt/src/project/templates/trips.php");
  }

  public function showEntries($message = "") {
    include("/opt/src/project/templates/entries.php");
  }

  public function showAddTrip($message = "") {
    include("/opt/src/project/templates/add_trip.php");
  }

  public function showAddEntry($message = "") {
    include("/opt/src/project/templates/add_entry.php");
  }

  public function showStats($message = "") {
    include("/opt/src/project/templates/stats.php");
  }
}
