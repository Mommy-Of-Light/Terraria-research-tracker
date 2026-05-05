<div class="row justify-content-center">

    <!-- Upload form -->
    <div class="col-md-5 mb-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title text-center">Upload new file</h5>

                <form action="/user/files/upload" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <input type="file" name="player_file" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Upload
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- File list -->
    <div class="col-md-5">
        <h2 class="mb-4 text-center">Files</h2>

        <?php foreach ($playerFiles as $file): ?>
            <div class="card mb-3 shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><?= htmlspecialchars(basename($file)) ?></h5>

                    <form action="/user/files/delete" method="POST" class="ms-3">
                        <input type="hidden" name="file" value="<?= htmlspecialchars($file) ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>

    </div>
</div>