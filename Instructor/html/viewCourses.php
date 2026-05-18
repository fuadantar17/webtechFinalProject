<?php
require_once("../php/sessionCheck.php");
require_once("../database/db.php");
?>

<!DOCTYPE html>
<html>

<head>
    <title>My Courses</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="../css/courses.css">
</head>

<body>

<div class="container mt-5">

    <div class="card-box">

        <h3 class="title"> My Courses</h3>

        <div class="table-responsive">

            <table class="table table-hover">

                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Edit</th>
                        <th>View</th>
                        <th>Delete</th>
                    </tr>
                </thead>

                <tbody>

                <?php
                $instructor_id = $_SESSION['instructor_id'];

                $sql = "SELECT * FROM courses WHERE instructor_id = $instructor_id";
                $result = $conn->query($sql);

                while($row = $result->fetch_assoc()){
                ?>

                <tr id="row_<?php echo $row['id']; ?>">

                    <td><b><?php echo $row['title']; ?></b></td>
                    <td><?php echo substr($row['description'],0,50); ?>...</td>

                    <td>
                        <span class="status <?php echo $row['status']; ?>">
                            <?php echo $row['status']; ?>
                        </span>
                    </td>

                    <td>
                        <a class="btn btn-warning btn-sm" href="editCourse.php?id=<?php echo $row['id']; ?>">Edit</a>
                    </td>

                    <td>
                        <a class="btn btn-info btn-sm" href="courseDetails.php?id=<?php echo $row['id']; ?>">View</a>
                    </td>

                    <td>
                        <button class="btn btn-danger btn-sm" onclick="deleteCourse(<?php echo $row['id']; ?>)">Delete</button>
                    </td>

                </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

        
        <div class="bottom-bar">
            <a href="dashboard.php" class="btn-back">⬅ Back to Dashboard</a>
        </div>

    </div>

</div>

<script src="../js/courses.js"></script>

</body>
</html>