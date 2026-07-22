<!-- ================= INDIANA JONES MODAL ================= -->
<div class="modal-overlay" id="indianaJonesModal" style="display:none;">
    <div class="personal-modal" style="max-width: 500px;">
        <span class="close-btn" onclick="closeIndianaJonesModal()">&times;</span>
        
        <form class="personal-form" method="POST" action="process-indiana-jones.php" enctype="multipart/form-data">
            <h3 class="form-title"><i class="fa-solid fa-calendar-days"></i> Submit Indiana Jones LOU</h3>
            
            <p style="font-size: 13px; color: #64748b; margin-bottom: 20px;">
                Submit your Letter of Undertaking if you have accumulated three (3) or more consecutive absences.
            </p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group" style="margin-bottom: 10px;">
                    <label>First Name</label>
                    <!-- Pre-filled and read-only to prevent tampering -->
                    <input type="text" name="first_name" value="<?= htmlspecialchars($first_name); ?>" readonly style="background: #f1f5f9; cursor: not-allowed; border: 1px solid #cbd5e1;">
                </div>
                <div class="form-group" style="margin-bottom: 10px;">
                    <label>Last Name</label>
                    <input type="text" name="last_name" value="<?= htmlspecialchars($last_name); ?>" readonly style="background: #f1f5f9; cursor: not-allowed; border: 1px solid #cbd5e1;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div class="form-group">
                    <label>Year Level</label>
                    <select name="year_level" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit;">
                        <option value="" disabled selected>Select</option>
                        <option value="1st Year">1</option>
                        <option value="2nd Year">2</option>
                        <option value="3rd Year">3</option>
                        <option value="4th Year">4</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Number of Absences</label>
                    <input type="number" name="number_of_absences" min="3" placeholder="e.g. 3" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label>Date of Submission (Date Recorded)</label>
                <input type="date" name="date_recorded" value="<?= date('Y-m-d'); ?>" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>

            <div class="form-group" style="margin-bottom: 25px;">
                <label>Upload Letter of Undertaking (PDF only)</label>
                <input type="file" name="undertaking_file" accept=".pdf" required style="width: 100%; padding: 10px; border: 1px dashed #94a3b8; border-radius: 6px; background-color: #f8fafc; cursor: pointer;">
            </div>

            <div class="modal-buttons">
                <button type="button" class="cancel-btn" onclick="closeIndianaJonesModal()">Cancel</button>
                <button type="submit" class="save-btn">Submit Document</button>
            </div>
        </form>
    </div>
</div>