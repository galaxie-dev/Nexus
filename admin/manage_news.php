<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireAdmin();

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch news with pagination
$page = isset($_GET['page']) ? max(1, filter_var($_GET['page'], FILTER_VALIDATE_INT)) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;
$sort = isset($_GET['sort']) && in_array($_GET['sort'], ['created_at', 'likes']) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) && in_array($_GET['order'], ['asc', 'desc']) ? $_GET['order'] : 'desc';

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM news_card");
    $stmt->execute();
    $total_news = $stmt->fetchColumn();
    $total_pages = ceil($total_news / $per_page);

    $stmt = $pdo->prepare("SELECT id, title, category, image_path, likes, created_at 
                          FROM news_card 
                          ORDER BY $sort $order 
                          LIMIT ? OFFSET ?");
    $stmt->execute([$per_page, $offset]);
    $news_items = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to load news";
    file_put_contents(__DIR__ . '/../logs/nexus.log', date('Y-m-d H:i:s') . " - Manage news failed: " . $e->getMessage() . "\n", FILE_APPEND);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Nexus - Manage News</title>
    <link href="../style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <script src="https://cdn.ckeditor.com/ckeditor5/34.0.0/classic/ckeditor.js"></script>
</head>
<body>
    <div class="container" role="main">
        <!-- Fixed Sidebar -->
        <nav class="fixed-sidebar" aria-label="Primary Navigation">
            <button class="close-btn" aria-label="Close menu"><i>NEXUS</i></button>
            <ul>
                <li class="home"><a href="../index_after.php"><i class="fas fa-home" aria-hidden="true"></i> Home</a></li>
                <li><a href="index.php"><i class="fas fa-tachometer-alt" aria-hidden="true"></i> Dashboard</a></li>
                <li><a href="upload_news.php"><i class="fas fa-plus" aria-hidden="true"></i> Upload News</a></li>
                <li><a href="manage_news.php"><i class="fas fa-list" aria-hidden="true"></i> Manage News</a></li>
                <li><a href="../logout.php" class="post-btn" aria-label="Log out">Log Out</a></li>
            </ul>
        </nav>
        <!-- Main Content -->
        <main class="main-content">
            <h1>Manage News</h1>
            <?php if (isset($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php elseif (empty($news_items)): ?>
                <p>No news items available.</p>
            <?php else: ?>
                <table class="news-table">
                    <thead>
                        <tr>
                            <th><a href="?sort=title&order=<?php echo $sort === 'title' && $order === 'asc' ? 'desc' : 'asc'; ?>">Title</a></th>
                            <th><a href="?sort=category&order=<?php echo $sort === 'category' && $order === 'asc' ? 'desc' : 'asc'; ?>">Category</a></th>
                            <th><a href="?sort=created_at&order=<?php echo $sort === 'created_at' && $order === 'asc' ? 'desc' : 'asc'; ?>">Created At</a></th>
                            <th><a href="?sort=likes&order=<?php echo $sort === 'likes' && $order === 'asc' ? 'desc' : 'asc'; ?>">Likes</a></th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($news_items as $news): ?>
                            <tr data-news-id="<?php echo $news['id']; ?>">
                                <td data-label="Title"><?php echo htmlspecialchars($news['title']); ?></td>
                                <td data-label="Category"><?php echo htmlspecialchars(ucfirst($news['category'])); ?></td>
                                <td data-label="Created At"><?php echo date('M j, Y', strtotime($news['created_at'])); ?></td>
                                <td data-label="Likes"><?php echo $news['likes']; ?></td>
                                <td data-label="Actions">
                                    <button class="edit-btn" data-news-id="<?php echo $news['id']; ?>"><i class="fas fa-edit"></i> Edit</button>
                                    <button class="delete-btn" data-news-id="<?php echo $news['id']; ?>"><i class="fas fa-trash"></i> Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>">Previous</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>" class="<?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&sort=<?php echo $sort; ?>&order=<?php echo $order; ?>">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <!-- Edit Modal -->
            <div class="modal" id="edit-modal" style="display: none;">
                <div class="modal-content">
                    <h2>Edit News</h2>
                    <form id="edit-news-form" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="news_id" id="edit-news-id">
                        <div class="form-group">
                            <label for="edit-title">Title <span class="required">*</span></label>
                            <input type="text" id="edit-title" name="title" maxlength="100" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-content">Content <span class="required">*</span></label>
                            <textarea id="edit-content" name="content" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="edit-category">Category <span class="required">*</span></label>
                            <select id="edit-category" name="category" required>
                                <option value="technology">Technology</option>
                                <option value="sports">Sports</option>
                                <option value="politics">Politics</option>
                                <option value="entertainment">Entertainment</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit-image">Image (Optional, JPEG/PNG, <5MB)></label>
                            <input type="file" id="edit-image" name="image" accept="image/jpeg,image/png">
                            <p>Current Image: <span id="current-image"></span></p>
                        </div>
                        <button type="submit" class="post-btn">Save Changes</button>
                        <button type="button" class="cancel-btn">Cancel</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script>
        let editor;
        // Initialize CKEditor for edit modal
        ClassicEditor
            .create(document.querySelector('#edit-content'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote'],
                wordCount: {
                    displayWords: true,
                    displayCharacters: true,
                    maxCharacters: 1000
                }
            })
            .then(newEditor => {
                editor = newEditor;
            })
            .catch(error => console.error('CKEditor error:', error));

        // Edit button
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', () => {
                const newsId = button.dataset.newsId;
                fetch(`process_news.php?action=get&news_id=${newsId}`, {
                    headers: { 'Content-Type': 'application/json' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('edit-news-id').value = newsId;
                        document.getElementById('edit-title').value = data.news.title;
                        editor.setData(data.news.content);
                        document.getElementById('edit-category').value = data.news.category;
                        document.getElementById('current-image').textContent = data.news.image_path || 'None';
                        document.getElementById('edit-modal').style.display = 'flex';
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Fetch news error:', error);
                    alert('Failed to load news details.');
                });
            });
        });

        // Delete button
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', () => {
                if (!confirm('Are you sure you want to delete this news item?')) return;
                const newsId = button.dataset.newsId;
                fetch('process_news.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=delete&news_id=${newsId}&csrf_token=${encodeURIComponent('<?php echo $_SESSION['csrf_token']; ?>')}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector(`tr[data-news-id="${newsId}"]`).remove();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    alert('Failed to delete news.');
                });
            });
        });

        // Edit form submission
        document.getElementById('edit-news-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('action', 'edit');
            fetch('process_news.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('edit-modal').style.display = 'none';
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Edit error:', error);
                alert('Failed to update news.');
            });
        });

        // Cancel edit
        document.querySelector('.cancel-btn').addEventListener('click', () => {
            document.getElementById('edit-modal').style.display = 'none';
        });

        // Client-side validation for edit form
        document.getElementById('edit-news-form').addEventListener('submit', (e) => {
            const title = document.getElementById('edit-title').value.trim();
            const image = document.getElementById('edit-image').files[0];

            if (title.length > 100) {
                e.preventDefault();
                alert('Title must be 100 characters or less.');
                return;
            }

            if (image) {
                const validTypes = ['image/jpeg', 'image/png'];
                if (!validTypes.includes(image.type)) {
                    e.preventDefault();
                    alert('Image must be JPEG or PNG.');
                    return;
                }
                if (image.size > 5 * 1024 * 1024) {
                    e.preventDefault();
                    alert('Image must be less than 5MB.');
                    return;
                }
            }
        });
    </script>
</body>
</html>