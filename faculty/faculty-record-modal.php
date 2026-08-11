<link rel="stylesheet" href="../assets/css/student-view-record.css">

<?php
// Fetch faculty profile row for viewing in the modal
$faculty_profile_row = null;

// Initialize portfolio document paths
$portfolio = [
    'cv' => null, 'tor' => null, 'diploma' => null, 'prc_license' => null,
    'certificates_membership' => null, 'seminars_regional' => null, 
    'seminars_national' => null, 'seminars_international' => null,
    'research_cert' => null, 'research_presenter' => null, 
    'community_extension' => null, 'test_questionnaires' => null, 
    'syllabi' => null, 'tos' => null
];

if (isset($conn) && !empty($faculty_id)) {
    $profile_stmt = $conn->prepare("SELECT * FROM faculty_profile WHERE faculty_no = ?");
    if ($profile_stmt) {
        $profile_stmt->bind_param("s", $faculty_id);
        $profile_stmt->execute();
        $profile_res = $profile_stmt->get_result();
        if ($profile_res && $profile_res->num_rows > 0) {
            $faculty_profile_row = $profile_res->fetch_assoc();
            foreach ($portfolio as $key => $val) {
                $portfolio[$key] = $faculty_profile_row[$key] ?? null;
            }
        }
        $profile_stmt->close();
    }
}

/**
 * Robust helper function to output attached documents.
 */
function renderDocumentStatus($filePath) {
    $filePath = trim((string)$filePath);

    if (!empty($filePath) && $filePath !== 'NULL' && $filePath !== '[]') {
        $filePath = str_replace(['[', ']', '"', "'"], '', $filePath);
        $filePath = str_replace('\\/', '/', $filePath);
        $filePath = str_replace('\\', '/', $filePath);
        $filePath = str_replace('../', '', $filePath);
        $filePath = ltrim($filePath, '/');

        $viewerUrl = 'view-file.php?file=' . urlencode($filePath);

        return '<p><a href="' . htmlspecialchars($viewerUrl) . '" target="_blank" style="color: #10b981; font-weight: 600; text-decoration: none;">
                    <i class="fa-solid fa-circle-check"></i> View Document
                </a></p>';
    }
    
    return '<p style="color: #ef4444; font-weight: 500;">
                <i class="fa-solid fa-circle-xmark"></i> Not Attached
            </p>';
}
?>

