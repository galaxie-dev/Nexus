

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
  transition: var(--transition);
}

body.dark-mode aside {
  background: var(--glass-dark);
  border-left: 1px solid var(--dark-border);
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

/* Floating "back to top" button */
.back-to-top {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  width: 48px;
  height: 48px;
  background: var(--primary);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 20px rgba(58, 134, 255, 0.3);
  opacity: 0;
  visibility: hidden;
  transition: var(--transition);
  z-index: 1000;
}

.back-to-top.visible {
  opacity: 1;
  visibility: visible;
}

.back-to-top:hover {
  transform: translateY(-4px);
}

/* Responsive */
@media (max-width: 1280px) {
  aside {
    transform: translateX(100%);
    box-shadow: 0 0 0 100vmax rgba(0,0,0,0);
  }
  
  aside.active {
    transform: translateX(0);
    box-shadow: 0 0 0 100vmax rgba(0,0,0,0.3);
  }
}

@media (max-width: 768px) {
  aside {
    width: 280px;
  }
}
</style>

<!-- Premium Right Sidebar -->
<aside aria-label="Right Sidebar">
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
document.addEventListener('DOMContentLoaded', function() {
  // Load suggested news on page load
  loadSuggestedNews();
  
  // Search functionality
  const searchInput = document.getElementById('news-search');
  const searchButton = document.getElementById('search-button');
  
  searchButton.addEventListener('click', function() {
    const query = searchInput.value.trim();
    if (query) {
      searchNews(query);
    }
  });
  
  searchInput.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      const query = searchInput.value.trim();
      if (query) {
        searchNews(query);
      }
    }
  });
});

function loadSuggestedNews() {
  fetch('get_suggested_news.php')
    .then(response => response.json())
    .then(data => {
      const container = document.getElementById('suggested-news');
      container.innerHTML = '';
      
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
        container.appendChild(newsItem);
      });
    })
    .catch(error => console.error('Error loading suggested news:', error));
}

function searchNews(query) {
  fetch(`search_news.php?query=${encodeURIComponent(query)}`)
    .then(response => response.json())
    .then(data => {
      const resultsContainer = document.getElementById('search-results-container');
      const searchResultsSection = document.getElementById('search-results');
      const suggestedNews = document.getElementById('suggested-news');
      
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
      
      // Show search results and hide suggested news
      searchResultsSection.style.display = 'block';
      suggestedNews.style.display = 'none';
    })
    .catch(error => console.error('Error searching news:', error));
}
</script>