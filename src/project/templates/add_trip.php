<!-- sources used: https://select2.org/getting-started/installation -->
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Travel Diary - Add Trip</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1"> 

        <meta name="author" content="Haley Andres & Alwyn Dippenaar">
        <meta name="description" content="Document your adventures around the globe.">

        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">       
        <link rel="stylesheet" href="styles/main.css">

        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                $("#start-date").val(new Date().toISOString().split("T")[0]);
            });

            document.addEventListener('DOMContentLoaded', () => {
                const input = document.getElementById('location-input');
                const suggestions = document.getElementById('suggestions');
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
                    document.getElementById('location').value = place.display_name;
                    document.getElementById('latitude').value = place.lat;
                    document.getElementById('longitude').value = place.lon;
                    suggestions.innerHTML = '';
                }

                function debounce(func, delay) {
                    let timer;
                    return function (...args) {
                        clearTimeout(timer);
                        timer = setTimeout(() => func.apply(this, args), delay);
                    };
                }

                $('#collaborators').select2({
                    width: '100%',
                    ajax: {
                        url: '?command=fetch_users',
                        dataType: 'json',
                        delay: 250,
                        cache: true,
                        processResults: function(data) {
                            return {
                                results: data.map(function(user) {
                                    return {
                                        id: user.id,
                                        text: user.name
                                    };
                                })
                            };
                        }
                    }
                });

                // verify end date is not before start date
                document.getElementById('add-trip-form').addEventListener('submit', function (e) {
                    const startDate = document.getElementById('start-date').value;
                    const endDate = document.getElementById('end-date').value;

                    if (endDate && endDate < startDate) {
                        e.preventDefault();
                        alert("End date cannot be before the start date.");
                    }
                });
            });
        </script>

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
                          <a class="nav-link active" aria-current="page" href="?command=trips">Trips</a>
                          <a class="nav-link" href="?command=entries">Entries</a>
                          <a class="nav-link" href="?command=stats">Stats</a>
                          <a class="nav-link" href="?command=logout">Logout</a>
                        </div>
                    </div>
                </div>
            </nav>
            <!-- page title -->
            <div class="row title my-4">
                <h1>Add Trip</h1>
            </div>
            <!-- add trip form -->
            <div class="row justify-content-center">
                <div class="col-6">
                    <form id="add-trip-form" action="?command=create_trip" method="post">
                        <div class="mb-3">
                            <label for="trip-name" class="form-label">Trip Name</label>
                            <input type="text" class="form-control" id="trip-name" name="trip-name" required>
                        </div>
                        <div class="mb-3">
                            <label for="start-date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start-date" name="start-date" required>
                        </div>
                        <div class="mb-3">
                            <label for="end-date" class="form-label">End Date (optional)</label>
                            <input type="date" class="form-control" id="end-date" name="end-date">
                        </div>
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="location-input" name="location-input" autocomplete="off" required>
                                <ul id="suggestions" class="list-group position-absolute w-100 z-3 overflow-auto" style="max-height: 200px;"></ul>
                            </div>
                            <input type="hidden" id="location" name="location">
                            <input type="hidden" id="latitude" name="latitude">
                            <input type="hidden" id="longitude" name="longitude">
                        </div>
                        <div class="mb-3">
                            <div class="position-relative">
                                <label for="collaborators">Collaborators (optional)
                                    <select class="js-example-basic-multiple js-states form-control" id="collaborators" name="collaborators[]" multiple="multiple"></select>
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="trip-description" class="form-label">Description (optional)</label>
                            <textarea class="form-control" id="trip-description" name="trip-description" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-secondary">Add Trip</button>
                    </form>
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
