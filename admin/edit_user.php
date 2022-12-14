<?php

session_start();

if (!$_SESSION || $_SESSION['user_type'] !== 'admin') {
  header("Location: ../index.php");
}

if (!isset($_GET['user']) && !isset($_GET['id'])) {
  header("Location: admin.php");
}
$user_type = $_GET['user'];
$user_id = $_GET['id'];

$error_message = "";
$success_message = "";

require "../components/database.php";

if ($_POST) {
  if ($user_type == 'admin') {
    $statement = $pdo->prepare(
      "SELECT * FROM login WHERE login_username=:username"
    );
  } elseif ($user_type == 'specialist') {
    $statement = $pdo->prepare(
      "SELECT * FROM login WHERE login_username=:username"
    );
  } elseif ($user_type == 'patient') {
    $statement = $pdo->prepare(
      "SELECT * FROM login WHERE login_username=:username"
    );
  }
  $statement->bindValue(":username", $_POST['username']);
  $statement->execute();
  $login = $statement->fetch(PDO::FETCH_ASSOC);

  $login_user_id = $login['login_admin_id'] ?? $login['login_specialist_id'] ?? $login['login_patient_id'] ?? '';

  if ($login && $login_user_id == $user_id) {

    if ($user_type == 'admin') {
      $statement = $pdo->prepare(
        "UPDATE admin 
        SET admin_name=:full_name, admin_mobile=:mobile, admin_email=:email, admin_gender=:gender 
        WHERE admin_id=:id"
      );
    } elseif ($user_type == 'patient') {
      $statement = $pdo->prepare(
        "UPDATE patient 
        SET patient_name=:full_name, patient_mobile=:mobile, patient_email=:email, patient_gender=:gender, patient_location=:location, patient_dob=:dob 
        WHERE patient_id=:id"
      );
      $statement->bindValue(":location", $_POST['location']);
      $statement->bindValue(":dob", $_POST['dob']);
    } elseif ($user_type == 'specialist') {
      $statement = $pdo->prepare(
        "UPDATE specialist 
        SET specialist_name=:full_name, specialist_mobile=:mobile, specialist_email=:email, specialist_gender=:gender, specialist_location=:location 
        WHERE specialist_id=:id"
      );
      $statement->bindValue(":location", $_POST['location']);
    }
    $statement->bindValue(":full_name", $_POST['full_name']);
    $statement->bindValue(":mobile", $_POST['mobile']);
    $statement->bindValue(":email", $_POST['email']);
    $statement->bindValue(":gender", $_POST['gender']);
    $statement->bindValue(":id", $user_id);
    $statement->execute();

    if ($user_type == 'admin') {
      $statement = $pdo->prepare(
        "UPDATE login 
        SET login_username=:username 
        WHERE login_admin_id=:id"
      );
    } elseif ($user_type == 'patient') {
      $statement = $pdo->prepare(
        "UPDATE login 
        SET login_username=:username 
        WHERE login_patient_id=:id"
      );
    } elseif ($user_type == 'specialist') {
      $statement = $pdo->prepare(
        "UPDATE login 
        SET login_username=:username 
        WHERE login_specialist_id=:id"
      );
    }
    $statement->bindValue(":username", $_POST['username']);
    $statement->bindValue(":id", $user_id);
    $statement->execute();

    $success_message = "User updated successfully!";
  } else {
    $error_message = "A user with the same username exists.";
  }
}

if ($user_type == 'admin') {
  $statement = $pdo->prepare(
    "SELECT * FROM admin 
    JOIN login 
    ON admin_id=login_admin_id 
    WHERE admin_id=:id"
  );
} elseif ($user_type == 'specialist') {
  $statement = $pdo->prepare(
    "SELECT * FROM specialist 
    JOIN login 
    ON specialist_id=login_specialist_id 
    WHERE specialist_id=:id"
  );
} elseif ($user_type == 'patient') {
  $statement = $pdo->prepare(
    "SELECT * FROM patient 
    JOIN login 
    ON patient_id=login_patient_id 
    WHERE patient_id=:id"
  );
}
$statement->bindValue(":id", $user_id);
$statement->execute();
$user = $statement->fetch(PDO::FETCH_ASSOC);

