<?php 
  // Database Connection
  require_once '../asset/core/php/connection.php';

  // User Login Validation
  require_once 'includes/validate-login.php';

  $requestId = $requestRecRow = '';
  // Page Usage Verification
  if (!isset($_POST['requestId'])) {
    header('Location: index.php');
  } else {
    $requestId = $_POST['requestId'];
    unset($_POST['requestId']);

    $checkRecValid = '
      SELECT * FROM item_code_req
      WHERE pk_item_code_req = "' . $requestId . '"
    ';
    $checkRecRes = $connection -> query($checkRecValid);

    if (!($checkRecRes -> num_rows > 0)) {
      // Record Doesn't Exist
      header('Location: index.php');
    } else {
      $requestRecRow = $checkRecRes -> fetch_assoc();
    }
  }
?>
<!doctype html>
<html lang="en">
  <head>
    <?php require_once 'includes/head.inc.php'; ?>    
  </head>
  <body>
    <div class="loading-cover">
      <img class="load-icon" src="../asset/core/img/icon/load-icon.svg" alt="loading icon">
    </div>
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
                  Item Code Request #<?=$requestId ?></small>
                </h1>
              </div>
              <div class="col-md-7 filter-fields">
                <input type="text" name="itemReqId" value="<?=$requestId ?>" hidden />
                <div class="row">
                  
                  <div class="col-md-6">
                    <input type="text" name="itemDescSearch" class="form-control" placeholder="Search Item Description" />
                  </div>

                  <div class="col-md-3">
                    <a href="../asset/core/img/ris/<?= $requestRecRow['ris_img'] . '?' . strtotime(date('Y-m-d h:i:s')) ?>" target="_blank"><button class="btn btn-info form-control">View RIS</button></a>
                  </div>
                  <div class="col-md-3">
                    <a href="../asset/core/img/quot/<?= $requestRecRow['quot_img'] . '?' . strtotime(date('Y-m-d h:i:s')) ?>" target="_blank"><button class="btn btn-info form-control">View Quot</button></a>
                  </div>

                </div>
              </div>
            </div>            
            
            <div class="row">
              <div class="col-12">
                <table class="table table-hover table-dashed">
                  <thead>
                    <tr>
                      <th>Item Code</th>
                      <th>Item Description</th>
                      <th>Item Category</th>
                      <th>Item Group</th>
                      <th>Item Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody class="record-container">
                    
                  </tbody>
                </table>
              </div>
              <div class="col-8">
                <div class="row">
                  <div class="col-2">
                    <a href="item-code-verify.php"><button class="btn btn-info"><i class="fa fa-chevron-left"></i><i class="fa fa-chevron-left"></i> Back</button></a>
                  </div>
                </div>
              </div>
              <div class="col-4">
                <div class="row text-center pagination-container">
                  <div class="col-3 offset-6 text-right">
                    <button class="btn nav-btn btn-light prev-btn" data-container="record-container"><span class="fas fa-chevron-left"></span></button>
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

      $(document).ready(function() {
        // Send AJAX Request
        let itemRecId = (document.querySelector('[name="itemReqId"]')).value;
        send_request_asycn (
          '../asset/core/ajax/item-code-verify-item-content.php', 
          'POST', 
          {
            itemDesc : '',
            itemRecId : itemRecId
          }, 
          '.record-container', 
          'recordList'
        );

        $(document).on('keyup','[name="itemDescSearch"]' , function() {
          let itemRecId = (document.querySelector('[name="itemReqId"]')).value;
          let searchVal = $(this).val();

          send_request_asycn (
            '../asset/core/ajax/item-code-verify-item-content.php', 
            'POST', 
            {
              itemDesc : searchVal,
              itemRecId : itemRecId
            }, 
            '.record-container', 
            'recordList'
          );
        });
        
      });
    </script>
  </body>
</html>