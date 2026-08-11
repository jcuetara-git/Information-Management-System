<div class="modal-overlay" id="portfolioModalOverlay">
    <div class="personal-modal">
        <span class="close-btn" onclick="confirmCancel(event)">&times;</span>
        
        <form class="personal-form" method="POST" enctype="multipart/form-data" onsubmit="return confirmSubmission()">
            <h3 class="form-title">Upload Faculty Portfolio</h3>
            
            <p style="font-size: 13.5px; color: #64748b; margin-bottom: 25px; line-height: 1.5;">
                Please select and upload your official credentials and academic file work here. 
                <span style="color: #f4b42a; font-weight: 600;">You can select 2 or more files at the same time for any field below by holding down Ctrl or Cmd while picking files.</span>
            </p>
            
            <?= $msg ?>

            <div class="form-grid">
                <!-- COLUMN 1 -->
                <div class="form-column">
                    <div class="form-group">
                        <label>Registered Email Address</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($registered_email) ?>" readonly title="Managed by registration details">
                    </div>
                    <div class="form-group">
                        <label>Contact Number <span style="color: #ef4444;">*</span></label>
                        <input type="text" name="contact_no" value="<?= htmlspecialchars($existing_contact) ?>" placeholder="e.g. +63 912 345 6789" required>
                    </div>
                    <div class="form-group">
                        <label>Status <span style="color: #ef4444;">*</span></label>
                        <select name="status" required>
                            <option value="" disabled <?= empty($existing_status) ? 'selected' : '' ?>>Select your professional status</option>
                            <option value="Full-time Regular" <?= $existing_status === 'Full-time Regular' ? 'selected' : '' ?>>Full-time Regular</option>
                            <option value="Full-time Probationary" <?= $existing_status === 'Full-time Probationary' ? 'selected' : '' ?>>Full-time Probationary</option>
                            <option value="Part-time Lawyers" <?= $existing_status === 'Part-time Lawyers' ? 'selected' : '' ?>>Part-time Lawyer</option>
                            <option value="Part-time Instructor" <?= $existing_status === 'Part-time Instructor' ? 'selected' : '' ?>>Part-time Instructor</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Curriculum Vitae (CV) <span style="color: #ef4444;">*</span></label>
                        <input type="file" name="cv[]" multiple <?= empty($existing_contact) ? 'required' : '' ?>>
                    </div>
                    <div class="form-group">
                        <label>Updated PRC License <span style="color: #ef4444;">*</span></label>
                        <input type="file" name="prc_license[]" multiple <?= empty($existing_contact) ? 'required' : '' ?>>
                    </div>
                </div>

                <!-- COLUMN 2 -->
                <div class="form-column">
                    <div class="form-group">
                        <label>Transcript of Records (TOR) <span style="color: #ef4444;">*</span></label>
                        <input type="file" name="tor[]" multiple <?= empty($existing_contact) ? 'required' : '' ?>>
                    </div>
                    <div class="form-group">
                        <label>Diploma <span style="color: #ef4444;">*</span></label>
                        <input type="file" name="diploma[]" multiple <?= empty($existing_contact) ? 'required' : '' ?>>
                    </div>
                    <div class="form-group">
                        <label>Certificate of Professional Membership</label>
                        <input type="file" name="certificates_membership[]" multiple>
                    </div>
                    <div class="form-group">
                        <label>Seminars & Trainings (Regional)</label>
                        <input type="file" name="seminars_regional[]" multiple>
                    </div>
                    <div class="form-group">
                        <label>Seminars & Trainings (National)</label>
                        <input type="file" name="seminars_national[]" multiple>
                    </div>
                </div>

                <!-- COLUMN 3 -->
                <div class="form-column">
                    <div class="form-group">
                        <label>Seminars & Trainings (International)</label>
                        <input type="file" name="seminars_international[]" multiple>
                    </div>
                    <div class="form-group">
                        <label>Certificate of Researchers</label>
                        <input type="file" name="research_cert[]" multiple>
                    </div>
                    <div class="form-group">
                        <label>Certificate as Research Presenter</label>
                        <input type="file" name="research_presenter[]" multiple>
                    </div>
                    <div class="form-group">
                        <label>Community Extension Documentation</label>
                        <input type="file" name="community_extension[]" multiple>
                    </div>
                    <div class="form-group">
                        <label>Syllabi</label>
                        <input type="file" name="syllabi[]" multiple>
                    </div>
                </div>

                <!-- COLUMN 4 -->
                <div class="form-column">
                    <div class="form-group">
                        <label>Test Questionnaires</label>
                        <input type="file" name="test_questionnaires[]" multiple>
                    </div>
                    <div class="form-group">
                        <label>Table of Specifications (TOS)</label>
                        <input type="file" name="tos[]" multiple>
                    </div>
                </div>
            </div>

            <div class="modal-buttons">
                <button type="button" class="cancel-btn" onclick="confirmCancel(event)">Cancel</button>
                <button type="submit" name="submit_portfolio" class="save-btn">Submit Portfolio Documents</button>
            </div>
        </form>
    </div>
</div>