<?php
require_once 'includes/db.php';

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] == 'get_suggested_news') {
        // Get suggested news (most liked + commented)
        $query = "SELECT n.id, n.title, n.content, n.category, n.likes, 
                 COUNT(c.id) as comments_count
                 FROM news_card n
                 LEFT JOIN comments c ON n.id = c.news_id
                 GROUP BY n.id
                 ORDER BY (n.likes + COUNT(c.id)) DESC, n.created_at DESC
                 LIMIT 3";
        $result = $conn->query($query);
        
        $news = [];
        while ($row = $result->fetch_assoc()) {
            $news[] = $row;
        }
        echo json_encode($news);
        exit;
    }
    elseif ($_GET['action'] == 'search_news' && isset($_GET['query'])) {
        $query = $_GET['query'];
        $searchQuery = "%$query%";
        
        $stmt = $conn->prepare("SELECT id, title, content, category, likes 
                               FROM news_card 
                               WHERE title LIKE ? OR content LIKE ? 
                               ORDER BY created_at DESC");
        $stmt->bind_param("ss", $searchQuery, $searchQuery);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $news = [];
        while ($row = $result->fetch_assoc()) {
            $news[] = $row;
        }
        echo json_encode($news);
        $stmt->close();
        exit;
    }
}
?>

<style>
/* Premium Right Sidebar */
aside {
  width: 320px;
  padding: 1.5rem;
  background: var(--glass);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-left: 1px solid var(--border);
  position: fixed;
  right: 0;
  top: 0;
  height: 100vh;
  overflow-y: auto;
  z-index: 900;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  transform: translateX(100%);
  box-shadow: 0 0 0 100vmax rgba(0,0,0,0);
}

aside.active {
  transform: translateX(0);
  box-shadow: 0 0 0 100vmax rgba(0,0,0,0.5);
}

body.dark-mode aside {
  background: var(--glass-dark);
  border-left: 1px solid var(--dark-border);
}

/* Close button */
.close-sidebar {
  display: none;
  position: fixed;
  top: 1rem;
  left: 1rem;
  width: 40px;
  height: 40px;
  background: var(--primary);
  color: white;
  border-radius: 50%;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 901;
  box-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.close-sidebar i {
  font-size: 1.2rem;
}

.search-box {
  display: flex;
  margin-bottom: 2rem;
  position: relative;
  box-shadow: var(--shadow);
  border-radius: var(--radius-lg);
  overflow: hidden;
}

.search-box input {
  flex: 1;
  padding: 0.85rem 1.25rem;
  border: none;
  background: var(--card-bg);
  color: var(--text);
  font-size: 0.95rem;
  transition: var(--transition);
}

body.dark-mode .search-box input {
  background: var(--dark-card-bg);
  color: var(--dark-text);
}

.search-box input:focus {
  outline: none;
  box-shadow: 0 0 0 2px var(--primary);
}

.search-box button {
  padding: 0 1.5rem;
  background: var(--primary);
  border: none;
  color: white;
  cursor: pointer;
  transition: var(--transition);
}

.search-box button:hover {
  background: #2a75e6;
}

.search-box button i {
  font-size: 1rem;
}

.card {
  background: var(--card-bg);
  padding: 1.5rem;
  border-radius: var(--radius-lg);
  margin-bottom: 1.5rem;
  box-shadow: var(--shadow);
  transition: var(--transition);
}

body.dark-mode .card {
  background: var(--dark-card-bg);
  box-shadow: var(--shadow-dark);
}

.card h2 {
  font-size: 1.25rem;
  margin-bottom: 1.25rem;
  color: var(--text);
  font-weight: 700;
  display: flex;
  align-items: center;
}

body.dark-mode .card h2 {
  color: var(--dark-text);
}

.card h2 i {
  margin-right: 0.75rem;
  color: var(--primary);
}

.trending-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.trending-item {
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border);
  transition: var(--transition);
  cursor: pointer;
}

body.dark-mode .trending-item {
  border-bottom: 1px solid var(--dark-border);
}

.trending-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.trending-item:hover {
  transform: translateX(4px);
}

.trending-item p {
  font-size: 0.85rem;
  color: var(--muted);
  margin-bottom: 0.5rem;
  line-height: 1.5;
}

.trend-title {
  font-weight: 700;
  color: var(--text);
  margin-bottom: 0.5rem;
  font-size: 1rem;
  transition: var(--transition);
}

body.dark-mode .trend-title {
  color: var(--dark-text);
}

.trending-item:hover .trend-title {
  color: var(--primary);
}

/* Mobile styles */
@media (max-width: 768px) {
  aside {
    width: 100%;
    padding: 1rem;
    border-left: none;
  }
  
  .close-sidebar {
    display: flex;
  }
  
  .search-box {
    margin-top: 3rem;
    margin-bottom: 1.5rem;
  }
  
  .card {
    padding: 1rem;
    margin-bottom: 1rem;
  }
  
  .card h2 {
    font-size: 1.1rem;
    margin-bottom: 1rem;
  }
  
  .trending-list {
    gap: 1rem;
  }
  
  .trending-item p {
    font-size: 0.8rem;
  }
  
  .trend-title {
    font-size: 0.9rem;
  }
}