if ($_POST && $error_message) {
  $full_name = $_POST['full_name'];
  $username = $_POST['username'];
  $mobile = $_POST['mobile'];
  $email = $_POST['email'];
  $gender = $_POST['gender'];
  $location = $_POST['location'] ?? '';
  $dob = $_POST['dob'] ?? '';
} else {
  $full_name = $user['admin_name'] ?? $user['specialist_name'] ?? $user['patient_name'];
  $username = $user['login_username'] ?? $user['login_username'] ?? $user['login_username'];
  $mobile = $user['admin_mobile'] ?? $user['specialist_mobile'] ?? $user['patient_mobile'];
  $email = $user['admin_email'] ?? $user['specialist_email'] ?? $user['patient_email'];
  $gender = $user['admin_gender'] ?? $user['specialist_gender'] ?? $user['patient_gender'];
  $location = $user['specialist_location'] ?? $user['patient_location'] ?? '';
  $dob = $user['patient_dob'] ?? '';
}


include "../components/header.php";
include "../components/navigation.php";

?>

<main class="my-3 flex-grow-1">
  <div class="container-sm">
    <div>
      <a href="users.php?user=<?php echo $user_type ?>" class="btn btn-secondary">Back</a>
    </div>

    <form method="POST" class="border w-75 mx-auto p-3 shadow-sm">
      <h1 class="text-center text-primary border-3 border-bottom border-primary pb-3">
        <?php if ($user_type == 'admin') : ?>
          Edit Admin
        <?php elseif ($user_type == 'specialist') : ?>
          Edit Specialist
        <?php elseif ($user_type == 'patient') : ?>
          Edit Patient
        <?php endif; ?>
      </h1>
      <?php if ($error_message) : ?>
        <div class="alert alert-danger">
          <?php echo $error_message ?>
        </div>
      <?php endif; ?>
      <?php if ($success_message) : ?>
        <div class="alert alert-success">
          <?php echo $success_message ?>
        </div>
      <?php endif; ?>
      <div class="row mb-3">
        <div class="col">
          <label for="full_name" class="form-label fw-bold">Full Name:</label>
          <input type="text" name="full_name" id="full_name" class="form-control" value="<?php echo $full_name ?>" placeholder="Enter full name" required>
        </div>
        <div class="col">
          <label for="username" class="form-label fw-bold">Username:</label>
          <input type="text" name="username" id="username" class="form-control" value="<?php echo $username ?>" placeholder="Enter username" required>
        </div>
      </div>
      <div class="row mb-3">
        <div class="col">
          <label for="mobile" class="form-label fw-bold">Phone Number:</label>
          <input type="text" name="mobile" id="mobile" class="form-control" value="<?php echo $mobile ?>" maxlength="10" placeholder="Enter phone number" required>
        </div>
        <div class="col">
          <label for="email" class="form-label fw-bold">Email:</label>
          <input type="email" name="email" id="email" class="form-control" value="<?php echo $email ?>" placeholder="Enter email" required>
        </div>
      </div>
      <div class="row mb-3">
        <div class="col-6">
          <label for="gender" class="form-label fw-bold">Gender:</label>
          <select name="gender" id="gender" class="form-select" required>
            <option value="">Select gender...</option>
            <option value="male" <?php echo $gender == 'male' ? 'selected' : '' ?>>Male</option>
            <option value="female" <?php echo $gender == 'female' ? 'selected' : '' ?>>Female</option>
          </select>
        </div>
        <?php if ($user_type !== 'admin') : ?>
          <div class="col-6">
            <label for="location" class="form-label fw-bold">Location:</label>
            <input type="text" name="location" id="location" class="form-control" value="<?php echo $location ?>" placeholder="Enter location">
          </div>
        <?php endif; ?>
      </div>
      <?php if ($user_type == 'patient') : ?>
        <div class="row mb-3">
          <div class="col-6">
            <label for="dob" class="form-label fw-bold">Date of Birth:</label>
            <input type="date" name="dob" id="dob" class="form-control" value="<?php echo $dob ?>" max="<?php echo date('Y-m-d') ?>">
          </div>
        </div>
      <?php endif; ?>
      <div class="mb-3">
        <button type="submit" class="btn btn-success">Update</button>
      </div>
    </form>

  </div>
</main>

<script src="../js/alerts.js"></script>

<?php include "../components/footer.php" ?>