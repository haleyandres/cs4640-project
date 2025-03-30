<?php
// published site: 

// DEBUGGING ONLY! Show all errors.
error_reporting(E_ALL);
ini_set("display_errors", 1);

spl_autoload_register(function ($classname) {
        // include "/students/vwz2wb/students/vwz2wb/private/hw6/$classname.php";
        include "/opt/src/project/$classname.php";
});

// Instantiate the front controller
$travelDiary = new TravelDiaryController($_GET);

// Run the controller
$travelDiary->run();

