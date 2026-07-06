<?php 
include("../config/db.php");
include("../config/auth.php");

// Placeholder for search logic
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$audience_filter = isset($_GET['audience']) ? trim($_GET['audience']) : '';
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
                $query = "SELECT * FROM announcements ORDER BY created_at DESC";
                $result = $conn->query($query);
                
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
                                    <button class="edit-btn-table"><i class="fa-solid fa-edit"></i></button>
                                    <button class="delete-btn"><i class="fa-solid fa-trash"></i></button>
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
                <?php endif; ?>
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

<script>
    window.onclick = function(event) {
        let modal = document.getElementById('announcementModal');
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    function toggleSpecificField() {
        const select = document.getElementById('audienceSelect');
        const field = document.getElementById('specificUserField');
        field.style.display = (select.value === 'specific_user') ? 'block' : 'none';
    }
</script>

</body>
</html>