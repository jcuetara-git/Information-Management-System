<!-- ================= ADD INFO MODAL ================= -->
<div class="modal-overlay" id="infoModal">
    <div class="personal-modal">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <form class="personal-form" method="POST" action="save-student.php" onsubmit="return confirmSave()">
            <h3 class="form-title">Student Personal Information</h3>
            <div class="form-grid">
                <!-- COLUMN 1 -->
                <div class="form-column">
                    <div class="form-group">
                        <label>ID Number</label> 
                        <input type="text" name="id_number" value="<?= htmlspecialchars($student_no); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($first_name); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($last_name); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" id="dob" name="dob" required>
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" id="age" name="age" required readonly>
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <div class="radio-group">
                            <label><input type="radio" name="gender" value="Male" required> Male</label>
                            <label><input type="radio" name="gender" value="Female"> Female</label>
                        </div>
                    </div>
                </div>
                <!-- COLUMN 2 -->
                <div class="form-column">
                    <div class="form-group">
                        <label>Civil Status</label>
                        <input type="text" name="civil_status" required>
                    </div>
                    <div class="form-group">
                        <label>Religion</label>
                        <input type="text" name="religion">
                    </div>
                    <div class="form-group">
                        <label>Permanent Address</label>
                        <textarea name="permanent_address" rows="2" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Provincial/City Address</label>
                        <textarea name="city_address" rows="2" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Housing Type</label>
                        <select name="housing_type">
                            <option value="" disabled selected>Select</option>
                            <option value="Owned">Owned</option>
                            <option value="Rented">Rented</option>
                            <option value="Free">Staying for Free</option>
                        </select>
                    </div>
                </div>
                <!-- COLUMN 3 -->
                <div class="form-column">
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" required>
                    </div>
                    <div class="form-group">
                        <label>Emergency Contact Person</label>
                        <input type="text" name="emergency_person" required>
                    </div>
                    <div class="form-group">
                        <label>Emergency Contact No.</label>
                        <input type="text" name="emergency_number" required>
                    </div>
                    <div class="form-group">
                        <label>Father's Name</label>
                        <input type="text" name="father_name">
                    </div>
                    <div class="form-group">
                        <label>Father's Occupation</label>
                        <input type="text" name="father_occupation">
                    </div>
                    <div class="form-group">
                        <label>Mother's Name</label>
                        <input type="text" name="mother_name">
                    </div>
                    <div class="form-group">
                        <label>Mother's Occupation</label>
                        <input type="text" name="mother_occupation">
                    </div>
                </div>
                <!-- COLUMN 4 -->
                <div class="form-column">
                    <div class="form-group">
                        <label>Extracurricular Activities</label>
                        <textarea name="activities" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Previous GPA</label>
                        <input type="text" name="previous_gpa" required>
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