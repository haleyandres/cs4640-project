<?php

class TravelDiaryController {

  private $db;

  private $errorMessage = "";

  public function __construct($input) {
    session_start();
    // $this->db = new Database();
    $this->input = $input;
  }

  public function run() {
    $command = "welcome";
    if (isset($this->input["command"]) && (
      $this->input["command"] == "login" || isset($_SESSION["name"])))
      $command = $this->input["command"];

    switch($command) {
      case "login":
        $this->login();
        break;
      case "signup":
        $this->signup();
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
      case "logout":
        $this->logout();
      case "welcome":
      default:
        $this->showLogin();
        break;
    }
  }

  public function login() {
    $this->showWelcome();
  }

  public function signup() {
    $this->showSignup();
  }

  public function logout() {
    session_destroy();
    session_start();
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
