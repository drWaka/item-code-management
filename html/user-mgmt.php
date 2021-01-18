<?php 
  // Database Connection
  require_once '../asset/core/php/connection.php';

  // User Login Validation
  require_once 'includes/validate-login.php';

  if (!($_SESSION['userType'] == 'requestor' || $_SESSION['userType'] == 'administrator')) {
    header('Location: index.php');
  }
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
            <div class="row">
              <div class="col-md-5">
                <h1 class="margin-top-sm page-header">
                  User Management</small>
                </h1>
              </div>
              <div class="col-md-7 filter-fields">
                <div class="row">
                  
                  <div class="col-md-7 offset-md-5">
                    <input type="text" name="userName" class="form-control" placeholder="Search User" />
                  </div>

                </div>
              </div>
            </div>            
            
            <div class="row">
              <div class="col-12">
                <table class="table table-hover table-dashed">
                  <thead>
                    <tr>
                      <th>User ID</th>
                      <th>Name</th>
                      <th>Last Updated</th>
                      <th>User Type</th>
                      <th>Account Status</th>
                      <th>Actions</th>
                      <th>Access Mgmt</th>
                    </tr>
                  </thead>
                  <tbody class="record-container">
                    
                  </tbody>
                </table>
              </div>
              <div class="col-4 offset-8">
                <div class="row text-center pagination-container">
                  <div class="col-3 text-right">
                    <button class="btn nav-btn btn-light prev-btn" data-container="record-container"><span class="fas fa-chevron-left"></span></button>
                  </div>
                  <div class="col-6">
                    <button class="btn btn-info form-control add-new-rec">Add New User</button>
                  </div>
                  <div class="col-3 text-left">
                    <button class="btn nav-btn btn-light next-btn" data-container="record-container"><span class="fas fa-chevron-right"></span></button>
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
    <script>
      
      // AJAX & Pagination Property
      var pageProp = {
        isMultiRec : 0,
        pageLimit : 5,
        curPage : 1,
        totalRec : 1,
        recObj : []
      }

      function refreshList() {
        let userName = (document.querySelector('[name="userName"]')).value;

        send_request_asycn (
          '../asset/core/ajax/user-mgmt-content.php', 
          'POST', 
          {
            userName : userName
          }, 
          '.record-container', 
          'recordList'
        );
      }

      $(document).ready(function() {
        // Send AJAX Request
        refreshList();

        $(document).on('click', '.add-new-rec', function() {
          let link = '../asset/core/ajax/user-mgmt-select.php';
          let data = {
            userId : "new-user"
          };
          let container = '.modal-container';
          let transName = 'modal-rec';
          
          send_request_asycn (link, 'POST', data, container, transName);
        });

        $(document).on('click', '.btn-trigger', function() {
          // Transaction Success Trigger
          refreshList();
        });

        $(document).on('keyup','[name="userName"]' , function(){
          refreshList();
        });
        
      });
    </script>
  </body>
</html>