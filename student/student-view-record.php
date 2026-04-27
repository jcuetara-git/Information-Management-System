
<?php
session_start();
include("../config/db.php");

// Ensure the student is logged in
if(!isset($_SESSION['student_no'])){
    header("Location: ../auth/login.php");
    exit();
}

$student_no = $_SESSION['student_no'];

// Query the student_profile table for the logged-in student's record
$stmt = $conn->prepare("SELECT * FROM student_profile WHERE student_no = ?");
$stmt->bind_param("s", $student_no);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// Calculate age if date of birth exists
$age = "";
if($row && !empty($row['dob'])){
    $birthDate = new DateTime($row['dob']);
    $today = new DateTime();
    $age = $today->diff($birthDate)->y;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Record</title>
    <link rel="stylesheet" href="../assets/css/student-view-record.css">
</head>

<body>

<div class="logo-section">
    <div class="logo-left">
        <div class="logo-circle">
            <img src="../assets/logo.png">
        </div>
        <div>
            <h2>College of Criminal Justice</h2>
            <p>Center of Development in Criminology</p>
        </div>
    </div>
    <div class="profile-icon">👤</div>
</div>

<div class="container">

    <h2>My Student Record</h2>

    <?php if($row): ?>

    <!-- PROFILE -->
    <div class="profile-card">
        <div class="profile-left">

            <form action="student-upload-photo.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="photo" id="photoInput" hidden onchange="this.form.submit()">
            </form>

            <img 
            src="<?= !empty($row['profile_pic']) ? '../uploads/'.$row['profile_pic'] : '../assets/student.jpg'; ?>" 
            class="student-pic"
            onclick="document.getElementById('photoInput').click();"
            >

            <div>
                <h3><?= htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?></h3>
                <p>ID Number: <?= htmlspecialchars($row['student_no']); ?></p>
            </div>
        </div>
    </div>

    <!-- ACCORDION -->
    <div class="accordion">

        <button class="accordion-btn">Personal Information</button>
        <div class="accordion-content">
            <div class="info-grid">
                <div class="field">
                    <label>First Name</label>
                    <p><?= htmlspecialchars($row['first_name']); ?></p>
                </div>
                <div class="field">
                    <label>Gender</label>
                    <p><?= htmlspecialchars($row['gender']); ?></p>
                </div>
                <div class="field">
                    <label>Middle Name</label>
                    <p><?= htmlspecialchars($row['middle_name']); ?></p>
                </div>
                <div class="field">
                    <label>Civil Status</label>
                    <p><?= htmlspecialchars($row['civil_status']); ?></p>
                </div>
                <div class="field">
                    <label>Last Name</label>
                    <p><?= htmlspecialchars($row['last_name']); ?></p>
                </div>
                <div class="field">
                    <label>Contact Number</label>
                    <p><?= htmlspecialchars($row['contact_number']); ?></p>
                </div>
                <div class="field">
                    <label>Date of Birth</label>
                    <p><?= htmlspecialchars($row['dob']); ?></p>
                </div>
                <div class="field">
                    <label>Activities</label>
                    <p><?= htmlspecialchars($row['activities']); ?></p>
                </div>
                <div class="field">
                    <label>Age</label>
                    <p><?= $age; ?></p>
                </div>
                <div class="field">
                    <label>Previous GPA</label>
                    <p><?= htmlspecialchars($row['previous_gpa']); ?></p>
                </div>
            </div>
        </div>

        <button class="accordion-btn">Residential Information</button>
        <div class="accordion-content">
            <div class="info-grid">
                <div class="field full-width">
                    <label>Permanent Address</label>
                    <p><?= htmlspecialchars($row['permanent_address']); ?></p>
                </div>
                <div class="field">
                    <label>City Address</label>
                    <p><?= htmlspecialchars($row['city_address']); ?></p>
                </div>
                <div class="field">
                    <label>Housing Type</label>
                    <p><?= htmlspecialchars($row['housing_type']); ?></p>
                </div>
            </div>
        </div>

        <button class="accordion-btn">Family Information</button>
        <div class="accordion-content">
            <div class="info-grid">
                <div class="field">
                    <label>Father</label>
                    <p><?= htmlspecialchars($row['father_name']); ?></p>
                </div>
                <div class="field">
                    <label>Father Occupation</label>
                    <p><?= htmlspecialchars($row['father_occupation']); ?></p>
                </div>
                <div class="field">
                    <label>Mother</label>
                    <p><?= htmlspecialchars($row['mother_name']); ?></p>
                </div>
                <div class="field">
                    <label>Mother Occupation</label>
                    <p><?= htmlspecialchars($row['mother_occupation']); ?></p>
                </div>
            </div>
        </div>

        <button class="accordion-btn">Emergency Contact</button>
        <div class="accordion-content">
            <div class="info-grid">
                <div class="field">
                    <label>Contact Person</label>
                    <p><?= htmlspecialchars($row['emergency_person']); ?></p>
                </div>
                <div class="field">
                    <label>Contact Number</label>
                    <p><?= htmlspecialchars($row['emergency_number']); ?></p>
                </div>
            </div>
        </div>

    </div>

    <?php else: ?>
        <div class="card" style="padding: 20px; text-align: center;">
            <p>No record found. Please add your personal information first.</p>
            <a href="student-add-info.php" style="color: #007bff; text-decoration: underline;">Add Info Now</a>
        </div>
    <?php endif; ?>

</div>

<!-- ACCORDION SCRIPT -->
<script>
document.querySelectorAll(".accordion-btn").forEach(btn => {
    btn.addEventListener("click", function(){
        let panel = this.nextElementSibling;
        panel.style.maxHeight = panel.style.maxHeight ? null : panel.scrollHeight + "px";
    });
});
</script>

</body>
</html>
