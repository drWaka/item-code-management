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
    "content" => ''
  );

  if (isset($_POST['itemGroupId'])) {
    $itemGroupId = new form_validation($_POST['itemGroupId'], 'int', 'Item Group ID', true);

    if ($itemGroupId -> valid == 1) {
      $transactQry = '
        SELECT * FROM item_categ
        WHERE FK_item_group = "' . $itemGroupId -> value . '"
        ORDER BY item_categ_desc
      ';
      // die($transactQry);
      $recResult = $connection -> query($transactQry);
      
      if ($recResult -> num_rows > 0) {
        while ($recRow = $recResult -> fetch_assoc()) {
          $content['content'] .= '
            <option value="' . $recRow['PK_item_categ'] . '">' . $recRow['item_categ_desc'] . '</option>
          ';
        }
      } else {
        $content['content'] = '
          <option value="">Invalid Item Group</option>
        ';
      }
    } else {
      $content['content'] = '
        <option value="">Choose a Valid Item Group</option>
      '; 
    }
  } else {
    $content['content'] = '
      <option value="">Choose a Valid Item Group</option>
    ';
  }

  // Encode JSON File
  encode_json_file(array($success, $content));
?>