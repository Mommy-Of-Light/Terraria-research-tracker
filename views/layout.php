<!doctype html>
<html lang="fr" data-bs-theme="<?= isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'dark' ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,100..800;1,100..800&display=swap"
        rel="stylesheet">
    <script type="text/javascript"></script>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php if ($withMenu)
        echo $this->fetch('menu.php'); ?>
    <main class="container custom-container py-5">
        <?= $content ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>