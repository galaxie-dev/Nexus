document.addEventListener('DOMContentLoaded', () => {
    // Behavioral tracking
    document.querySelectorAll('.tweet').forEach(tweet => {
        tweet.addEventListener('click', (e) => {
            if (e.target.closest('.like-btn, .comment-btn, .share-btn, .comment-form')) return;
            const newsId = tweet.dataset.newsId;
            fetch('track.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `news_id=${newsId}`
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) console.error('Tracking failed:', data.message);
            })
            .catch(error => console.error('Tracking error:', error));
        });
    });

    // Like buttons
    document.querySelectorAll('.like-btn').forEach(button => {
        button.addEventListener('click', () => {
            if (button.disabled) return;
            const newsId = button.dataset.newsId;
            button.classList.add('animate-like');
            setTimeout(() => button.classList.remove('animate-like'), 300);
            fetch('like.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `news_id=${newsId}&csrf_token=${encodeURIComponent(csrfToken)}`
            })
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const likesSpan = document.getElementById(`likes${newsId}`);
                    likesSpan.textContent = data.likes;
                    button.classList.toggle('liked', data.action === 'liked');
                    button.querySelector('i').className = data.action === 'liked' ? 'fas fa-heart' : 'far fa-heart';
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Like error:', error);
                alert('Failed to process like. Check console for details.');
            });
        });
    });

    // Comment buttons (toggle form)
    document.querySelectorAll('.comment-btn').forEach(button => {
        button.addEventListener('click', () => {
            if (button.disabled) return;
            const newsId = button.dataset.newsId;
            const form = document.querySelector(`.comment-form[data-news-id="${newsId}"]`);
            form.style.display = form.style.display === 'none' ? 'flex' : 'none';
            if (form.style.display === 'flex') form.querySelector('textarea').focus();
        });
    });

    // Comment forms
    document.querySelectorAll('.comment-form').forEach(form => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const newsId = form.dataset.newsId;
            const content = form.querySelector('textarea').value.trim();
            if (!content) return;

            fetch('comment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `news_id=${newsId}&content=${encodeURIComponent(content)}&csrf_token=${encodeURIComponent(csrfToken)}`
            })
            .then(response => {
                if (!response.ok) throw new Error('Network error');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    form.querySelector('textarea').value = '';
                    form.style.display = 'none';
                    // SSE will append comment
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Comment error:', error);
                alert('Failed to post comment. Check console for details.');
            });
        });
    });

    // Share buttons
    document.querySelectorAll('.share-btn').forEach(button => {
        button.addEventListener('click', () => {
            const newsId = button.dataset.newsId;
            const platform = button.dataset.platform;
            const title = document.querySelector(`.tweet[data-news-id="${newsId}"] .tweet-title`).textContent;
            const url = window.location.origin + '/index_after.php';

            let shareUrl = '';
            if (platform === 'whatsapp') {
                shareUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(title + ' ' + url)}`;
            } else if (platform === 'twitter') {
                shareUrl = `https://twitter.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}`;
            }

            fetch('share.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `news_id=${newsId}&platform=${platform}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.open(shareUrl, '_blank');
                } else {
                    alert(data.message);
                }
            })
            .catch(error => console.error('Share error:', error));
        });
    });

    // SSE for real-time updates
    const source = new EventSource('includes/sse.php');
    source.onmessage = (event) => {
        const data = JSON.parse(event.data);
        if (data.type === 'like') {
            const likesSpan = document.getElementById(`likes${data.news_id}`);
            if (likesSpan) likesSpan.textContent = data.likes;
        } else if (data.type === 'comment') {
            const commentCountSpan = document.getElementById(`comments${data.news_id}`);
            if (commentCountSpan) commentCountSpan.textContent = data.comment_count;

            const commentsSection = document.querySelector(`.comments-section[data-news-id="${data.news_id}"]`);
            if (commentsSection) {
                const commentDiv = document.createElement('div');
                commentDiv.className = 'comment';
                commentDiv.dataset.commentId = data.comment_id;
                commentDiv.innerHTML = `
                    <span class="comment-username">@${data.username}</span>
                    <span class="comment-time">${data.created_at}</span>
                    <p>${data.content}</p>
                `;
                commentsSection.prepend(commentDiv);
            }
        } else if (data.type === 'error') {
            console.error('SSE error:', data.message);
        }
    };
    source.onerror = () => {
        console.error('SSE connection error, reconnecting...');
        setTimeout(() => {
            source.close();
            window.location.reload();
        }, 5000);
    };
});