/* Desktop styles */
@media (min-width: 1281px) {
  aside {
    transform: translateX(0);
    box-shadow: none;
  }
  
  .close-sidebar {
    display: none !important;
  }
}
</style>

<!-- Premium Right Sidebar -->
<aside aria-label="Right Sidebar">
  <div class="close-sidebar" id="close-sidebar">
    <i class="fas fa-times"></i>
  </div>
  
  <div class="search-box" role="search">
    <input type="search" id="news-search" placeholder="Search Nexus..." aria-label="Search" />
    <button id="search-button" aria-label="Search button">
      <i class="fas fa-search"></i>
    </button>
  </div>
  
  <section class="card" aria-label="What's happening">
    <h2>
      <i class="fas fa-bolt"></i>
      <span>Suggested For You</span>
    </h2>
    <div class="trending-list" id="suggested-news">
      <!-- Content will be loaded dynamically -->
    </div>
  </section>
  
  <section class="card" aria-label="Search results" id="search-results" style="display: none;">
    <h2>
      <i class="fas fa-search"></i>
      <span>Search Results</span>
    </h2>
    <div class="trending-list" id="search-results-container">
      <!-- Search results will appear here -->
    </div>
  </section>
</aside>

<script>
// Global function to open sidebar
window.openSearchSidebar = function() {
  const sidebar = document.querySelector('aside');
  if (sidebar) {
    sidebar.classList.add('active');
    document.body.style.overflow = 'hidden';
  }
};

document.addEventListener('DOMContentLoaded', function() {
  // Toggle sidebar
  const closeSidebarBtn = document.getElementById('close-sidebar');
  const sidebar = document.querySelector('aside');
  
  // Function to close sidebar
  function closeSidebar() {
    sidebar.classList.remove('active');
    document.body.style.overflow = '';
  }
  
  // Close button event
  if (closeSidebarBtn) {
    closeSidebarBtn.addEventListener('click', closeSidebar);
  }
  
  // Close when clicking outside
  sidebar.addEventListener('click', function(e) {
    if (e.target === sidebar) {
      closeSidebar();
    }
  });
  
  // Load suggested news on page load
  loadSuggestedNews();
  
  // Search functionality
  const searchInput = document.getElementById('news-search');
  const searchButton = document.getElementById('search-button');
  const searchResultsSection = document.getElementById('search-results');
  
  searchButton.addEventListener('click', function() {
    const query = searchInput.value.trim();
    if (query) {
      searchNews(query);
    } else {
      loadSuggestedNews();
      searchResultsSection.style.display = 'none';
    }
  });
  
  searchInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      const query = searchInput.value.trim();
      if (query) {
        searchNews(query);
      } else {
        loadSuggestedNews();
        searchResultsSection.style.display = 'none';
      }
    }
  });
});

function loadSuggestedNews() {
  fetch('right-side-bar.php?action=get_suggested_news')
    .then(response => response.json())
    .then(data => {
      const container = document.getElementById('suggested-news');
      container.innerHTML = '';
      
      if (data.length === 0) {
        container.innerHTML = '<p>No suggested news available.</p>';
        return;
      }
      
      data.forEach(news => {
        const newsItem = document.createElement('div');
        newsItem.className = 'trending-item';
        newsItem.innerHTML = `
          <p>${news.category} • ${news.likes} likes • ${news.comments_count || 0} comments</p>
          <p class="trend-title">${news.title}</p>
          <p>${news.content.substring(0, 100)}...</p>
        `;
        newsItem.addEventListener('click', function() {
          window.location.href = `view-article.php?id=${news.id}`;
        });
        container.appendChild(newsItem);
      });
    })
    .catch(error => {
      console.error('Error loading suggested news:', error);
      document.getElementById('suggested-news').innerHTML = '<p>Error loading news. Please try again.</p>';
    });
}

function searchNews(query) {
  fetch(`right-side-bar.php?action=search_news&query=${encodeURIComponent(query)}`)
    .then(response => response.json())
    .then(data => {
      const container = document.getElementById('suggested-news');
      const resultsContainer = document.getElementById('search-results-container');
      const searchResultsSection = document.getElementById('search-results');
      
      container.style.display = 'none';
      searchResultsSection.style.display = 'block';
      resultsContainer.innerHTML = '';
      
      if (data.length === 0) {
        resultsContainer.innerHTML = '<p>No results found for your search.</p>';
      } else {
        data.forEach(news => {
          const newsItem = document.createElement('div');
          newsItem.className = 'trending-item';
          newsItem.innerHTML = `
            <p>${news.category} • ${news.likes} likes</p>
            <p class="trend-title">${news.title}</p>
            <p>${news.content.substring(0, 100)}...</p>
          `;
          newsItem.addEventListener('click', function() {
            window.location.href = `view-article.php?id=${news.id}`;
          });
          resultsContainer.appendChild(newsItem);
        });
      }
    })
    .catch(error => {
      console.error('Error searching news:', error);
      const resultsContainer = document.getElementById('search-results-container');
      resultsContainer.innerHTML = '<p>Error searching news. Please try again.</p>';
    });
}
</script>