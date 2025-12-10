<?php
include "dbcon.php";

$branches = [];
$errors = [];
$success = "";

if (isset($_POST['create'])) {
    $branch_name = trim($_POST['name']);

    if (empty($branch_name)) $errors[] = "Branch name is required.";

    if (empty($errors)) {
        $conn->query("INSERT INTO branch (name) VALUES ('$branch_name')");
        header("Location: ".$_SERVER['PHP_SELF']."?created=1");
        exit;
    }
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);

    if (empty($name)) $errors[] = "Branch name is required.";

    if (empty($errors)) {
        $conn->query("UPDATE branch SET name='$name' WHERE id=$id");
        header("Location: ".$_SERVER['PHP_SELF']."?updated=1");
        exit;
    }
}

if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $conn->query("DELETE FROM branch WHERE id=$id");
    header("Location: ".$_SERVER['PHP_SELF']."?deleted=1");
    exit;
}

$result = $conn->query("SELECT id, name FROM branch");
while ($row = $result->fetch_assoc()) $branches[] = $row;

if (isset($_GET['created'])) $success = "Branch added!";
if (isset($_GET['updated'])) $success = "Branch updated!";
if (isset($_GET['deleted'])) $success = "Branch deleted!";
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

  <style>
    .error-text { color: red; font-size: 14px; display:none; }
  </style>
</head>

<body>

<?php include 'navbar.php'; ?>

<div class="container my-5">

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
      <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
  </div>
<?php endif; ?>

<?php if ($success): ?>
  <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form action="" method="POST" id="createForm">
  <div class="form-group">
    <label>Branch Name</label>
    <input type="text" class="form-control" id="create_name" name="name">
    <small class="error-text" id="create_error">Branch name is required.</small>
  </div>
  <button type="submit" class="btn btn-primary" name="create">Add Branch</button>
</form>

<table class="table my-5">
  <thead class="thead-dark">
    <tr>
      <th>#</th>
      <th>Branch</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($branches as $index => $branch): ?>
    <tr>
      <td><?= $index+1 ?></td>
      <td><?= htmlspecialchars($branch['name']) ?></td>
      <td>
        <button 
          class="btn btn-primary btn-sm editBtn"
          data-id="<?= $branch['id'] ?>"
          data-name="<?= htmlspecialchars($branch['name']) ?>"
          data-toggle="modal" 
          data-target="#editModal"
        >Edit</button>

        <button 
          class="btn btn-danger btn-sm deleteBtn"
          data-id="<?= $branch['id'] ?>"
          data-toggle="modal"
          data-target="#deleteModal"
        >Delete</button>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>

</div>

<!-- EDIT MODAL -->
<div class="modal fade" id="editModal">
  <div class="modal-dialog">
    <form method="POST" action="" id="editForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5>Edit Branch</h5>
          <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="id" id="edit_id">

          <div class="form-group">
            <label>Branch Name</label>
            <input type="text" class="form-control" name="name" id="edit_name">
            <small class="error-text" id="edit_error">Branch name is required.</small>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" name="update" class="btn btn-primary">Save</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- DELETE MODAL -->
<div class="modal fade" id="deleteModal">
  <div class="modal-dialog">
    <form method="POST" action="">
      <div class="modal-content">
        <div class="modal-header">
          <h5>Delete Branch</h5>
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

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$('.editBtn').click(function(){
    $('#edit_id').val($(this).data('id'));
    $('#edit_name').val($(this).data('name'));
});

$('.deleteBtn').click(function(){
    $('#delete_id').val($(this).data('id'));
});

$('#createForm').submit(function(e){
    let name = $('#create_name').val().trim();
    if (name === "") {
        $('#create_error').show();
        e.preventDefault();
    } else {
        $('#create_error').hide();
    }
});

$('#editForm').submit(function(e){
    let name = $('#edit_name').val().trim();
    if (name === "") {
        $('#edit_error').show();
        e.preventDefault();
    } else {
        $('#edit_error').hide();
    }
});
</script>

</body>
</html>
