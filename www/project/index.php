<?php
// published site: https://cs4640.cs.virginia.edu/vwz2wb/project/

// DEBUGGING ONLY! Show all errors.
error_reporting(E_ALL);
ini_set("display_errors", 1);

spl_autoload_register(function ($classname) {
        // include "/students/vwz2wb/students/vwz2wb/private/project/$classname.php";
        include "/opt/src/project/$classname.php";
});

// Instantiate the front controller
$travelDiary = new TravelDiaryController($_GET);

// Run the controller
$travelDiary->run();

