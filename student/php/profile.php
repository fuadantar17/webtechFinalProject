<?php
require_once "auth.php";
requireStudentLogin();
require_once "../database/db.php";

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $program = trim($_POST["program"] ?? "");
    $phone = trim($_POST["phone"] ?? "");

    if ($name === "" || $program === "") {
        header("Location: profile.php?error=Name and program are required");
        exit;
    }

    if (isset($_FILES["profile_pic"]) && $_FILES["profile_pic"]["error"] !== UPLOAD_ERR_NO_FILE) {
        $allowed = ["image/jpeg" => "jpg", "image/png" => "png", "image/webp" => "webp"];
        $mime = mime_content_type($_FILES["profile_pic"]["tmp_name"]);

        if (!isset($allowed[$mime]) || $_FILES["profile_pic"]["size"] > 2 * 1024 * 1024) {
            header("Location: profile.php?error=Invalid image. Use JPG, PNG or WEBP under 2MB");
            exit;
        }

        $pic = "student_" . time() . "_" . rand(1000, 9999) . "." . $allowed[$mime];
        move_uploaded_file($_FILES["profile_pic"]["tmp_name"], "../pictures/" . $pic);

        $stmt = $conn->prepare("UPDATE users SET name = ?, program = ?, phone = ?, profile_pic = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $program, $phone, $pic, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, program = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $program, $phone, $user_id);
    }

    $stmt->execute();
    $_SESSION["name"] = $name;

    header("Location: profile.php?success=Profile updated");
    exit;
}

$stmt = $conn->prepare("SELECT name, email, student_id, program, phone, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

include "../html/header.html";
?>
<div class="card large-card">
    <h2>Manage Profile</h2>
    <img class="avatar" src="../pictures/<?php echo e($user['profile_pic'] ?: 'default.png'); ?>">
    <p><b>Student ID:</b> <?php echo e($user['student_id']); ?></p>
    <p><b>Email:</b> <?php echo e($user['email']); ?></p>

    <form method="POST" enctype="multipart/form-data">
        <label>Name</label>
        <input name="name" value="<?php echo e($user['name']); ?>" required>

        <label>Program</label>
        <input name="program" value="<?php echo e($user['program']); ?>" required>

        <label>Phone</label>
        <input name="phone" value="<?php echo e($user['phone']); ?>">

        <label>Change Profile Picture</label>
        <input type="file" name="profile_pic" accept="image/jpeg,image/png,image/webp">

        <button>Update Profile</button>
    </form>
</div>
<?php include "../html/footer.html"; ?>