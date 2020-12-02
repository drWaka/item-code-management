<?php 
  require_once 'asset/core/php/connection.php';
  require_once 'asset/core/php/error-handler.php';

  // Check if the $_SESSION['userId'] exists
  if (isset($_SESSION['userId'])) {
    header('Location: html/index.php');
  }

  // Error Management Section
  $userIdErr = $userPasswordErr = '';
  if (isset($_SESSION['userIdErr'])) {
    $userIdErr = $_SESSION['userIdErr'];
    // Unset the Used Session Variables
    unset($_SESSION['userIdErr']);
  }
  $userIdErr = new error_handler($userIdErr);

  if (isset($_SESSION['userPasswordErr'])) {
    $userPasswordErr = $_SESSION['userPasswordErr'];
    // Unset the Used Session Variables
    unset($_SESSION['userPasswordErr']);
  }
  $userPasswordErr = new error_handler($userPasswordErr);

  // Field Values
  $userIdValue = '';
  if (isset($_SESSION['userIdField'])) {
    $userIdValue = $_SESSION['userIdField'];
    unset($_SESSION['userIdField']);
  }

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
    <title>Item Code Management &minus; Our Lady Of Lourdes Hospital</title>
    <link rel="icon" href="asset/core/img/icon/ollh-icon.ico">

    <!-- CSS Dependencies -->
    <!-- Template CSS -->
    <link href="asset/vendor/admin4b/css/admin4b.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="asset/vendor/font-awesome/5.0/css/all.css" />
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">

    <!-- User Defined CSS -->
    <link rel="stylesheet" href="asset/core/css/generic-style.css" />
    <link rel="stylesheet" href="asset/core/css/login-style.css">
  </head>
  <body>
    <img src="asset/core/img/background/login-bg.jpg" id="page-bg" alt="">
    <div class="bg-backrop"></div>

    <div class="login-form">
      <div class="row login-header">
        <div class="col-12 text-center">
          <img src="asset/core/img/logo/ollh-logo.gif" alt="">
          <h2>Item Code Management System <br><small>Our Lady Of Lourdes Hospital</small></h2>
        </div>
      </div>
      <form class="" action="asset/core/php/login-script.php" method="post" submit-type="synchronous" form-name="login-form">

        <div class="col-md-12">
          <label for="">User ID : </label>
          <div class="form-group <?=$userIdErr -> error_class?>">
            <input type="text" name="userId" placeholder="User ID" class="form-control form-control-line" value="<?=$userIdValue?>" autocomplete="off">
            <?=$userIdErr -> error_icon?>
            <?=$userIdErr -> error_text?>
          </div>
        </div>

        <div class="col-md-12">
          <label for="">Password : </label>
          <div class="form-group <?=$userPasswordErr -> error_class?>">
            <input type="password" name="userPassword" placeholder="Password" class="form-control form-control-line">
            <?=$userPasswordErr -> error_icon?>
            <?=$userPasswordErr -> error_text?>
          </div>
        </div>

        <div class="col-sm-12">
          <div class="form-group text-center">
            <button class="btn btn-info submit-btn" type="button">LOGIN</button>
          </div>
        </div>

        <div class="">
          <div class="col-sm-12 text-center">
            <button type="button" class="btn btn-link transaction-btn" data-content="{}" data-link="asset/core/ajax/login-reset-password-select.php" trans-name="modal-rec">Forgot Your Password?</button>
          </div>
        </div>

      </form>
    </div>

    <div class="modal-container"></div>
    
    <!-- JS Dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.12.0/highlight.min.js"></script>
    <script src="asset/vendor/admin4b/js/admin4b.min.js"></script>
    <script src="asset/vendor/admin4b/js/admin4b.docs.js"></script>

    <!-- User Based Dependencies -->
    <script src="asset/core/js/core-form-management.js"></script>
    <script src="asset/core/js/core-ajax-request.js"></script>

    <!-- User Defined Script -->
    <script>
      $(document).ready(function() {
        initialize_form_validation('.login-form');
      });
    </script>
  </body>
</html>