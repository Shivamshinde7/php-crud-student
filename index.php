<?php
include "dbcon.php";

$branches = [];
$r = $conn->query("SELECT id, name FROM branch");
while ($row = $r->fetch_assoc()) {
  $branches[] = $row;
}

$limit = 4;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$searchSql = "";

if ($search !== "") {
  $search = $conn->real_escape_string($search);
  $searchSql = " WHERE students.name LIKE '%$search%'
                 OR students.email LIKE '%$search%'
                 OR students.mobile LIKE '%$search%'
                 OR branch.name LIKE '%$search%'";
}

/* ---------- TOTAL RECORDS ---------- */
$countResult = $conn->query("
  SELECT COUNT(*) AS total 
  FROM students 
  LEFT JOIN branch ON students.branch_id = branch.id
  $searchSql
");

$totalRecords = $countResult->fetch_assoc()['total'];

if ($totalRecords % $limit == 0) {
  $totalPages = $totalRecords / $limit;
} else {
  $totalPages = intdiv($totalRecords, $limit) + 1;
}

$students = [];
$s = $conn->query("
  SELECT students.id, students.name, students.email, students.mobile,
         students.branch_id, branch.name AS branch_name
  FROM students
  LEFT JOIN branch ON students.branch_id = branch.id
  $searchSql
  LIMIT $limit OFFSET $offset
");

while ($row = $s->fetch_assoc()) {
  $students[] = $row;
}

$errors = [];
$success = "";

if (isset($_POST['update'])) {

  $id     = $_POST['id'];
  $name   = trim($_POST['name']);
  $email  = trim($_POST['email']);
  $mobile = trim($_POST['mobile']);
  $branch = trim($_POST['branch_id']);

  if ($name === "") $errors['name'] = "Name is required.";

  if ($email === "") {
    $errors['email'] = "Email is required.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "Invalid email format.";
  }

  if ($mobile === "") {
    $errors['mobile'] = "Mobile is required.";
  } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
    $errors['mobile'] = "Mobile must be 10 digits.";
  }

  if ($branch === "") $errors['branch'] = "Select a branch.";

  if (empty($errors)) {

    $check = $conn->query("
      SELECT id FROM students 
      WHERE (email='$email' OR mobile='$mobile') AND id != $id
    ");

    if ($check->num_rows > 0) {

      $checkEmail = $conn->query("
        SELECT id FROM students WHERE email='$email' AND id != $id
      ");
      if ($checkEmail->num_rows > 0) {
        $errors['email'] = "Email already exists.";
      }

      $checkMobile = $conn->query("
        SELECT id FROM students WHERE mobile='$mobile' AND id != $id
      ");
      if ($checkMobile->num_rows > 0) {
        $errors['mobile'] = "Mobile already exists.";
      }

    }
  }

  if (empty($errors)) {

    $conn->query("
      UPDATE students 
      SET name='$name', email='$email', mobile='$mobile', branch_id='$branch'
      WHERE id=$id
    ");

    header("Location: " . $_SERVER['PHP_SELF'] . "?updated=1");
    exit;
  }
}

if (isset($_POST['delete'])) {
  $id = $_POST['id'];
  $conn->query("DELETE FROM students WHERE id=$id");

  header("Location: " . $_SERVER['PHP_SELF'] . "?deleted=1");
  exit;
}

if (isset($_GET['updated'])) $success = "Student updated!";
if (isset($_GET['deleted'])) $success = "Student deleted!";
if (isset($_GET['created'])) $success = "Student created!";
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Students</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

  <style>
    .field-error {
      color: red;
      font-size: 14px;
      margin-top: 4px;
    }
  </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container my-5">

<?php if (!empty($success)): ?>
  <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<table class="table">
  <thead class="thead-dark">
    <tr>
      <th>#</th>
      <th>Name</th>
      <th>Email</th>
      <th>Mobile</th>
      <th>Branch</th>
      <th>Action</th>
    </tr>
  </thead>

  <tbody>
  <?php if (empty($students)): ?>
    <tr><td colspan="6" class="text-center">No matching results found</td></tr>
  <?php endif; ?>

  <?php foreach ($students as $i => $s): ?>
    <tr>
      <td><?= $offset + $i + 1 ?></td>
      <td><?= htmlspecialchars($s['name']) ?></td>
      <td><?= htmlspecialchars($s['email']) ?></td>
      <td><?= htmlspecialchars($s['mobile']) ?></td>
      <td><?= htmlspecialchars($s['branch_name']) ?></td>
      <td>

        <button class="btn btn-primary btn-sm editBtn"
          data-id="<?= $s['id'] ?>"
          data-name="<?= htmlspecialchars($s['name']) ?>"
          data-email="<?= htmlspecialchars($s['email']) ?>"
          data-mobile="<?= htmlspecialchars($s['mobile']) ?>"
          data-branch="<?= $s['branch_id'] ?>"
          data-toggle="modal" data-target="#editModal">Edit</button>

        <button class="btn btn-danger btn-sm deleteBtn"
          data-id="<?= $s['id'] ?>"
          data-toggle="modal" data-target="#deleteModal">Delete</button>

      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

<nav>
  <ul class="pagination justify-content-center">
    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
      <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= $search ?>">Previous</a>
    </li>

    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
      <li class="page-item <?= $p == $page ? 'active' : '' ?>">
        <a class="page-link" href="?page=<?= $p ?>&search=<?= $search ?>"><?= $p ?></a>
      </li>
    <?php endfor; ?>

    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
      <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= $search ?>">Next</a>
    </li>
  </ul>
</nav>

</div>

<div class="modal fade" id="editModal">
<div class="modal-dialog">
<form method="POST">
<div class="modal-content">

  <div class="modal-header">
    <h5>Edit Student</h5>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
  </div>

  <div class="modal-body">

    <input type="hidden" name="id" id="edit_id" value="<?= $id ?? '' ?>">

    <div class="form-group">
      <label>Name</label>
      <input type="text" class="form-control" name="name" id="edit_name" value="<?= $name ?? '' ?>">
      <?php if (isset($errors['name'])): ?><div class="field-error"><?= $errors['name'] ?></div><?php endif; ?>
    </div>

    <div class="form-group">
      <label>Email</label>
      <input type="email" class="form-control" name="email" id="edit_email" value="<?= $email ?? '' ?>">
      <?php if (isset($errors['email'])): ?><div class="field-error"><?= $errors['email'] ?></div><?php endif; ?>
    </div>

    <div class="form-group">
      <label>Mobile</label>
      <input type="number" class="form-control" name="mobile" id="edit_mobile" value="<?= $mobile ?? '' ?>">
      <?php if (isset($errors['mobile'])): ?><div class="field-error"><?= $errors['mobile'] ?></div><?php endif; ?>
    </div>

    <div class="form-group">
      <label>Branch</label>
      <select class="form-control" name="branch_id" id="edit_branch">
        <option value="">Select Branch</option>
        <?php foreach ($branches as $b): ?>
        <option value="<?= $b['id'] ?>" <?= (isset($branch) && $branch==$b['id']) ? 'selected' : '' ?>>
          <?= $b['name'] ?>
        </option>
        <?php endforeach; ?>
      </select>
      <?php if (isset($errors['branch'])): ?><div class="field-error"><?= $errors['branch'] ?></div><?php endif; ?>
    </div>

  </div>

  <div class="modal-footer">
    <button type="submit" name="update" class="btn btn-primary">Update</button>
  </div>

</div>
</form>
</div>
</div>

<div class="modal fade" id="deleteModal">
<div class="modal-dialog">
<form method="POST">
<div class="modal-content">

  <div class="modal-header">
    <h5>Delete Student</h5>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
  </div>

  <div class="modal-body">
    <input type="hidden" name="id" id="delete_id">
    <p>Are you sure?</p>
  </div>

  <div class="modal-footer">
    <button type="submit" name="delete" class="btn btn-danger">Delete</button>
  </div>

</div>
</form>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$('.editBtn').click(function(){
  $('#edit_id').val($(this).data('id'));
  $('#edit_name').val($(this).data('name'));
  $('#edit_email').val($(this).data('email'));
  $('#edit_mobile').val($(this).data('mobile'));
  $('#edit_branch').val($(this).data('branch'));
});

$('.deleteBtn').click(function(){
  $('#delete_id').val($(this).data('id'));
});

<?php if (!empty($errors) && isset($_POST['update'])): ?>
$(document).ready(function(){
  $('#editModal').modal('show');
});
<?php endif; ?>
</script>

</body>
</html>
