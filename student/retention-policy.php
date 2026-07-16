<?php
include("../config/auth.php");
include("../config/db.php"); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 2. Capture form data
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $memo_date = $_POST['memo_date'];
    $year_level = $_POST['year_level'];
    $failed = $_POST['failed_subjects'];
    
    // 3. Handle File Upload (as before)
    $uploadDir = "uploads/";
    $filePath = $uploadDir . basename($_FILES["undertaking_file"]["name"]);
    move_uploaded_file($_FILES["undertaking_file"]["tmp_name"], $filePath);

    // 4. Insert into Database
    $stmt = $conn->prepare("INSERT INTO retention_records (firstname, lastname, memo_issued_date, year_level, failed_subjects_count, undertaking_file_path) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssis", $firstname, $lastname, $memo_date, $year_level, $failed, $filePath);
    
    if ($stmt->execute()) {
        echo "Data successfully saved to database.";
    } else {
        echo "Database error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>retention-policy</title>
</head>
<body>
    <!-- RETENTION POLICY MODAL -->
    <div class="modal-overlay" id="retentionModal">
    <div class="personal-modal">
        <span class="close-btn" onclick="closeRetentionModal()">&times;</span>
        
        <form class="personal-form" action="process_policy.php" method="POST" enctype="multipart/form-data">
            <h3 class="form-title">Student Retention Details</h3>
            
            <div class="form-grid">
                <!-- COLUMN 1 -->
                <div class="form-column">
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="firstname" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="middlename">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="lastname" required>
                    </div>
                </div>

                <!-- COLUMN 2 -->
                <div class="form-column">
                    <div class="form-group">
                        <label>Year Level</label>
                        <input type="text" name="year_level" required>
                    </div>
                    <div class="form-group">
                        <label>Date Memo Issued</label>
                        <input type="date" name="memo_date" required>
                    </div>
                    <div class="form-group">
                        <label>Failed Prof. Subjects</label>
                        <input type="number" name="failed_subjects" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Submit Letter of Undertaking</label>
                <input type="file" name="undertaking_file" required>
            </div>

            <div class="modal-buttons">
                <button type="button" class="cancel-btn" onclick="closeRetentionModal()">Cancel</button>
                <button type="submit" class="save-btn">Submit</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRetentionModal() {
    document.getElementById('retentionModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeRetentionModal() {
    document.getElementById('retentionModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}
</script>

</body>
</html>
