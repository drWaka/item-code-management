<?php 
  // Database Connection
  require_once '../asset/core/php/connection.php';

  // User Login Validation
  require_once 'includes/validate-login.php';
?>
<!doctype html>
<html lang="en">
  <head>
    <?php require_once 'includes/head.inc.php'; ?>
  </head>
  <body>
    <div class="app">
      <div class="app-body">

        <?php require_once 'includes/sidebar.inc.php'; ?>

        <div class="app-content">

          <?php require_once 'includes/headbar.inc.php'; ?>
          
          <!-- Page Content Start -->
          <div class="container-fluid">

            <div class="row margin-top-lg">
              <div class="col-6 offset-3">
                <div class="row text-center" style="background-color: #FFF; border-radius: 10px">
                  <div class="col-sm-12 margin-top">
                    <img src="../asset/core/img/logo/ollh-logo.gif" alt="" style="width : 60%">
                  </div>
                  <div class="col-sm-12 margin-top-sm margin-bottom-sm">
                    <h2 class="uppercase">Item Code Management System</h2>
                    <h3>Our Lady Of Lourdes Hospital</h3>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>

    <div class="modal-container"></div>

    
    <?php require_once 'includes/js-dependencies.inc.php'; ?>
  </body>
</html>