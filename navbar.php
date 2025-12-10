<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="#">Navbar</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
   <?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<ul class="navbar-nav mr-auto">
  <li class="nav-item <?= ($currentPage == 'index.php') ? 'active' : '' ?>">
    <a class="nav-link" href="index.php">Home</a>
  </li>

  <li class="nav-item <?= ($currentPage == 'create.php') ? 'active' : '' ?>">
    <a href="create.php" class="nav-link">Add Student</a>
  </li>

  <li class="nav-item <?= ($currentPage == 'branch.php') ? 'active' : '' ?>">
    <a href="branch.php" class="nav-link">Add Branch</a>
  </li>
</ul>
<?php
$currentPage = basename($_SERVER['PHP_SELF']);

if ($currentPage == 'index.php') { 
?>
<form class="form-inline my-2 my-lg-0" method="GET" action="index.php">
  <input 
    class="form-control mr-sm-2" 
    type="search" 
    name="search"
    placeholder="Search name, email, mobile" 
    value="<?php echo $_GET['search'] ?? ''; ?>"
  >
  <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
</form>
<?php 
}
?>


  </div>
</nav>