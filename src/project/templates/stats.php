<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Travel Diary - Stats</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1"> 

        <meta name="author" content="Haley Andres & Alwyn Dippenaar">
        <meta name="description" content="Document your adventures around the globe.">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">       
        <link rel="stylesheet" href="styles/main.css">
        <link rel="stylesheet" href="styles/stats.css">
    </head>  
    <body>
        <div class="container">
            <!-- site title -->
            <div class="row title">
                <h1>Travel Diary</h1>
            </div>
            <!-- navigation bar -->
            <nav class="navbar navbar-expand-sm" id="main-nav">
                <div class="container-fluid">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                      </button>
                    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                        <div class="navbar-nav">
                          <a class="nav-link" href="?command=home">Map</a>
                          <a class="nav-link" href="?command=trips">Trips</a>
                          <a class="nav-link" href="?command=entries">Entries</a>
                          <a class="nav-link active" aria-current="page" href="?command=stats">Stats</a>
                          <a class="nav-link" href="?command=logout">Logout</a>
                        </div>
                    </div>
                </div>
            </nav>
            <!-- page title -->
            <div class="row title my-4">
                <h1>My Travel Statistics</h1>
            </div>
            <!-- bucket list progress -->
            <div class="row row-cols-1 mb-4">
                <div id="bucket-list-bar" class="progress mb-3">
                    <div id="bucket-list-bar-progress" class="progress-bar bg-secondary progress-bar-striped" role="progressbar" style="width: 30%" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <h4 class="text-center">Bucket List progress</h4>
            </div>
            <div id="stats-cards" class="row row-cols-1 row-cols-md-2 mx-5">
                <!-- trip stats -->
                <div class="col stats-card-col d-flex align-items-stretch">
                    <div class="card w-100">
                        <div class="card-body">
                            <h5 class="card-title"><a href="?command=trips" class="card-title stretched-link">Total Trips Taken</a></h5>
                            <?php if(!empty($firstTrip)): ?>
                                <h6 class="card-subtitle mb-2 text-body-secondary"><?php echo $firstTripDate?> - <?php echo date("F j, Y")?></h6>
                            <?php endif; ?>
                            <p class="card-text display-1"><?php echo $stats[0]["num_trips"]?></p>
                        </div>
                    </div>
                </div>
                <div class="col stats-card-col d-flex align-items-stretch">
                    <div class="card w-100">
                        <div class="card-body">
                            <h5 class="card-title"><a href="?command=trips" class="card-title stretched-link">Miles Traveled</a></h5>
                            <?php if(!empty($firstTrip)): ?>
                                <h6 class="card-subtitle mb-2 text-body-secondary"><?php echo $firstTripDate?> - <?php echo date("F j, Y")?></h6>
                            <?php endif; ?>
                            <p class="card-text display-1"><?php echo $stats[0]["miles_traveled"]?></p>
                        </div>
                    </div>
                </div>
                <div class="col stats-card-col d-flex align-items-stretch">
                    <div class="card w-100">
                        <div class="card-body">
                            <h5 class="card-title"><a href="?command=trips" class="card-title stretched-link">Countries Visited</a></h5>
                            <?php if(!empty($firstTrip)): ?>
                                <h6 class="card-subtitle mb-2 text-body-secondary"><?php echo $firstTripDate?> - <?php echo date("F j, Y")?></h6>
                            <?php endif; ?>
                            <p class="card-text display-1"><?php echo $stats[0]["num_countries"]?></p>
                        </div>
                    </div>
                </div>
                <div class="col stats-card-col d-flex align-items-stretch">
                    <div class="card w-100">
                        <div class="card-body">
                            <h5 class="card-title"><a href="?command=trips" class="card-title stretched-link">Cities Visited</a></h5>
                            <?php if(!empty($firstTrip)): ?>
                                <h6 class="card-subtitle mb-2 text-body-secondary"><?php echo $firstTripDate?> - <?php echo date("F j, Y")?></h6>
                            <?php endif; ?>
                            <p class="card-text display-1"><?php echo $stats[0]["num_cities"]?></p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- year in review -->
            <div class="row row-cols-1 mx-5">
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <h1 class="card-title">Year In Review</h1>
                            <h6 class="card-subtitle mb-2 text-body-secondary">February 2024 - February 2025</h6>
                            <p class="card-text">You've been on 4 trips in the past 12 months!</p>
                            <img src="media/placeholder-graph.png" alt="a graph depicting the number of trips taken each month in the past year" class="img-fluid" width="300">
                        </div>
                    </div>
                </div>
            </div>
            <!-- site footer -->
            <footer class="py-3 my-4" id="footer">
                <ul class="nav justify-content-center border-bottom pb-3 mb-3">
                  <li class="nav-item"><a href="?command=home" class="nav-link px-2">Map</a></li>
                  <li class="nav-item"><a href="?command=trips" class="nav-link px-2">Trips</a></li>
                  <li class="nav-item"><a href="?command=entries" class="nav-link px-2">Entries</a></li>
                  <li class="nav-item"><a href="?command=stats" class="nav-link px-2">Stats</a></li>
                </ul>
                <p class="text-center">Copyright © 2025</p>
            </footer>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </body>
</html>
