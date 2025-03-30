<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- https://cs4640.cs.virginia.edu/mqp8th/project/index.html -->
        <title>Travel Diary</title>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="author" content="Haley Andres & Alwyn Dippenaar">
        <meta name="description" content="Document your adventures around the globe.">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">       
        <link rel="stylesheet" href="styles/main.css">
        <link rel="stylesheet" href="styles/map.css">
    </head>  
    <body>
        <div class="container">
            <!-- site title -->
            <div class="row title">
                <h1>Travel Diary</h1>
            </div>
            <!-- log in form -->
            <div class="card p-4 shadow-lg mt-5 mx-auto" style="width: 35rem;">
                <div class="card-body">
                  <h3 class="card-title mb-4 title">Login</h3>
                  <form action="?command=login" method="post">
                    <div class="form-group mb-3">
                      <label for="email" class="form-label"><strong>Email address</strong></label>
                      <input type="email" class="form-control" id="email" name="email" aria-describedby="emailHelp">
                      <small id="emailHelp" class="form-text text-muted">Your email won't be shared.</small>
                    </div>
                    <div class="form-group mb-3">
                      <label for="passwd" class="form-label"><strong>Password</strong></label>
                      <input type="password" class="form-control" id="password" name="password">
                    </div>
                    <?=$message?>
                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" class="btn btn-secondary">Log In</button>
                        <a href="?command=signup" class="btn" role="button" style="background-color: gainsboro; color: black;">Create an account</a>    
                    </div>
                  </form>
                </div>
            </div>
            
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </body>
</html>
