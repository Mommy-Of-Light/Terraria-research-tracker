<div class="row justify-content-center">
    <div class="col-md-5">
        <h2 class="mb-4 text-center">Register</h2>

        <form method="POST" action="/register" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-success w-100" type="submit">Create Account</button>
            <div class="text-center mt-3">
                <a href="/login">Already have an account? Login</a>
            </div>
        </form>
    </div>
</div>
