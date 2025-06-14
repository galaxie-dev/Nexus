// === Dark Mode Toggle ===
const darkBtn = document.createElement('button');
darkBtn.className = 'dark-toggle-btn';
darkBtn.textContent = '🌓';
document.body.appendChild(darkBtn);

darkBtn.addEventListener('click', () => {
  document.body.classList.toggle('dark-mode');
  const enabled = document.body.classList.contains('dark-mode');
  localStorage.setItem('darkMode', enabled);
  darkBtn.textContent = enabled ? '☀️' : '🌓';
});

// Auto-load dark mode if previously enabled
if (localStorage.getItem('darkMode') === 'true') {
  document.body.classList.add('dark-mode');
  darkBtn.textContent = '☀️';
}

// === Scroll Animation ===
function animateOnScroll() {
  const elements = document.querySelectorAll('.tweet');
  const trigger = window.innerHeight * 0.85;

  elements.forEach(el => {
    const top = el.getBoundingClientRect().top;
    if (top < trigger) {
      el.classList.add('visible');
    }
  });
}

window.addEventListener('scroll', animateOnScroll);
window.addEventListener('load', animateOnScroll);
