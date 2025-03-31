<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Travel Diary - Add Trip</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1"> 

        <meta name="author" content="Haley Andres & Alwyn Dippenaar">
        <meta name="description" content="Document your adventures around the globe.">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">       
        <link rel="stylesheet" href="styles/main.css">
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
                <h1>Edit Trip</h1>
            </div>
            <!-- add trip form -->
            <div class="row justify-content-center">
                <div class="col-6">
                    <form id="add-trip-form">
                        <div class="mb-3">
                            <label for="trip-name" class="form-label">Trip Name</label>
                            <input type="text" value="<?php echo $result[0]["name"]?>" class="form-control" id="trip-name" name="trip-name" required>
                        </div>
                        <div class="mb-3">
                            <label for="start-date" class="form-label">Start Date</label>
                            <input type="date" value="<?php echo $result[0]["start_date"]?>" class="form-control" id="start-date" name="start-date" required>
                        </div>
                        <div class="mb-3">
                            <label for="location" class="form-label">Primary Location</label>
                            <input type="text" value="<?php echo $result[0]["city"]?>, <?php echo $result[0]["country"]?>" class="form-control" id="location" name="location" required>
                        </div>
                        <div class="mb-3">
                            <label for="collaborators" class="form-label">Collaborators</label>
                            <select id="collaborators" class="form-select" aria-label="collaborators">
                                <option selected><?php echo $result[0]["collaborators"]?></option>
                                <?php foreach($users as $user): ?>
                                    <option value="<?php echo $user["id"]?>"><?php echo $user["name"];?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="trip-description" class="form-label">Description</label>
                            <textarea class="form-control" id="trip-description" name="trip-description" rows="3" required><?php echo $result[0]["notes"]?></textarea>
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
