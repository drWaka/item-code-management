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
                  Item Code Requests
                </h1>
              </div>
              <div class="col-md-7 filter-fields">
                <div class="row">
                  
                  <div class="col-md-7 offset-md-2">
                    <input type="text" name="risNoSearch" class="form-control" placeholder="Search RIS No" />
                  </div>

                  <div class="col-md-3">
                    <div class="form-group">
                      <button class="btn btn-info form-control filter-toggle">Toggle Filter</button>
                    </div>
                  </div>

                </div>
              </div>
            </div>
            <div class="row advFilter hide">

              <div class="col-6">
                <div class="row">
                  <div class="col-3">
                    <label for="useDateRng" class="rangeLbl"><input type="checkbox" name="useDateRng" id="useDateRng" class="" checked> Document Date Range: </label>
                  </div>
                  <div class="rangeCont col-9 row">
                    <div class="col-6">
                      <input type="date" name="dateRngMin" class="form-control" value="<?= date('Y-m-d'); ?>">
                    </div>
                    <div class="col-6">
                      <input type="date" name="dateRngMax" class="form-control" value="<?= date('Y-m-d'); ?>">
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="col-6">
                <div class="row">
                  <div class="col-3">
                    <label for="useIdRng" class="rangeLbl"><input type="checkbox" name="useIdRng" id="useIdRng" class=""> Document No. Range: </label>
                  </div>
                  <div class="rangeCont col-9 row">
                    <div class="col-6">
                      <input type="input" name="idRngMin" class="form-control" value="0" disabled>
                    </div>
                    <div class="col-6">
                      <input type="input" name="idRngMax" class="form-control" value="0" disabled>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-6 margin-top-xs">
                <div class="row">
                  <div class="col-3"> Status :  </div>
                  <div class="col-3">
                    <label for="statSaved"><input type="checkbox" name="reqStat[]" value="saved" id="statSaved" class="" checked disabled> Saved</label>
                  </div>
                  <div class="col-3">
                    <label for="statPosted"><input type="checkbox" name="reqStat[]" value="posted" id="statPosted" class="" checked> Posted</label>
                  </div>
                  <div class="col-3">
                    <label for="statCancelled"><input type="checkbox" name="reqStat[]" value="cancelled" id="statCancelled" class=""> Cancelled</label>
                  </div>
                </div>
              </div>

            </div>
            
            
            <div class="row">
              <div class="col-12">
                <table class="table table-hover table-dashed">
                  <thead>
                    <tr>
                    <th>Document No</th>
                      <th>Document Date</th>
                      <th>RIS No</th>
                      <th>No. of Items</th>
                      <th>Status</th>
                      <th>Requested By</th>
                      <th>Posting Date</th>
                      <th>Posted By</th>
                      <th>Actions</th>
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
                    <button class="btn btn-info form-control add-new-rec">File New Request</button>
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
        let statChkBox = document.querySelectorAll("[name='reqStat[]']:checked");
        var checkedStatBox = '';

        for (var i = 0 ; i < statChkBox.length ; i++) {
          checkedStatBox += checkedStatBox.length > 0 ? '-' + statChkBox[i].value : statChkBox[i].value;
        }

        let risNo = (document.querySelector('[name="risNoSearch"]')).value,
        useDateRng = document.querySelector('[name="useDateRng"]').checked ? 1 : 0,
        dateRngMin = (document.querySelector('[name="dateRngMin"]')).value,
        dateRngMax = (document.querySelector('[name="dateRngMax"]')).value,
        useIdRng = document.querySelector('[name="useIdRng"]').checked ? 1 : 0,
        idRngMin = (document.querySelector('[name="idRngMin"]')).value,
        idRngMax = (document.querySelector('[name="idRngMax"]')).value;

        send_request_asycn (
          '../asset/core/ajax/item-code-req-content.php', 
          'POST', 
          {
            risNo : risNo,
            useDateRng : useDateRng,
            dateRngMin : dateRngMin,
            dateRngMax : dateRngMax,
            useIdRng : useIdRng,
            idRngMin : idRngMin,
            idRngMax : idRngMax,
            checkedStatBox : checkedStatBox
          }, 
          '.record-container', 
          'recordList'
        );
      }

      $(document).ready(function() {
        // Send AJAX Request
        refreshList();

        $(document).on('click', '.add-new-rec', function() {
          let link = '../asset/core/ajax/item-code-req-select.php';
          let data = {
            recordId : "new-rec"
          };
          let container = '.modal-container';
          let transName = 'modal-rec';
          
          send_request_asycn (link, 'POST', data, container, transName);
        });

        $(document).on('click', '.btn-trigger', function() {
          // Transaction Success Trigger
          refreshList();
        });

        $('.filter-toggle').on('click', function() {
          let advFilter = document.querySelector('.advFilter');

          if (advFilter.className.indexOf('hide') > -1) {
            advFilter.classList.remove('hide');
          } else {
            advFilter.classList.add('hide');
          }
        });

        $(document).on('keyup','[name="risNoSearch"]' , function(){
          var curVal = $(this).val()
          var lastChar = curVal.substring(curVal.length - 1);
          if (isNaN(lastChar)) {
            $(this).val(curVal.substring(0, curVal.length - 1));
          } else {
            refreshList();
          }
        });
        $(document).on('click','[name="reqStat[]"]' , function(){
          refreshList();
        });


        // ID Range
        $(document).on('click','[name="useIdRng"]' , function(){
          let idRngMin = document.querySelector('[name="idRngMin"]');
          let idRngMax = document.querySelector('[name="idRngMax"]');
          let checked = (document.querySelector('[name="useIdRng"]')).checked ? 1 : 0;
          if (checked) {
            idRngMin.removeAttribute('disabled');
            idRngMax.removeAttribute('disabled');
          } else {
            idRngMin.setAttribute('disabled', 'disabled');
            idRngMax.setAttribute('disabled', 'disabled');
          }

          refreshList();
        });
        $(document).on('blur','[name="idRngMin"], [name="idRngMax"]' , function(){
          if ($(this).val() == '') {
            $(this).val('0');
          }
          refreshList();
        });
        $(document).on('keyup','[name="idRngMin"], [name="idRngMax"]' , function(){
          var curVal = $(this).val()
          var lastChar = curVal.substring(curVal.length - 1);
          if (isNaN(lastChar)) {
            $(this).val(curVal.substring(0, curVal.length - 1));
          }
        });

        // Date Range
        $(document).on('click','[name="useDateRng"]' , function(){
          let dateRngMin = document.querySelector('[name="dateRngMin"]');
          let dateRngMax = document.querySelector('[name="dateRngMax"]');
          let checked = (document.querySelector('[name="useDateRng"]')).checked ? 1 : 0;
          if (checked) {
            dateRngMin.removeAttribute('disabled');
            dateRngMax.removeAttribute('disabled');
          } else {
            dateRngMin.setAttribute('disabled', 'disabled');
            dateRngMax.setAttribute('disabled', 'disabled');
          }
          
          refreshList();
        });
        $(document).on('blur','[name="dateRngMin"], [name="dateRngMax"]' , function(){
          refreshList();
        });
        
      });
    </script>
  </body>
</html>