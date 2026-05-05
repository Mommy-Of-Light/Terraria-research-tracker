<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <h1>Profile: <?= htmlspecialchars($user->userName) ?></h1>

            <div class="mb-4">
                <img src="<?= $user->profilePic ?? 'https://ui-avatars.com/api/?name='.$user->userName ?>"
                     alt="Profile Picture" class="img-fluid rounded-circle" style="max-width: 200px;min-width: 200px;">
            </div>

            <form method="post" enctype="multipart/form-data" action="/profile/pfp/add">
                <div class="mb-3">
                    <label class="form-label">Choose image source</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="pic_source" value="url" id="source_url" checked>
                            <label class="form-check-label" for="source_url">URL</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="pic_source" value="file" id="source_file">
                            <label class="form-check-label" for="source_file">Upload File</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3" id="url_input">
                    <label for="profile_pic_url" class="form-label">Profile Picture URL</label>
                    <input type="url" class="form-control" name="profile_pic_url" id="profile_pic_url"
                           placeholder="https://example.com/pic.jpg">
                </div>

                <div class="mb-3 d-none" id="file_input">
                    <label for="profile_pic_file" class="form-label">Upload a File</label>
                    <input type="file" class="form-control" name="profile_pic_file" id="profile_pic_file" accept="image/*">
                </div>

                <button type="submit" class="btn btn-primary">Update Picture</button>
            </form>
            <form action="/profile/pfp/delete" method="post">
                <?php if (!empty($user->profilePic) && !str_starts_with($user->profilePic, 'https://ui-avatars.com/api/?name=')): ?>
                    <button type="submit" name="delete_image" value="1" class="btn btn-warning mt-2">Delete Image</button>
                <?php endif; ?>
            </form>

            <div class="mt-4">
                <form method="post" action="/profile/delete"
                      onsubmit="return confirm('Are you sure you want to delete your account? This cannot be undone.')">
                    <button type="submit" class="btn btn-danger">Delete Account</button>
                </form>
            </div>

            <br><br><br>
            <?php if (!empty($errorMsg)): ?>
                <div class="alert alert-danger"><?= $errorMsg ?></div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sourceRadios = document.querySelectorAll('input[name="pic_source"]');
    const urlInput = document.getElementById('url_input');
    const fileInput = document.getElementById('file_input');

    sourceRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            if (radio.value === 'url' && radio.checked) {
                urlInput.classList.remove('d-none');
                fileInput.classList.add('d-none');
            } else if (radio.value === 'file' && radio.checked) {
                urlInput.classList.add('d-none');
                fileInput.classList.remove('d-none');
            }
        });
    });

    // Auto-hide alerts after 3 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll(".alert");
        alerts.forEach(alert => alert.style.display = "none");
    }, 3000);
});
</script>
