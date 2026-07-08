<?php 
include("../config/db.php");
include("../config/auth.php");

// Active search and filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$audience_filter = isset($_GET['audience']) ? trim($_GET['audience']) : '';

// --- BACKEND PROCESSING LOGIC FOR EDIT & DELETE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'update') {
        $id = intval($_POST['announcement_id']);
        $title = trim($_POST['title']);
        $audience = $_POST['audience'];
        $target_user_id = ($audience === 'specific_user') ? trim($_POST['target_user_id']) : null;
        $status = $_POST['status'];
        $message = trim($_POST['message']);

        $stmt = $conn->prepare("UPDATE announcements SET title = ?, target_audience = ?, target_user_id = ?, status = ?, message = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $title, $audience, $target_user_id, $status, $message, $id);
        
        if ($stmt->execute()) {
            header("Location: admin-announcement.php?success=updated");
            exit();
        }
    }

    if ($action === 'delete') {
        $id = intval($_POST['announcement_id']);
        
        $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            header("Location: admin-announcement.php?success=deleted");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>admin-announcement</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="admin-announcement.css">
</head>

<body>

<div class="main-container">
    <?php include("../includes/sidebar.php"); ?>

    <main class="dashboard-container" id="mainContent" role="main">
        
        <section class="card welcome-card">
            <div class="welcome-content">
                <h2>Announcements Management</h2>
                <p>Broadcast updates to students, faculty, and alumni.</p>
            </div>
        </section>

        <section class="card filters-container">
            <div class="filters-header">
                <h3><i class="fa-solid fa-filter"></i> Search & Filter</h3>
            </div>
            
            <form method="GET" class="filters-grid">
                <div class="filter-group search-input">
                    <label for="search">Search Announcement</label>
                    <i class="fa-solid fa-search search-icon"></i>
                    <input type="text" id="search" name="search" placeholder="Search by title or content..." value="<?= htmlspecialchars($search) ?>">
                </div>

                <div class="filter-group">
                    <label for="audience">Target Audience</label>
                    <select id="audience" name="audience">
                        <option value="">All Audiences</option>
                        <option value="students" <?= $audience_filter === 'students' ? 'selected' : '' ?>>Students</option>
                        <option value="faculty" <?= $audience_filter === 'faculty' ? 'selected' : '' ?>>Faculty</option>
                        <option value="alumni" <?= $audience_filter === 'alumni' ? 'selected' : '' ?>>Alumni</option>
                    </select>
                </div>

                <div class="filter-buttons">
                    <button type="submit" class="filter-btn"><i class="fa-solid fa-search"></i> Search</button>
                    <a href="admin-announcement.php" class="reset-btn"><i class="fa-solid fa-redo"></i> Reset</a>
                </div>
            </form>
        </section>

        <button class="filter-btn" style="margin-bottom: 20px; width: auto; padding: 12px 25px;" onclick="document.getElementById('announcementModal').style.display='flex'">
            <i class="fa-solid fa-plus"></i> New Announcement
        </button>

        <section class="card table-container">
            <div class="table-wrapper">
                <?php
                // Build dynamic SQL query based on search inputs
                $query = "SELECT * FROM announcements WHERE 1=1";
                $types = "";
                $params = [];

                if (!empty($search)) {
                    $query .= " AND (title LIKE ? OR message LIKE ?)";
                    $searchTerm = "%" . $search . "%";
                    $params[] = $searchTerm;
                    $params[] = $searchTerm;
                    $types .= "ss";
                }

                if (!empty($audience_filter)) {
                    $query .= " AND target_audience = ?";
                    $params[] = $audience_filter;
                    $types .= "s";
                }

                $query .= " ORDER BY created_at DESC";

                // Prepare and execute the statement
                $stmt = $conn->prepare($query);
                if (!empty($params)) {
                    $stmt->bind_param($types, ...$params);
                }
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result && $result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date Posted</th>
                            <th>Title</th>
                            <th>Audience</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td data-label="Date Posted"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                                <td data-label="Title"><?= htmlspecialchars($row['title']) ?></td>
                                <td data-label="Audience"><?= ucfirst(htmlspecialchars($row['target_audience'])) ?></td>
                                <td data-label="Status">
                                    <span style="color: <?= $row['status'] === 'published' ? 'green' : 'orange' ?>; font-weight: bold;">
                                        <?= ucfirst(htmlspecialchars($row['status'])) ?>
                                    </span>
                                </td>
                                <td data-label="Actions" class="action-btns">
                                    <!-- Added data attributes to map row details directly to the edit/delete modals -->
                                    <button class="edit-btn-table" 
                                            data-id="<?= $row['id'] ?>"
                                            data-title="<?= htmlspecialchars($row['title']) ?>"
                                            data-audience="<?= htmlspecialchars($row['target_audience']) ?>"
                                            data-user-id="<?= htmlspecialchars($row['target_user_id'] ?? '') ?>"
                                            data-status="<?= htmlspecialchars($row['status']) ?>"
                                            data-message="<?= htmlspecialchars($row['message']) ?>">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                    <button class="delete-btn" 
                                            data-id="<?= $row['id'] ?>"
                                            data-title="<?= htmlspecialchars($row['title']) ?>">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div id="noDataMessage" style="text-align: center; padding: 40px; color: #777;">
                    <i class="fa-solid fa-folder-open" style="font-size: 40px; margin-bottom: 10px; display: block; opacity: 0.5;"></i>
                    <p>No announcements found. Click "New Announcement" to create one.</p>
                </div>
                <?php endif; 
                $stmt->close();
                ?>
            </div>
        </section>
    </main>
</div>

<!-- Modal -->
<div id="announcementModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('announcementModal').style.display='none'">&times;</span>
        <h2>Create Announcement</h2>
        <form action="save-announcement.php" method="POST">
            <label>Title</label>
            <input type="text" name="title" placeholder="Enter announcement title" required>
            
            <label>Target Audience</label>
            <select name="audience" id="audienceSelect" onchange="toggleSpecificField()" required>
                <option value="all">All</option>
                <option value="students">All Students</option>
                <option value="faculty">All Faculty</option>
                <option value="alumni">All Alumni</option>
                <option value="specific_user">Specific User</option>
            </select>

            <div id="specificUserField" style="display:none;">
                <label>User ID Number</label>
                <input type="text" name="target_user_id" placeholder="Enter Student/Faculty/Alumni ID">
            </div>

            <label>Status</label>
            <select name="status" required>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
            </select>

            <label>Message</label>
            <textarea name="message" rows="5" placeholder="Write your announcement here..." required></textarea>
            
            <button type="submit" class="filter-btn" style="width:100%; margin-top: 20px; height: 45px;">Post Announcement</button>
        </form>
    </div>
</div>

<!-- EDIT ANNOUNCEMENT MODAL -->
<div id="editAnnouncementModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('editAnnouncementModal').style.display='none'">&times;</span>
        <h2>Edit Announcement</h2>
        <form action="admin-announcement.php" method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="announcement_id" id="edit_id">
            
            <label>Title</label>
            <input type="text" name="title" id="edit_title" required>
            
            <label>Target Audience</label>
            <select name="audience" id="editAudienceSelect" onchange="toggleEditSpecificField()" required>
                <option value="all">All</option>
                <option value="students">All Students</option>
                <option value="faculty">All Faculty</option>
                <option value="alumni">All Alumni</option>
                <option value="specific_user">Specific User</option>
            </select>

            <div id="editSpecificUserField" style="display:none;">
                <label>User ID Number</label>
                <input type="text" name="target_user_id" id="edit_user_id" placeholder="Enter Student/Faculty/Alumni ID">
            </div>

            <label>Status</label>
            <select name="status" id="edit_status" required>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
            </select>

            <label>Message</label>
            <textarea name="message" id="edit_message" rows="5" required></textarea>
            
            <button type="submit" class="filter-btn" style="width:100%; margin-top: 20px; height: 45px; background-color: #f1b22e; color: #000;">Save Changes</button>
        </form>
    </div>
</div>

<!-- DELETE CONFIRMATION MODAL -->
<div id="deleteAnnouncementModal" class="modal">
    <div class="modal-content" style="max-width: 450px;">
        <span class="close" onclick="document.getElementById('deleteAnnouncementModal').style.display='none'">&times;</span>
        <h2>Confirm Deletion</h2>
        <form action="admin-announcement.php" method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="announcement_id" id="delete_id">
            
            <p style="margin: 15px 0;">Are you sure you want to delete the announcement: <strong id="delete_title_text"></strong>?</p>
            <p style="color: red; font-size: 13px; margin-bottom: 20px;"><i class="fa-solid fa-triangle-exclamation"></i> This action cannot be undone.</p>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="reset-btn" style="padding: 10px 20px;" onclick="document.getElementById('deleteAnnouncementModal').style.display='none'">Cancel</button>
                <button type="submit" class="filter-btn" style="background-color: #dc3545; color: white; padding: 10px 20px; width: auto;">Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Handles closing when clicking outside any modal backdrop overlay
    window.onclick = function(event) {
        let createModal = document.getElementById('announcementModal');
        let editModal = document.getElementById('editAnnouncementModal');
        let deleteModal = document.getElementById('deleteAnnouncementModal');
        
        if (event.target == createModal) { createModal.style.display = "none"; }
        if (event.target == editModal) { editModal.style.display = "none"; }
        if (event.target == deleteModal) { deleteModal.style.display = "none"; }
    }

    function toggleSpecificField() {
        const select = document.getElementById('audienceSelect');
        const field = document.getElementById('specificUserField');
        field.style.display = (select.value === 'specific_user') ? 'block' : 'none';
    }

    function toggleEditSpecificField() {
        const select = document.getElementById('editAudienceSelect');
        const field = document.getElementById('editSpecificUserField');
        field.style.display = (select.value === 'specific_user') ? 'block' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Edit Button Click Handlers
        const editButtons = document.querySelectorAll('.edit-btn-table');
        editButtons.forEach(button => {
            button.addEventListener('click', function () {
                document.getElementById('edit_id').value = this.getAttribute('data-id');
                document.getElementById('edit_title').value = this.getAttribute('data-title');
                document.getElementById('edit_message').value = this.getAttribute('data-message');
                document.getElementById('edit_status').value = this.getAttribute('data-status');
                
                const audienceValue = this.getAttribute('data-audience');
                document.getElementById('editAudienceSelect').value = audienceValue;
                
                if(audienceValue === 'specific_user') {
                    document.getElementById('edit_user_id').value = this.getAttribute('data-user-id');
                    document.getElementById('editSpecificUserField').style.display = 'block';
                } else {
                    document.getElementById('edit_user_id').value = '';
                    document.getElementById('editSpecificUserField').style.display = 'none';
                }
                
                document.getElementById('editAnnouncementModal').style.display = 'flex';
            });
        });

        // Delete Button Click Handlers
        const deleteButtons = document.querySelectorAll('.delete-btn');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                document.getElementById('delete_id').value = this.getAttribute('data-id');
                document.getElementById('delete_title_text').textContent = this.getAttribute('data-title');
                document.getElementById('deleteAnnouncementModal').style.display = 'flex';
            });
        });
    });
</script>

</body>
</html>