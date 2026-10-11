<header>
    <nav class="site-navbar navbar navbar-expand" aria-label="Main navigation">
        <div class="container-fluid px-0">
            <a class="site-brand" href="Landingpage.php"><span class="brand-mark">RR</span> Filipino Meme's</a>
            <div class="nav-links navbar-nav flex-row">
                <a class="nav-link" href="#home">Home</a>
                <a class="nav-link" href="#about">About</a>
                <?php if (!empty($_SESSION['user_email'])): ?>
                    <span class="nav-link">Signed in as <?= htmlspecialchars($_SESSION['user_email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                    <a class="nav-link" href="login.php?logout=1">Log out</a>
                <?php else: ?>
                    <a class="nav-link" href="login.php">Log in</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>
