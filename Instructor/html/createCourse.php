<?php
require_once("../php/sessionCheck.php");
require_once("../database/db.php");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Create Course</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CUSTOM CSS -->
    <link rel="stylesheet" href="../css/createCourse.css">
</head>

<body>

<div class="card-box">

    <!-- BACK -->
    <a href="dashboard.php" class="btn-back">⬅ Back to Dashboard</a>

    <h3>Create Course</h3>

    <form action="../php/courseInsert.php" method="POST">

        <input type="text" class="form-control" name="title" placeholder="Course Title" required>

        <textarea class="form-control" name="description" placeholder="Course Description" required></textarea>

        <select class="form-control" name="subject_id" required>
            <option value="">Select Subject</option>

            <?php
            $sql = "SELECT * FROM subjects";
            $result = $conn->query($sql);

            while($row = $result->fetch_assoc()){
            ?>
                <option value="<?php echo $row['id']; ?>">
                    <?php echo $row['name']; ?>
                </option>
            <?php } ?>

        </select>

        <select class="form-control" name="enrollment_type">
            <option value="open">Open</option>
            <option value="approval">Approval</option>
        </select>

        <input type="number" class="form-control" name="max_students" placeholder="Max Students" required>

        <select class="form-control" name="status">
            <option value="draft">Draft</option>
            <option value="active">Publish</option>
        </select>

        <button type="submit" name="create_course" class="btn btn-success w-100">
            Create Course
        </button>

    </form>

</div>

</body>
</html>