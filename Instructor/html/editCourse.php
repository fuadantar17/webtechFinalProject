<?php
require_once("../php/sessionCheck.php");
require_once("../database/db.php");

$id = $_GET['id'];

$sql = "SELECT * FROM courses WHERE id = $id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Course</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CUSTOM CSS -->
    <link rel="stylesheet" href="../css/editCourse.css">
</head>

<body>

<div class="card-box">

    <h3>Edit Course</h3>

    <form action="../php/updateCourse.php" method="POST">

        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

        <input type="text" class="form-control" name="title" value="<?php echo $row['title']; ?>" required>

        <textarea class="form-control" name="description" required><?php echo $row['description']; ?></textarea>

        <select class="form-control" name="status">
            <option value="draft" <?php if($row['status']=="draft") echo "selected"; ?>>Draft</option>
            <option value="active" <?php if($row['status']=="active") echo "selected"; ?>>Active</option>
        </select>

        <button type="submit" class="btn btn-success w-100">
            Update Course
        </button>

    </form>

    <!-- BACK BUTTON (BOTTOM) -->
    <a href="viewCourses.php" class="btn-back">⬅ Back to Courses</a>

</div>

</body>
</html>