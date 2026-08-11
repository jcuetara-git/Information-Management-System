<!-- ================= ADD ALUMNI INFO MODAL ================= -->
<div class="modal-overlay" id="infoModal">
    <div class="personal-modal">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <form class="personal-form" method="POST" action="save-alumni.php" onsubmit="return confirmSave()">
            <h3 class="form-title">Alumni Personal Information</h3>
            <div class="form-grid">
                
                <!-- COLUMN 1: Basic Personal Info -->
                <div class="form-column">
                    <div class="form-group">
                        <label>ID Number</label> 
                        <input type="text" name="student_no" value="<?= htmlspecialchars($student_no ?? ''); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($first_name ?? ''); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" placeholder="Optional">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($last_name ?? ''); ?>" readonly>
                    </div>
                </div>

                <!-- COLUMN 2: Demographics & Contact -->
                <div class="form-column">
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" id="dob" name="dob" required>
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" id="age" name="age" required readonly>
                    </div>
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" placeholder="e.g. 09123456789" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <!-- Assumes $email is fetched from session in the main file -->
                        <input type="email" name="email_address" value="<?= htmlspecialchars($email ?? ''); ?>" required>
                    </div>
                </div>

                <!-- COLUMN 3: Academic & Career Profile -->
                <div class="form-column">
                    <div class="form-group">
                        <label>Year Graduated</label>
                        <input type="number" name="year_graduated" placeholder="YYYY" required>
                    </div>
                    <div class="form-group">
                        <label>Date of Licensure Exam</label>
                        <!-- Nullable in DB, so not marked required -->
                        <input type="date" name="date_of_licensure_exam">
                    </div>
                    <div class="form-group">
                        <label>PRC Board Rating (%)</label>
                        <!-- Nullable in DB, step="0.01" to match decimal(5,2) -->
                        <input type="number" step="0.01" name="prc_board_rating" placeholder="e.g. 85.50">
                    </div>
                    <div class="form-group">
                        <label>Current Job / Occupation</label>
                        <textarea name="current_job" rows="3" placeholder="Enter your current job title and company..." required></textarea>
                    </div>
                </div>

            </div>
            
            <div class="modal-buttons">
                <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
                <button type="submit" class="save-btn">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Auto-calculate Age based on selected Date of Birth
    document.getElementById('dob').addEventListener('change', function() {
        const dob = new Date(this.value);
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const m = today.getMonth() - dob.getMonth();
        
        // Subtract a year if the birthday hasn't occurred yet this year
        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
            age--;
        }
        
        document.getElementById('age').value = age > 0 ? age : 0;
    });

    // Make sure confirmSave function exists
    function confirmSave() {
        return confirm("Are you sure you want to save this information?");
    }
</script>