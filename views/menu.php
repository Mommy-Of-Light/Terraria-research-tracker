<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">Terraria Wiki</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-toggle">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbar-toggle">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">

                <?php if (!empty($_SESSION['user'])): ?>
                    <li class="nav-item">
                    <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="/chats">Chats</a></li>
                    <li class="nav-item"><a class="nav-link" href="/profile">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="/user/files">Files</a></li>
                    <li class="nav-item"><a class="nav-link" href="/logout">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/login">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="/register">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>