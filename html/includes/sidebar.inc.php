<!-- Sidebar Start -->
<div class="app-sidebar">
  <div class="text-right">
    <button type="button" class="btn btn-sidebar" data-dismiss="sidebar"><span class="x"></span>
    </button>
  </div>
  
  <div class="sidebar-header">
    <img src="../asset/core/img/logo/ollh-logo.gif" class="user-photo">
    <p class="username">Item Code Management<br><small>Our Lady Of Lourdes Hospital</small></p>
  </div>

  <ul id="sidebar-nav" class="sidebar-nav">
    <?php 
      $userAccessQry = "
        SELECT c.*
        FROM sys_user AS a 
        INNER JOIN sys_user_modules AS b ON a.PK_sys_user = b.FK_sys_user
        INNER JOIN sys_modules AS c ON b.FK_sys_module = c.PK_sys_module
        WHERE a.PK_sys_user = '{$_SESSION['userId']}'
          AND c.FK_sys_system = '1'
          AND b.isValid = 1
        ORDER BY c.sorting
      ";
      $userAccessRes = $connection -> query($userAccessQry);

      if ($userAccessRes -> num_rows > 0) {
        while ($userAccessRow = $userAccessRes -> fetch_assoc()) {
          echo "
            <li>
              <a href='{$userAccessRow['addr_link']}' class='sidebar-nav-link'>{$userAccessRow['icon']} {$userAccessRow['description']}</a>
            </li>
          ";
        }
      }
    ?>
    

    <!-- <li class="sidebar-nav-group">
      <a href="#fileMaintainance" class="sidebar-nav-link" data-toggle="collapse"><i class="fa fa-cog"></i> File Maintenance</a>
      <ul id="fileMaintainance" class="collapse" data-parent="#sidebar-nav">
        <li><a href="./pages/device-controls/camera.html" class="sidebar-nav-link">UOM</a></li>
        <li><a href="./pages/device-controls/file-manager.html" class="sidebar-nav-link">Item Category</a></li>
        <li><a href="./pages/device-controls/file-manager.html" class="sidebar-nav-link">Item Group</a></li>
      </ul>
    </li> -->
  </ul>

  <div class="sidebar-footer">
    <a href="#" data-toggle="tooltip" class="transaction-btn" trans-name="modal-rec" data-target="modal-container" data-link="../asset/core/ajax/user-mgmt-select.php" data-content="{&quot;userId&quot; : &quot;<?= $_SESSION['userId'] ?>&quot;}" title="My Profile">
      <i class="icon-user"></i> 
    </a>
    <a href="#" data-toggle="tooltip" class="transaction-btn" trans-name="modal-rec" data-target="modal-container" data-link="../asset/core/ajax/password-mgmt-select.php" data-content="{&quot;userId&quot; : &quot;<?= $_SESSION['userId'] ?>&quot;}" title="Change Password">
      <i class="icon-key"></i> 
    </a>
    <a href="../asset/core/php/logout-script.php" data-toggle="tooltip" title="Logout">
      <i class="icon-logout"></i>
    </a>
  </div>
</div>
<!-- Sidebar End -->
