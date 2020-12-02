<!-- Navbar Start -->
<nav class="navbar navbar-expand navbar-light bg-white">
  <button type="button" class="btn btn-sidebar" data-toggle="sidebar"><i class="icon-menu"></i></button>
  <div class="navbar-brand">OLLH &minus; ICM</div>
  <ul class="navbar-nav ml-auto">
    <li class="nav-item dropdown">
      <a href="#" class="nav-link dropdown-toggle capitalize" data-toggle="dropdown">
        <?= substr($_SESSION['firstName'], 0, 1) . '. ' . $_SESSION['lastName'] ?>
          
      </a>
      <div class="dropdown-menu dropdown-menu-right">
        <a href="#" class="transaction-btn dropdown-item" trans-name="modal-rec" data-target="modal-container" data-link="../asset/core/ajax/user-mgmt-select.php" data-content="{&quot;userId&quot; : &quot;<?= $_SESSION['userId'] ?>&quot;}">
          <div>My Profile</div>
        </a>
        <div class="dropdown-divider"></div>
        <a href="#" class="transaction-btn dropdown-item" trans-name="modal-rec" data-target="modal-container" data-link="../asset/core/ajax/password-mgmt-select.php" data-content="{&quot;userId&quot; : &quot;<?= $_SESSION['userId'] ?>&quot;}">
          <div>Change Password</div>
        </a>
        <div class="dropdown-divider"></div>
        <a href="../asset/core/php/logout-script.php" class="dropdown-item">
          <div>Logout</div>
        </a>
      </div>
    </li>
  </ul>
</nav>
<!-- Navbar End -->