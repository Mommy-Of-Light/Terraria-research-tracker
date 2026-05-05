<div class="row justify-content-center">
    <div class="col-md-5">
        <h2 class="mb-4 text-center">Login</h2>

        <?php if (!empty($_GET['error'])): ?>
            <div class="alert alert-danger">Invalid Username or password</div>
        <?php endif; ?>

        <form method="POST" action="/login" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100" type="submit">Login</button>
            <div class="text-center mt-3">
                <a href="/register">Create an account</a>
            </div>
        </form>
    </div>
</div>
