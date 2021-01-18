<?php 
  // Connection
  require '../php/connection.php';
  // Form Validation
  require '../php/server-side-validation.php';
  // Misc Generic Functions
  require '../php/generic-functions.php';

  // JSON Variables
  $success = 'success';
  $content = array(
    "modal" => ''
  );

  if (isset($_POST['userId'])) {
    $recordId  = new form_validation($_POST['userId'], 'str-int', 'User ID', true);

    if ($recordId -> valid == 1) {
      // User ID Validation
      if (is_numeric($recordId -> value)) {
        $checkExist = 'SELECT * FROM sys_user WHERE PK_sys_user = "' . $recordId -> value . '"';
        $checkExistRes = $connection -> query($checkExist);

        if (!($checkExistRes -> num_rows > 0)) {
          $recordId -> valid = 0;
          $recordId -> err_msg = 'User Record Not Found';
        }
      } else {
        if ($recordId -> value != 'new-user') {
          $recordId -> valid = 0;
          $recordId -> err_msg = 'Invalid User Record ID';
        }
      }
    }

    if ($recordId -> valid == 1) {
      $modalContent = '';

      $modulesQry = '
        SELECT 
          c.description
          , c.PK_sys_module
          , c.icon
          , c.short_desc
          , b.isValid
        FROM sys_user AS a
        INNER JOIN sys_user_modules AS b ON a.PK_sys_user = b.FK_sys_user
        INNER JOIN sys_modules AS c ON b.FK_sys_module = c.PK_sys_module
        WHERE a.PK_sys_user = "' . $recordId -> value . '"
          AND c.FK_sys_system = 1
        ORDER BY c.sorting
      ';
      $modulesRes = $connection -> query($modulesQry);
      if ($modulesRes -> num_rows > 0) {
        while ($moduleRow = $modulesRes -> fetch_assoc()) {
          $modalContent .= '
            <div class="panel panel-default">
              <div class="panel-heading">
                <h6 class="panel-title">
                  <a data-toggle="collapse" data-parent="#userAccess" href="#' . $moduleRow['short_desc'] . '">
                  ' . ucfirst($moduleRow['description']) . '</a><i class="fas fa-angle-right"></i>
                </h6>
              </div>
              <div id="' . $moduleRow['short_desc'] . '" class="panel-collapse collapse in margin-bottom-sm">
                <div class="panel-body">Lorem ipsum dolor sit amet, consectetur adipisicing elit,
                sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad
                minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea
                commodo consequat.</div>
              </div>
            </div>
          ';
          $subModuleQry = '
            SELECT
              c.description
              , c.PK_sys_sub_module
              , c.short_desc
              , b.isValid
            FROM sys_user AS a
            INNER JOIN sys_user_sub_modules AS b ON a.PK_sys_user = b.FK_sys_user
            INNER JOIN sys_sub_modules as c ON b.FK_sys_sub_module = c.PK_sys_sub_module
            WHERE a.PK_sys_user = "' . $recordId -> value . '"
              AND c.FK_sys_module = "' . $moduleRow['PK_sys_module'] . '"
            ORDER BY c.sorting
          ';
          // die($subModuleQry);
          $subModuleRes = $connection -> query($subModuleQry);
          if ($subModuleRes -> num_rows > 0) {

          } else {
            
          }
        }
      }

      $content['modal'] = modalize(
        '<div class="row">
          <div class="col-sm-12">
            <h2 class="header capitalize text-center">User Access Management</h2>
          </div>
          
          <div class="col-sm-12 margin-top">
            <div class="panel-group" id="userAccess">' . $modalContent . '</div>
          </div>
        </div>
        ', 
        array(
          "trasnType" => 'error',
          "btnLbl" => 'Close'
        )
      );
    } else {
      if ($recordId -> valid == 0) {
        $content['modal'] = modalize( 
          '<div class="row text-center">
              <h2 class="header capitalize col-12">Error Encountered</h2>
              <p class="para-text col-12">Error Details: ' . $recordId -> err_msg . '</p>
          </div>', 
          array(
            "trasnType" => 'error',
            "btnLbl" => 'Dismiss'
          )
        );
      }
    }
  } else {
    $content['modal'] = modalize(
      '<div class="row text-center">
        <h2 class="header capitalize col-12">Error Encountered</h2>
        <p class="para-text col-12">Error Details: Insufficient Data Submitted</p>
      </div>', 
      array(
        "trasnType" => 'error',
        "btnLbl" => 'Dismiss'
      )
    );
  }

  // Encode JSON File
  encode_json_file(array($success, $content));
?>