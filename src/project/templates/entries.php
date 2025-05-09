<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Travel Diary - Entries</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1"> 

        <meta name="author" content="Haley Andres & Alwyn Dippenaar">
        <meta name="description" content="Document your adventures around the globe.">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">       
        <link rel="stylesheet" href="styles/main.css">
        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
        <script>
        $(document).ready(function() {
            function sortTrips(ascending) {
                const cards = $(".card").get();

                cards.sort(function(first, second) {
                    const date1 = new Date($(first).find(".card-subtitle").text().split(" - ")[0]);
                    const date2 = new Date($(second).find(".card-subtitle").text().split(" - ")[0]);

                    let sorted;
                    if (ascending) {
                        sorted = date1 - date2;
                    } else {
                        sorted = date2 - date1;
                    }
                    return sorted;
                });

                const container = $(".row-cols-1");
                $.each(cards, function(index, card) {
                    container.append($(card).closest(".col"));
                });
            }

            $("#sort-select").change(function() {
                const sortOrder = $(this).val();
                sortTrips(sortOrder === "asc");
            });

            // confirmation for delete
            $("a[href*='command=delete_entry']").on("click", function (e) {
                const confirmed = confirm("Are you sure you want to delete this entry?");
                if (!confirmed) {
                    e.preventDefault();
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
                          <a class="nav-link" href="?command=trips">Trips</a>
                          <a class="nav-link active" aria-current="page" href="?command=entries">Entries</a>
                          <a class="nav-link" href="?command=stats">Stats</a>
                          <a class="nav-link" href="?command=logout">Logout</a>
                        </div>
                    </div>
                </div>
            </nav>
            <div class="row row-cols-3">
                <div class="col"></div>
                <!-- page title -->
                <div class="col px-5">
                    <div class="title my-4">
                        <h1>My Entries</h1>
                    </div>
                </div>
                <!-- sort by -->
                <div class="col ps-5">
                    <div class="my-5 justify-content-end">
                        <div class="d-flex align-items-center">
                            <label for="sort-select" style="white-space: nowrap;" class="form-label mb-0 me-2 ps-5">Sort by:</label>
                            <select class="form-select form-select-sm me-5" id="sort-select">
                                <option value="desc" selected>Newest First</option>
                                <option value="asc">Oldest First</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <?php if(!empty($entries)): ?>
                <div class="row row-cols-1 mx-5">
                    <!-- add button -->
                    <div class="col">
                        <a class="btn btn-outline-dark" id="add-button" href="?command=addentry" role="button">+</a>
                    </div>
                    <!-- entry cards -->
                    <?php foreach ($entries as $entry): ?>
                        <div class="col">
                            <div class="card" style="width: 50rem;">
                                <div class="card-header">
                                    <h5 class="card-title"><?=$entry["title"]?></h5>
                                    <h6 class="card-subtitle text-body-secondary"><?=date("F j, Y", strtotime($entry["date"]))?></h6>
                                </div>
                                <div class="card-body">
                                    <p class="card-text"><?=$entry["entry"]?></p>
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <a href="?command=edit_entry&id=<?= $entry['id'] ?>" class="btn btn-sm" style="background-color: gainsboro; color: black;">Edit</a>
                                            <a href="?command=delete_entry&id=<?= $entry['id'] ?>" class="btn btn-sm" style="background-color: gainsboro; color: black;">Delete</a>
                                        </div>
                                        <div>
                                            <p><?= htmlspecialchars($entry["location"]) ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- add button -->
                <div class="d-flex justify-content-center align-items-center" style="height: 30vh;">
                        <a class="btn btn-outline-dark" id="add-button" href="?command=addentry" role="button">+</a>
                </div>
            <?php endif; ?>
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