<!-- ================= VIEW RECORD MODAL ================= -->
<div class="modal-overlay" id="recordModal">
    <div class="personal-modal">
        <span class="close-btn" onclick="closeRecordModal()">&times;</span>
        <div class="container" style="width: 100%; padding: 10px 0;">
            <h2 style="margin-bottom: 20px; color: #1e293b;">My Faculty Record</h2>
            <?php if($faculty_profile_row): ?>

            <!-- PROFILE CARD -->
            <div class="profile-card">
                <div class="profile-left">
                    <form id="photoUploadForm" enctype="multipart/form-data">
                        <input type="file" name="photo" id="photoInput" hidden onchange="uploadProfilePhoto()">
                    </form>
                    <div class="student-pic-container" onclick="document.getElementById('photoInput').click();" title="Click to change photo">
                        <img 
                        id="facultyProfileImg"
                        src="<?= !empty($faculty_profile_row['profile_pic']) ? '../uploads/' . $faculty_profile_row['profile_pic'] : '../assets/student.jpg'; ?>" 
                        class="student-pic"
                        alt="Faculty Photo"
                        style="transition: opacity 0.3s; cursor: pointer;"
                        >
                    </div>
                    <div>
                        <h3><?= htmlspecialchars($full_name); ?></h3>
                        <p>ID Number: <?= htmlspecialchars($faculty_id); ?></p>
                    </div>
                </div>
            </div>

            <!-- ACCORDION -->
            <div class="accordion">
                <!-- CATEGORY 1: Profile Information -->
                <button type="button" class="accordion-btn">Profile Information</button>
                <div class="accordion-content">
                    <div class="info-grid">
                        <div class="field">
                            <label>Employment Status</label>
                            <p><?= !empty($faculty_profile_row['status']) ? htmlspecialchars($faculty_profile_row['status']) : '---' ?></p>
                        </div>
                        <div class="field">
                            <label>Email Address</label>
                            <p><?= !empty($faculty_profile_row['email']) ? htmlspecialchars($faculty_profile_row['email']) : '---' ?></p>
                        </div>
                        <div class="field">
                            <label>Contact Number</label>
                            <p><?= !empty($faculty_profile_row['contact_no']) ? htmlspecialchars($faculty_profile_row['contact_no']) : '---' ?></p>
                        </div>
                    </div>
                </div>

                <!-- CATEGORY 2: Personal & Academic Credentials -->
                <button type="button" class="accordion-btn">Personal & Academic Credentials</button>
                <div class="accordion-content">
                    <div class="info-grid">
                        <div class="field">
                            <label>Curriculum Vitae (CV)</label>
                            <?= renderDocumentStatus($portfolio['cv']) ?>
                        </div>
                        <div class="field">
                            <label>Updated PRC License</label>
                            <?= renderDocumentStatus($portfolio['prc_license']) ?>
                        </div>
                        <div class="field">
                            <label>Transcript of Records (TOR)</label>
                            <?= renderDocumentStatus($portfolio['tor']) ?>
                        </div>
                        <div class="field">
                            <label>Diploma</label>
                            <?= renderDocumentStatus($portfolio['diploma']) ?>
                        </div>
                    </div>
                </div>

                <!-- CATEGORY 3: Professional Associations & Trainings -->
                <button type="button" class="accordion-btn">Professional Associations & Trainings</button>
                <div class="accordion-content">
                    <div class="info-grid">
                        <div class="field">
                            <label>Certificate of Professional Membership</label>
                            <?= renderDocumentStatus($portfolio['certificates_membership']) ?>
                        </div>
                        <div class="field">
                            <label>Seminars Attended (Regional)</label>
                            <?= renderDocumentStatus($portfolio['seminars_regional']) ?>
                        </div>
                        <div class="field">
                            <label>Seminars Attended (National)</label>
                            <?= renderDocumentStatus($portfolio['seminars_national']) ?>
                        </div>
                        <div class="field">
                            <label>Seminars Attended (International)</label>
                            <?= renderDocumentStatus($portfolio['seminars_international']) ?>
                        </div>
                    </div>
                </div>

                <!-- CATEGORY 4: Research Works & Instructional Materials -->
                <button type="button" class="accordion-btn">Research Works & Instructional Materials</button>
                <div class="accordion-content">
                    <div class="info-grid">
                        <div class="field">
                            <label>Certificate of Researchers</label>
                            <?= renderDocumentStatus($portfolio['research_cert']) ?>
                        </div>
                        <div class="field">
                            <label>Certificate as Research Presenter</label>
                            <?= renderDocumentStatus($portfolio['research_presenter']) ?>
                        </div>
                        <div class="field">
                            <label>Community Extension Documentation</label>
                            <?= renderDocumentStatus($portfolio['community_extension']) ?>
                        </div>
                        <div class="field">
                            <label>Syllabi</label>
                            <?= renderDocumentStatus($portfolio['syllabi']) ?>
                        </div>
                        <div class="field">
                            <label>Test Questionnaires</label>
                            <?= renderDocumentStatus($portfolio['test_questionnaires']) ?>
                        </div>
                        <div class="field">
                            <label>Table of Specifications (TOS)</label>
                            <?= renderDocumentStatus($portfolio['tos']) ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php else: ?>
                <div class="card" style="text-align: center; padding: 40px 20px;">
                    <p style="color: #64748b; margin-bottom: 15px;">No record found. <br> Please add your faculty portfolio records information first.</p>
                    <a href="faculty-personal-info.php" style="display: inline-block; background: #f4b42c; color: black; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: 600;">
                        Add Faculty Portfolio Information
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Accordion Logic
    document.addEventListener("DOMContentLoaded", function () {
        const accButtons = document.querySelectorAll(".accordion-btn");
        accButtons.forEach(button => {
            button.addEventListener("click", function () {
                this.classList.toggle("active");
                const content = this.nextElementSibling;
                if (content.style.maxHeight) {
                    content.style.maxHeight = null;
                } else {
                    content.style.maxHeight = content.scrollHeight + "px";
                }
            });
        });
    });

    // AJAX function to upload faculty profile photo without page reload
    function uploadProfilePhoto() {
        const fileInput = document.getElementById('photoInput');
        const file = fileInput.files[0];
        
        if (!file) return;

        const formData = new FormData();
        formData.append('photo', file);

        const imgElement = document.getElementById('facultyProfileImg');
        if (!imgElement) return;

        const originalSrc = imgElement.src;
        imgElement.style.opacity = '0.5';

        fetch('faculty-upload-photo.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            return response.text().then(text => {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error("Non-JSON Response from Server:", text);
                    throw new Error("Server returned an invalid response. Check console.");
                }
            });
        })
        .then(data => {
            if (data.status === 'success') {
                imgElement.src = data.new_image + '?t=' + new Date().getTime();
                imgElement.style.opacity = '1';
            } else {
                alert("Upload failed: " + data.message);
                imgElement.src = originalSrc;
                imgElement.style.opacity = '1';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during upload: ' + error.message);
            imgElement.src = originalSrc;
            imgElement.style.opacity = '1';
        });
        
        fileInput.value = '';
    }
</script>