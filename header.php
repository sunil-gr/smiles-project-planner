<?php
// header.php
?>
<div class="custom-header">
    <div class="header-logo-title">
        <img src="logo.png" alt="Logo" class="header-logo">
        <span class="header-title">Smiles Project Planner</span>
    </div>
    <?php if (isset($_SESSION['user'])): ?>
    <form method="post" action="logout.php" class="logout-form">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
    <?php endif; ?>
</div>