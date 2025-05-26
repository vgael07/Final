<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Premium Cakes | Goldilocks </title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
      <link href="css/styles.css" rel="stylesheet" /> 
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
    }
    .navbar {
      background-color: #ffd700;
    }
    .navbar-brand {
      font-weight: bold;
      color: #5a3900;
    }
    .card-title {
      font-weight: bold;
    }
    .footer {
      background-color: #5a3900;
      color: #fff;
      padding: 2rem 0;
    }
    .footer a {
      color:rgb(228, 158, 29);
      text-decoration: none;
    }
    .footer a:hover {
      text-decoration: underline;
    }
    /* Cart icon counter */
    .cart-icon {
      position: relative;
      display: inline-block;
    }
    .cart-counter {
      position: absolute;
      top: -8px;
      right: -8px;
      background: red;
      color: white;
      border-radius: 50%;
      padding: 2px 6px;
      font-size: 12px;
    }
      .card-img-top {
    height: 250px;
    object-fit: cover;
  }
  </style>
</head>
<body>

  <!-- Header/Navbar --> 
  <nav class="navbar navbar-expand-lg navbar-light shadow-sm">
  <div class="container d-flex justify-content-between align-items-center">
    <a href="index.php">
    <img src="logo2.png" alt="Logo" width="200" height="50"></a>

    <div class="search-container mx-auto">
      <input id="search" type="text" name="q" placeholder="What are you looking for?" 
        class="custom-search" maxlength="100" role="combobox" aria-haspopup="false" 
        aria-autocomplete="both" autocomplete="off" aria-expanded="false">
      <i class="bi bi-search"></i>
    </div>

    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="login.php">Sign In</a></li>
        <li class="nav-item">
          <a class="nav-link position-relative" href="order.php">
            <div class="cart-icon">🛒<span id="cartCount" class="cart-counter">0</span></div>
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
  <!-- Hero Section -->
<section class="py-5 text-center bg-light">
  <div class="container">
    <img src="Premium1_.png" alt="Premium Cakes Banner" class="img-fluid mb-4" style="max-height: 300px;">
    <h1 class="fw-bold">Premium Cakes</h1>
  </div>
</section>


  <!-- Premium Cakes Grid -->
  <section class="py-5">
    <div class="container">
      <div class="row">
        <!-- Card 1 -->
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <img src="picture17.jpg" class="card-img-top" alt="Black Forest">
            <div class="card-body text-center">
              <h5 class="card-title">Black Forest</h5>
              <p class="card-text">₱817</p>
              <button class="btn btn-warning text-white" onclick="addToCart('Black Forest', 817)">Add to Tray</button>
            </div>
          </div>
        </div>
        <!-- Card 2 -->
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <img src="picture17.png" class="card-img-top" alt="Mango Dream">
            <div class="card-body text-center">
              <h5 class="card-title">Mango Dream</h5>
              <p class="card-text">₱817</p>
              <button class="btn btn-warning text-white" onclick="addToCart('Mango Dream', 817)">Add to Tray</button>
            </div>
          </div>
        </div>
        <!-- Card 3 -->
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <img src="picture18.png" class="card-img-top" alt="Ube Dream">
            <div class="card-body text-center">
              <h5 class="card-title">Ube Dream</h5>
              <p class="card-text">₱817</p>
              <button class="btn btn-warning text-white" onclick="addToCart('Ube Dream', 817)">Add to Tray</button>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <img src="torte.jpg" class="card-img-top" alt="Black Forest">
            <div class="card-body text-center">
              <h5 class="card-title">Chocolate Cherry Torte</h5>
              <p class="card-text">₱817</p>
              <button class="btn btn-warning text-white" onclick="addToCart('Black Forest', 817)">Add to Tray</button>
            </div>
          </div>
        </div>
        <!-- Card 2 -->
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <img src="sansrival.jpg" class="card-img-top" alt="Mango Dream">
            <div class="card-body text-center">
              <h5 class="card-title">Classic Sansrival</h5>
              <p class="card-text">₱1,023</p>
              <button class="btn btn-warning text-white" onclick="addToCart('Mango Dream', 1023)">Add to Tray</button>
            </div>
          </div>
        </div>
        <!-- Card 3 -->
        <div class="col-md-4 mb-4">
          <div class="card h-100">
            <img src="allaboutcake.jpg" class="card-img-top" alt="Ube Dream">
            <div class="card-body text-center">
              <h5 class="card-title">All About Chocolate Cake</h5>
              <p class="card-text">₱919</p>
              <button class="btn btn-warning text-white" onclick="addToCart('Ube Dream', 919)">Add to Tray</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
            <div class="container px-5">
                <div class="row align-items-center justify-content-between flex-column flex-sm-row">
                    <div class="col-auto"><div class="small m-0 text-white">Copyright &copy; Your Website 2023</div></div>
                    <div class="col-auto">
                        <a class="link-light small" href="#!">Privacy</a>
                        <span class="text-white mx-1">&middot;</span>
                        <a class="link-light small" href="#!">Terms</a>
                        <span class="text-white mx-1">&middot;</span>
                        <a class="link-light small" href="#!">Contact</a>
                    </div>
                </div>
            </div>
  </footer>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    let cart = [];

    function addToCart(name, price) {
      cart.push({ name, price });
      document.getElementById('cartCount').innerText = cart.length;

      // Optional feedback
      alert(`${name} added to tray!`);
      console.log(cart);
    }
  </script>
</body>
</html>
