<?php
// Campus Buddy Menu Component
$current_page = basename($_SERVER['PHP_SELF']);
$page_name = basename(dirname($_SERVER['PHP_SELF']));

// Determine active page based on current directory
$is_active = function($page) use ($page_name) {
    return $page_name === $page;
};

// Include topbar
include 'topbar.php';
?>

<!-- ================= MOBILE MENU ================= -->
<nav class="mobile-menu">
  <a href="../Dashboard1/" class="<?php echo $is_active('Dashboard1') ? 'active' : ''; ?>">
    <img src="../includes/menuicons/home.png" alt="Home" width="20" height="20" style="vertical-align: middle;">
    <span>Home</span>
  </a>
  <a href="../routine/" class="<?php echo $is_active('routine') ? 'active' : ''; ?>">
    <img src="../includes/menuicons/routine.png" alt="Routine" width="20" height="20" style="vertical-align: middle;">
    <span>Routine</span>
  </a>
  <a href="../Class/" class="<?php echo $is_active('Class') ? 'active' : ''; ?>">
    <img src="../includes/menuicons/Classtask.png" alt="ClassTask" width="20" height="20" style="vertical-align: middle;">
    <span>ClassTask</span>
  </a>
  <a href="../clubs/" class="<?php echo $is_active('clubs') ? 'active' : ''; ?>">
    <img src="../includes/menuicons/clubs.png" alt="Clubs" width="20" height="20" style="vertical-align: middle;">
    <span>Clubs</span>
  </a>
  <a href="../pdf & Notes/" class="<?php echo $is_active('pdf & Notes') ? 'active' : ''; ?>">
    <img src="../includes/menuicons/pdf&Notes.png" alt="Notes & Pdf" width="20" height="20" style="vertical-align: middle;">
    <span>Notes & Pdf</span>
  </a>
  <a href="../comunity/" class="<?php echo $is_active('comunity') ? 'active' : ''; ?>">
    <img src="../includes/menuicons/community.png" alt="Community" width="20" height="20" style="vertical-align: middle;">
    <span>Community</span>
  </a>
  <a href="../Alumni/" class="<?php echo $is_active('Alumni') ? 'active' : ''; ?>">
    <img src="../includes/menuicons/alumni.png" alt="Alumni" width="20" height="20" style="vertical-align: middle;">
    <span>Alumni</span>
  </a>
  <a href="../QuestionBank/" class="<?php echo $is_active('QuestionBank') ? 'active' : ''; ?>">
    <img src="../includes/menuicons/questionBank.png" alt="Question Bank" width="20" height="20" style="vertical-align: middle;">
    <span>Q Bank</span>
  </a>
</nav>

<!-- ================= DESKTOP SIDEBAR ================= -->
<nav class="sidebar">
  <?php if ($page_name === 'Dashboard1'): ?>
    <div class="buddy-box">
      <img src="../includes/menuicons/Buddy.png" alt="Buddy">
      <button>Chat With Buddy</button>
    </div>
  <?php endif; ?>
  
  <a href="../Dashboard1/" class="menu <?php echo $is_active('Dashboard1') ? 'active' : ''; ?>">
    <img src="../includes/menuicons/home.png" alt="Home" width="20" height="20" style="vertical-align: middle;">
    <p>Home</p>
  </a>
  <a href="../routine/" class="menu <?php echo $is_active('routine') ? 'active' : ''; ?>">
    <img src="../includes/menuicons/routine.png" alt="Routine" width="20" height="20" style="vertical-align: middle;">
    <p>Routine</p>
  </a>
  <a href="../Class/" class="menu <?php echo $is_active('Class') ? 'active' : ''; ?>">
    <img src="../includes/menuicons/Classtask.png" alt="ClassTask" width="20" height="20" style="vertical-align: middle;">
    <p>ClassTask</p>
  </a>
  <a href="../clubs/" class="menu <?php echo $is_active('clubs') ? 'active' : ''; ?>">
    <img src="../includes/menuicons/clubs.png" alt="Clubs" width="20" height="20" style="vertical-align: middle;">
    <p>Clubs</p>
  </a>
  <a href="../pdf & Notes/" class="menu <?php echo $is_active('pdf & Notes') ? 'active' : ''; ?>">
    <img src="../includes/menuicons/pdf&Notes.png" alt="Notes & Pdf" width="20" height="20" style="vertical-align: middle;">
    <p>Notes & Pdf</p>
  </a>
  <a href="../comunity/" class="menu <?php echo $is_active('comunity') ? 'active' : ''; ?>">
    <img src="../includes/menuicons/community.png" alt="Community" width="20" height="20" style="vertical-align: middle;">
    <p>Community</p>
  </a>
  <a href="../Alumni/" class="menu <?php echo $is_active('Alumni') ? 'active' : ''; ?>">
    <img src="../includes/menuicons/alumni.png" alt="Alumni" width="20" height="20" style="vertical-align: middle;">
    <p>Alumni</p>
  </a>
  <a href="../QuestionBank/" class="menu <?php echo $is_active('QuestionBank') ? 'active' : ''; ?>">
    <img src="../includes/menuicons/questionBank.png" alt="Question Bank" width="20" height="20" style="vertical-align: middle;">
    <p>Q Bank</p>
  </a>
</nav>

<!-- ================= SOCIAL MEDIA FOOTER ================= -->
<div class="social-footer">
  <a href="https://www.facebook.com" target="_blank" class="social-icon" title="Facebook">
    <img src="../includes/Topbaricons/facebook.png" alt="Facebook" width="20" height="20">
  </a>
  <a href="https://twitter.com" target="_blank" class="social-icon" title="Twitter">
    <img src="../includes/Topbaricons/twitter.png" alt="Twitter" width="20" height="20">
  </a>
  <a href="mailto:contact@campusbuddy.com" class="social-icon" title="Email">
    <img src="../includes/Topbaricons/mail.png" alt="Email" width="20" height="20">
  </a>
</div>
