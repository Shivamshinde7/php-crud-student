<?php
include "dbcon.php";

$branches = [];
$result = $conn->query("SELECT id, name FROM branch");
while ($row = $result->fetch_assoc()) {
    $branches[] = $row;
}

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name   = trim($_POST['name']);
    $email  = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $branch = trim($_POST['branch_id']);

    if (empty($name)) {
        $errors['name'] = "Name is required.";
    }

    if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }

    if (empty($mobile)) {
        $errors['mobile'] = "Mobile number is required.";
    } elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $errors['mobile'] = "Mobile number must be 10 digits.";
    }

    if (empty($branch)) {
        $errors['branch'] = "Please select a branch.";
    }


    if (empty($errors)) {

        $check = $conn->query("SELECT id FROM students WHERE email='$email' OR mobile='$mobile'");

        if ($check->num_rows > 0) {

            $checkEmail = $conn->query("SELECT id FROM students WHERE email='$email'");
            if ($checkEmail->num_rows > 0) {
                $errors['email'] = "Email already exists.";
            }

            $checkMobile = $conn->query("SELECT id FROM students WHERE mobile='$mobile'");
            if ($checkMobile->num_rows > 0) {
                $errors['mobile'] = "Mobile already exists.";
            }

        } else {

            $sql = "INSERT INTO students (name, email, mobile, branch_id)
                    VALUES ('$name', '$email', '$mobile', '$branch')";

            if ($conn->query($sql)) {
                header("Location: index.php?created=1");
                exit;
            }
        }
    }
}



// print_r($_SERVER);
// exit;
?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

    <!-- <title>Hello, world!</title> -->
</head>

<body>
<style>
  .field-error {
    color: red;
    font-size: 14px;
    margin-top: 4px;
  }
</style>

    <?php include 'navbar.php'; ?>

    <?php
    echo $conn ? "" : "Connection failed";
    ?>
    <div class="container my-5">

     <?php if (!empty($success)): ?>
  <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>



   <form action="" method="POST">

    <div class="form-group">
        <label>Name</label>
        <input 
            type="text" 
            class="form-control" 
            name="name"
            value="<?= $name ?? '' ?>">

        <?php if (isset($errors['name'])): ?>
            <div class="field-error"><?= $errors['name'] ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label>Email</label>
        <input 
            type="email" 
            class="form-control" 
            name="email"
            value="<?= $email ?? '' ?>">

        <?php if (isset($errors['email'])): ?>
            <div class="field-error"><?= $errors['email'] ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label>Mobile</label>
        <input 
            type="number" 
            class="form-control" 
            name="mobile"
            value="<?= $mobile ?? '' ?>">

        <?php if (isset($errors['mobile'])): ?>
            <div class="field-error"><?= $errors['mobile'] ?></div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label>Branch</label>
        <select class="form-control" name="branch_id">
            <option value="">Select Branch</option>

            <?php foreach ($branches as $b): ?>
                <option 
                    value="<?= $b['id']; ?>"
                    <?= (isset($branch) && $branch == $b['id']) ? 'selected' : '' ?>>
                    <?= $b['name']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if (isset($errors['branch'])): ?>
            <div class="field-error"><?= $errors['branch'] ?></div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary">Submit</button>
</form>


    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>

</body>

</html>