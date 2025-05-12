<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Nexus</title>
<link href="style.css" rel="stylesheet">
<link
  rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
/>
</head>
<body>
  <div class="container" role="main">
    <!-- Left Sidebar -->
    <nav aria-label="Primary Navigation">
      <button class="close-btn" aria-label="Close menu">
        <i>NEXUS</i>
      </button>
      <ul>
        <li class="home"><i class="fas fa-home" aria-hidden="true"></i> Home</li>
        <li><i class="fas fa-search" aria-hidden="true"></i> Explore</li>
        <li><i class="fas fa-bell" aria-hidden="true"></i> Notifications</li>
        <li><i class="fas fa-envelope" aria-hidden="true"></i> Messages</li>
        <li><i class="fas fa-bookmark" aria-hidden="true"></i> Bookmarks</li>
        <li><i class="fas fa-user" aria-hidden="true"></i> Profile</li>
      </ul>
      <!-- <button class="post-btn" type="button">Post</button> -->
    </nav>
    <!-- Main Content -->
    <main>
      <div class="tabs" role="tablist" aria-label="Content tabs">
        <button class="active" role="tab" aria-selected="true" tabindex="0">For you</button>
        <button role="tab" aria-selected="false" tabindex="-1">Following</button>
      </div>
      <!-- Tweet 1 -->
      <article class="tweet" aria-label="Tweet by JUST MIH™">
        <img
          src="https://storage.googleapis.com/a1aa/image/37126454-0da0-4eb4-cfd0-c6a2ec411163.jpg"
          alt="Profile picture of a user with a dog"
          class="profile-pic"
          width="48"
          height="48"
          loading="lazy"
        />
        <div class="content">
          <header>
            <span class="name">JUST MIH™</span>
            <span class="time">@HUMUORn · 2h</span>
            <button class="more-btn" aria-label="More options"><i class="fas fa-ellipsis-h" aria-hidden="true"></i></button>
          </header>
          <p class="text">
            Me: Bro ni nani ameimba hii wimbo?
            <br />
            Him: Juice wrld.
            <br />
            Me: Si uache aimbe.
          </p>
          <img
            src="https://storage.googleapis.com/a1aa/image/73214d66-2621-4b7b-7019-508c964121a5.jpg"
            alt="View from inside a car showing a dog sitting in the passenger seat looking back"
            class="tweet-image"
            width="600"
            height="300"
            loading="lazy"
          />
          <footer>
            <div><i class="far fa-comment" aria-hidden="true"></i> 57</div>
            <div><i class="fas fa-retweet" aria-hidden="true"></i> 235</div>
            <div><i class="far fa-heart" aria-hidden="true"></i> 776</div>
            <div><i class="fas fa-chart-bar" aria-hidden="true"></i> 9.2K</div>
            <div><i class="far fa-bookmark" aria-hidden="true"></i></div>
            <div><i class="fas fa-upload" aria-hidden="true"></i></div>
          </footer>
        </div>
      </article>
      
    </main>
    <!-- Right Sidebar -->
    <aside aria-label="Right Sidebar">
      <div class="search-box" role="search">
        <input type="search" placeholder="Search" aria-label="Search" />
        <button aria-label="Search button"><i class="fas fa-search" aria-hidden="true"></i></button>
      </div>
      <div class="hosting-list" aria-label="Hosting channels">
        <div class="hosting-item">
          <div class="hosting-left">
            <img src="https://storage.googleapis.com/a1aa/image/230a2c4d-9dbe-40af-93f3-4aea0e10780b.jpg" alt="NTV Kenya logo" width="32" height="32" loading="lazy" />
            <div>
              <p class="hosting-text">NTV Kenya</p>
              <p class="hosting-subtext">is hosting</p>
            </div>
          </div>
          <span class="hosting-right">+3.5K</span>
        </div>
        <div class="hosting-item">
          <div class="hosting-left">
            <img src="https://storage.googleapis.com/a1aa/image/41703d74-c983-466e-e3cd-5102407d779c.jpg" alt="Citizen TV Kenya logo" width="32" height="32" loading="lazy" />
            <div>
              <p class="hosting-text">Citizen TV Live</p>
              <p class="hosting-subtext">is hosting</p>
            </div>
          </div>
          <span class="hosting-right">+1.3K</span>
        </div>
        <div class="hosting-item">
          <div class="hosting-left">
            <img src="https://storage.googleapis.com/a1aa/image/5d8e7075-0f41-4422-6332-863825e25754.jpg" alt="KTN News logo" width="32" height="32" loading="lazy" />
            <div>
              <p class="hosting-text">KTN NEWS</p>
              <p class="hosting-subtext">is hosting</p>
            </div>
          </div>
          <span class="hosting-right">+15</span>
        </div>
      </div>
      <section class="card" aria-label="What's happening">
        <h2>Suggested For You</h2>
        <article class="wh-item">
          <img src="https://storage.googleapis.com/a1aa/image/72de2495-ef4f-4974-4d47-eb61a242e443.jpg" alt="Going Public album cover art" width="48" height="48" loading="lazy" />
          <div class="wh-text">
            <p class="font-semibold">Going Public</p>
            <p class="live">LIVE</p>
          </div>
        </article>
        <div class="trending-list">
          <div>
            <p>Trending in Kenya</p>
            <p class="trend-title">George Natembeya</p>
            <p>3,166 posts</p>
            <button class="more-btn" aria-label="More options for George Natembeya">...</button>
          </div>
          <div>
            <p>Trending in Kenya</p>
            <p class="trend-title">Farouk Kibet</p>
            <p>4,293 posts</p>
            <button class="more-btn" aria-label="More options for Farouk Kibet">...</button>
          </div>
          <div>
            <p>Trending in Kenya</p>
            <p class="trend-title">#BanForeignHate</p>
            <p>1,131 posts</p>
            <button class="more-btn" aria-label="More options for #BanForeignHate">...</button>
          </div>
          <div>
            <p>Trending in Kenya</p>
            <p class="trend-title">HON HASSAN DUALE</p>
            <button class="more-btn" aria-label="More options for HON HASSAN DUALE">...</button>
          </div>
          <button class="more-btn" aria-label="Show more trending topics">Show more</button>
        </div>
      </section>
      <section class="card" aria-label="Who to follow">
        <h2>Who to follow</h2>
        <div class="follow-list">
          <article>
            <div class="follow-left">
              <img src="https://storage.googleapis.com/a1aa/image/02cc31a3-e529-437b-3145-3e30bc8878cf.jpg" alt="TEL profile picture" width="32" height="32" loading="lazy" />
              <div class="user-info">
                <p>TEL</p>
                <p class="handle">@_Tel254</p>
              </div>
            </div>
            <button class="follow-btn" type="button">Follow</button>
          </article>
          <article>
            <div class="follow-left">
              <img src="https://storage.googleapis.com/a1aa/image/19bf423e-8a8b-4caa-3afe-8be181bab7c2.jpg" alt="Rod Sam Molder profile picture" width="32" height="32" loading="lazy" />
              <div class="user-info">
                <p>Rod Sam Molder</p>
                <p class="handle">@GuiverMiguel</p>
              </div>
              <i class="fas fa-check-circle verified" aria-label="Verified account"></i>
            </div>
            <button class="follow-btn" type="button">Follow</button>
          </article>
          <article>
            <div class="follow-left">
              <img src="https://storage.googleapis.com/a1aa/image/6125e55a-c1bc-48e9-b448-6be01e07260d.jpg" alt="Charlene Ruto profile picture" width="32" height="32" loading="lazy" />
              <div class="user-info">
                <p>Charlene Ruto</p>
                <p class="handle">@charlruto</p>
              </div>
              <i class="fas fa-check-circle verified" aria-label="Verified account"></i>
            </div>
            <button class="follow-btn" type="button">Follow</button>
          </article>
        </div>
        <button class="more-btn" aria-label="Show more who to follow">Show more</button>
      </section>
      <footer class="footer" aria-label="Footer">
        <p>Terms of Service</p>
        <span>|</span>
        <p>Privacy Policy</p>
        <span>|</span>
        <p>Cookie Policy</p>
      </footer>
    </aside>
  </div>
</body>
</html>