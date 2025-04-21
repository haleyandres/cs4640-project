<!DOCTYPE html>
<html lang="en">
    <!-- sources used: https://leafletjs.com/examples/quick-start/, https://wiki.openstreetmap.org/wiki/API_v0.6 -->
    <head>
        <title>Travel Diary - Map</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="author" content="Haley Andres & Alwyn Dippenaar">
        <meta name="description" content="Document your adventures around the globe.">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">       

        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
            crossorigin=""/>
        <!-- Make sure you put this AFTER Leaflet's CSS -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>

        <link rel="stylesheet" href="styles/main.css">
        <link rel="stylesheet" href="map.css">

        <script>
            const tripLocations = <?= $trips ?>;
            const bucketListLocations = <?= $bucketlist ?>; 

            function loadMap() {
                const map = L.map('map').setView([20, 0], 2);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                }).addTo(map);

                // add blue marker + pop up for each location in trips
                if(tripLocations.length !== 0) {
                    tripLocations.forEach(place => {
                        if (place.latitude && place.longitude) {
                            L.marker([parseFloat(place.latitude), parseFloat(place.longitude)])
                                .addTo(map)
                                .bindPopup(`${place.name}<br>${place.start_date} - ${place.end_date}`);
                        }
                    });
                }
                
                // add red marker for each unvisited location in bucket list
                const bucketListIcon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
                    iconAnchor: [15, 40],
                });

                if(bucketListLocations.length !== 0) {
                    bucketListLocations.forEach(place => {
                        if (place.latitude && place.longitude) {
                            L.marker([parseFloat(place.latitude), parseFloat(place.longitude)],
                                {icon: bucketListIcon})
                                .addTo(map)
                        }
                    });
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                const input = document.getElementById('location');
                const suggestions = document.getElementById('suggestions');
                const submit = document.getElementById('add-location');
                let selectedPlace = null;

                input.addEventListener('input', debounce(async () => {
                    const query = input.value.trim();
                    if (query.length < 3) return suggestions.innerHTML = '';

                    const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&addressdetails=1&limit=5`);
                    const data = await res.json();
                    showSuggestions(data);
                }, 150));

                function showSuggestions(results) {
                    suggestions.innerHTML = '';
                    results.forEach(place => {
                        const li = document.createElement('li');
                        li.textContent = place.display_name;
                        li.className = 'list-group-item list-group-item-action';
                        li.style.cursor = 'pointer';
                        li.addEventListener('click', () => selectPlace(place));
                        suggestions.appendChild(li);
                    });
                }

                function selectPlace(place) {
                    input.value = place.display_name;
                    selectedPlace = {
                        location: place.display_name,
                        lat: place.lat,
                        lon: place.lon
                    };
                    suggestions.innerHTML = '';
                }

                submit.addEventListener('click', async () => {
                    if (!selectedPlace) {
                        alert("Please choose a location from the suggestions.");
                        return;
                    }

                    const data = {
                        display_name: input.value,
                        lat: selectedPlace.lat,
                        lon: selectedPlace.lon
                    };

                    const response = await fetch('?command=add_bucketlist', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();
                    const responseMessage = document.getElementById('response');
                    responseMessage.textContent = result.message;
                    if (result.status === "error") {
                        responseMessage.classList.remove('text-success');
                        responseMessage.classList.add("text-danger");
                    } else {
                        responseMessage.classList.remove('text-danger');
                        responseMessage.classList.add("text-success");
                    }
                    selectedPlace = null;
                    input.value = '';
                });

                function debounce(func, delay) {
                    let timer;
                    return function (...args) {
                        clearTimeout(timer);
                        timer = setTimeout(() => func.apply(this, args), delay);
                    };
                }
            });

        </script>
    </head>  
    <body onload="loadMap();">
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
                    <div class="collapse navbar-collapse justify" id="navbarNavAltMarkup">
                        <div class="navbar-nav">
                          <a class="nav-link active" aria-current="page" href="?command=home">Map</a>
                          <a class="nav-link" href="?command=trips">Trips</a>
                          <a class="nav-link" href="?command=entries">Entries</a>
                          <a class="nav-link" href="?command=stats">Stats</a>
                          <a class="nav-link" href="?command=logout">Logout</a>
                        </div>
                    </div>
                </div>
            </nav>
            <!-- Main Map -->
            <div class="row my-2">
                <div class="col-12">
                    <div id="map" style="height: 500px; width: 100%"></div>
                </div>
            </div>
            <!-- add destination button -->
            <p class="mb-1">Add a bucket list destination</p>
            <div class="row position-relative">
                <div class="col-5 d-flex justify-content-end position-relative">
                    <div class="w-100 position-relative">
                        <input type="text" id="location" class="form-control" autocomplete="off">
                        <ul id="suggestions" class="list-group position-absolute w-100 z-3 overflow-auto" style="max-height: 200px;"></ul>
                    </div>
                    <button type="submit" id="add-location" class="btn btn-secondary ms-2">Add</button>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <p id="response" class="my-3"></p>
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
