<!DOCTYPE html>
<html lang="en">
    <!-- sources used: https://www.chartjs.org/docs/latest/ -->
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

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            const tripLocations = JSON.parse(<?= json_encode($trips ?? []) ?>);;
            const bucketListLocations = JSON.parse(<?= json_encode($bucketlist ?? []) ?>);
            const bucketVisited = JSON.parse(<?= json_encode($bucketlistComplete ?? []) ?>);

            function loadStats(){
                fillBar();
                makeChart();
            }

            function fillBar(){
                let progressPercentage;
                if (bucketListLocations.length === 0) {
                    progressPercentage = 0;
                } else {
                    progressPercentage = (bucketVisited.length / bucketListLocations.length) * 100;
                }

                const progressBar = document.getElementById('bucket-list-bar-progress');
                progressBar.style.width = `${progressPercentage}%`;
                progressBar.setAttribute('aria-valuenow', progressPercentage);
            }

            function makeChart(){
                const now = new Date();
                const labels = [];
                for (let i = 11; i >= 0; i--) {
                    const d = new Date(now.getFullYear(), now.getMonth()-i, 1);
                    labels.push(d.toLocaleString('default', {month: 'short', year: 'numeric'}));
                }

                const countryCounts = labels.map(() => 0);

                tripLocations.forEach(trip => {
                    const tripDate = new Date(trip.start_date);
                    const label = tripDate.toLocaleString('default', {month: 'short', year: 'numeric'});
                    
                    const index = labels.indexOf(label);
                    if (index !== -1) {
                        countryCounts[index] += 1;
                    }
                });

                // changing the trip number in chart section
                const numTrips = document.getElementById('year-trips');
                numTrips.innerHTML = tripLocations.length;

                // changing last 12 months range in chart section
                const startMonth = document.getElementById('start-month-range');
                const endMonth = document.getElementById('end-month-range');

                const currentDate = new Date();
                const oneYearAgo = new Date(currentDate);
                oneYearAgo.setMonth(oneYearAgo.getMonth() - 11);
                const formatter = new Intl.DateTimeFormat('en-US', {month: 'long', year: 'numeric'})

                startMonth.innerHTML = formatter.format(oneYearAgo);
                endMonth.innerHTML = formatter.format(currentDate);

                // putting in the actual chart
                const ctx = document.getElementById('travelChart').getContext('2d');
                const travelChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Countries Visited in the Last 12 Months',
                        data: countryCounts,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.3
                    }]
                },
                options: {scales: {y: {beginAtZero: true, 
                    ticks: {stepSize: 1, callback: function (value){
                        // this part is needed so that it doesn't show fractions of months
                        if (Number.isInteger(value)) {
                            return value;
                        }
                        return '';
                    }
                }}}}});
            }

        </script>
    </head>  
    <body onload="loadStats();">
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
                    <div id="bucket-list-bar-progress" class="progress-bar bg-secondary progress-bar-striped" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
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
                            <h5 class="card-title"><a href="?command=trips" class="card-title stretched-link">Days of Travel</a></h5>
                            <?php if(!empty($firstTrip)): ?>
                                <h6 class="card-subtitle mb-2 text-body-secondary"><?php echo $firstTripDate?> - <?php echo date("F j, Y")?></h6>
                            <?php endif; ?>
                            <p class="card-text display-1"><?php echo $stats[0]["days_traveled"]?></p>
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
                            <h6 class="card-subtitle mb-2 text-body-secondary"><span id="start-month-range">February 2024</span> - <span id="end-month-range">February 2024</span></h6>
                            <p class="card-text">You've been on <span id="year-trips">0</span> trips in the past 12 months!</p>
                            <canvas id="travelChart"></canvas>
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
