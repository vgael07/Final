    <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login | Goldilocks</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
      <link href="css/styles.css" rel="stylesheet" />
  <style>

    .login-container {
      max-width: 400px;
      margin: 100px auto;
      background: white;
      padding: 2rem;
      border-radius: 1rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .login-header {
      text-align: center;
      margin-bottom: 1.5rem;
    }

    .login-header h2 {
      font-weight: bold;
      color: #5a3900;
    }

    .btn-login {
      background-color: #ffd700;
      color: #5a3900;
      font-weight: bold;
    }

    .btn-login:hover {
      background-color: #e6c200;
    }

    .form-control:focus {
      box-shadow: 0 0 0 0.25rem rgba(255, 215, 0, 0.4);
      border-color: #ffd700;
    }

    .login-footer {
      text-align: center;
      margin-top: 1rem;
    }

    .login-footer a {
      color: #5a3900;
      text-decoration: none;
    }

    .login-footer a:hover {
      text-decoration: underline;
    }
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
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light shadow-sm">
  <div class="container d-flex justify-content-between align-items-center">
    <a href="order.php">
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


  <div class="container">
    <div class="login-container">
      <div class="login-header">
        <h2 class="mt-3">Login</h2>
      </div>

      <form>
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" id="username" class="form-control" placeholder="Enter your username" required>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" id="password" class="form-control" placeholder="Enter your password" required>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-login">Login</button>
        </div>
      </form>

      <div class="login-footer mt-4">
        <p><a href="#">Forgot password?</a></p>
        <p>Don't have an account? <a href="#">Sign up</a></p>

      </div>
    </div>
  </div>

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

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
