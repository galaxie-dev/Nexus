<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

requireAdmin();

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Nexus - Upload News</title>
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
                <li><a href="../includes/logout.php" class="post-btn" aria-label="Log out">Log Out</a></li>
            </ul>
        </nav>
        <!-- Main Content -->
        <main class="main-content">
            <h1>Upload News</h1>
            <form id="upload-news-form" action="process_news.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label for="title">Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" maxlength="100" required>
                </div>
                <div class="form-group">
                    <label for="content">Content <span class="required">*</span></label>
                    <textarea id="content" name="content" required></textarea>
                </div>
                <div class="form-group">
                    <label for="category">Category <span class="required">*</span></label>
                    <select id="category" name="category" onchange="toggleCustomCategory(this)" required>
                        <option value="">Select Category</option>
                        <option value="technology">Technology</option>
                        <option value="sports">Sports</option>
                        <option value="politics">Politics</option>
                        <option value="entertainment">Entertainment</option>
                        <option value="business">Business</option>
                        <option value="health">Health</option>
                        <option value="science">Science</option>
                        <option value="world">World</option>
                        <option value="education">Education</option>
                        <option value="travel">Travel</option>
                        <option value="environment">Environment</option>
                        <option value="finance">Finance</option>
                        <option value="fashion">Fashion</option>
                        <option value="lifestyle">Lifestyle</option>
                        <option value="food">Food</option>
                        <option value="automotive">Automotive</option>
                        <option value="culture">Culture</option>
                        <option value="crime">Crime</option>
                        <option value="weather">Weather</option>
                        <option value="opinion">Opinion</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group" id="custom-category-input" style="display: none;">
                    <label for="custom_category">Custom Category <span class="required">*</span></label>
                    <input type="text" id="custom_category" name="custom_category" maxlength="50">
                </div>
                <div class="form-group">
                    <label for="image">Image (Optional, JPEG/PNG, <5MB)</label>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png">
                </div>
                <button type="submit" class="post-btn">Upload News</button>
            </form>
        </main>
    </div>
    <script>
        // Toggle custom category input
        function toggleCustomCategory(select) {
            const customInput = document.getElementById('custom-category-input');
            customInput.style.display = select.value === 'other' ? 'block' : 'none';
            document.getElementById('custom_category').required = select.value === 'other';
        }

        // Initialize CKEditor
        ClassicEditor
            .create(document.querySelector('#content'), {
                toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote'],
                wordCount: {
                    displayWords: true,
                    displayCharacters: true,
                    maxCharacters: 1000
                }
            })
            .catch(error => console.error('CKEditor error:', error));

        // Client-side validation
        document.getElementById('upload-news-form').addEventListener('submit', (e) => {
            const title = document.getElementById('title').value.trim();
            const category = document.getElementById('category').value;
            const customCategory = document.getElementById('custom_category').value.trim();
            const image = document.getElementById('image').files[0];

            if (title.length > 100) {
                e.preventDefault();
                alert('Title must be 100 characters or less.');
                return;
            }

            if (category === 'other' && !customCategory) {
                e.preventDefault();
                alert('Custom category is required when "Other" is selected.');
                return;
            }

            if (customCategory.length > 50) {
                e.preventDefault();
                alert('Custom category must be 50 characters or less.');
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