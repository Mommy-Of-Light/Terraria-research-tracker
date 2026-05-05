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
    <style>
        .theme-toggle-btn {
            cursor: pointer;
        }

        /* Width of the entire scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        /* Track (background) */
        ::-webkit-scrollbar-track {
            background: #2b2e31ff;
            border-radius: 10px;
        }

        /* Scroll thumb (the draggable part) */
        ::-webkit-scrollbar-thumb {
            background: #4b3b3bff;
            border-radius: 10px;
            border: 2px solid #f1f1f1;
        }

        /* Hover effect */
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        body {
            background: #1c1f2e;
            overflow: hidden;
        }

        .container {
            margin: 40px auto;
            font-family: "Courier New", monospace;
            color: #e6e6e6;
        }

        #viewStyleBox {
            margin-left: 10px;
            margin-right: 10px;
        }

        button {
            font-family: inherit;
            padding: 8px 16px;
            border: 2px solid #7aa2ff;
            background: linear-gradient(#3b3f55, #2a2d3d);
            color: #fff;
            cursor: pointer;
            box-shadow: 0 3px 0 #1a1c28;
        }

        button:hover:not(:disabled) {
            border-color: #ffe66d;
        }

        h2 {
            text-align: center;
            margin: 18px 0 10px;
            color: #ffe66d;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 14px;
            margin-bottom: 14px;
            align-items: center;
        }

        .hidden {
            display: none !important;
        }

        .toolbar {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            height: min-content;
            gap: 10px;
            /* margin-top: 10px; */
            margin-bottom: 10px;
        }

        .vertical-divider {
            content: "";
            display: inline-block;
            width: 1px;
            height: 100%;
            background-color: #7aa2ff;
            margin-left: 8px;
            margin-right: 8px;
            vertical-align: middle;
        }

        #viewStyleBox button.active {
            border-color: #ffe66d;
            color: #ffe66d;
        }

        .item-list {
            list-style: none;
            padding: 0;
            margin: 10px;
        }

        .item-list li {
            background: linear-gradient(#3b3f55, #2a2d3d);
            border: 2px solid #7aa2ff;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 6px;
            box-shadow: 0 3px 0 #1a1c28;
        }

        .item-list li:hover {
            border-color: #ffe66d;
        }

        .item-list-loader {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
        }

        .item-meta {
            font-size: 0.85rem;
            color: #9ad1ff;
        }

        .item-list.list li {
            margin-bottom: 6px;
        }

        .item-list.grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 8px;
        }

        .item-list.grid li {
            height: 170px;
            padding: 6px;
            text-align: center;
        }

        .item-slot-name {
            font-size: 0.8rem;
            margin-bottom: 4px;
        }

        .item-slot-meta {
            font-size: 0.7rem;
            color: #9ad1ff;
        }

        .page-size {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
        }

        .page-size select {
            font-family: inherit;
            background: #2a2d3d;
            color: #fff;
            border: 2px solid #7aa2ff;
            padding: 2px 6px;
        }

        .page-size select:hover {
            border-color: #ffe66d;
        }

        .item-status-green {
            border-color: #4caf50 !important;
        }

        .item-status-yellow {
            border-color: #f1c40f !important;
        }

        .item-status-red {
            border-color: #e74c3c !important;
        }

        .filter {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
        }

        #searchInput {
            background: #2a2d3d;
            border: 2px solid #7aa2ff;
            color: #fff;
            padding: 4px 6px;
            width: 160px;
            font-family: inherit;
        }

        #searchInput::placeholder {
            color: #aaa;
        }

        #sortSelect {
            background: #2a2d3d;
            border: 2px solid #7aa2ff;
            color: #fff;
            padding: 4px 6px;
            font-family: inherit;
        }

        #searchInput:focus,
        #sortSelect:hover {
            border-color: #ffe66d;
        }

        #summary {
            margin: 12px;
            text-align: center;
        }

        #itemList {
            max-height: 50vh;
            overflow-y: auto;
        }

        .summary-text {
            margin-bottom: 6px;
            font-weight: bold;
            color: #ffe66d;
        }

        .progress-bar-container {
            width: 100%;
            height: 14px;
            background: #2a2d3d;
            border: 2px solid #7aa2ff;
            border-radius: 6px;
            overflow: hidden;
            margin: 4px;
        }

        .progress-bar {
            height: 100%;
            background: #4caf50;
            width: 0%;
            transition: width 0.3s ease;
        }

        .progress-percent {
            font-size: 0.85rem;
            color: #9ad1ff;
        }

        .sprite {
            display: inline-block;
            background-repeat: no-repeat;
            background-size: auto 24px;
            width: 22px;
            height: 24px;
            image-rendering: pixelated;
        }
    </style>
<link rel="stylesheet" href="style.css">
</head>

<body>
    <?php if ($withMenu)
        echo $this->fetch('menu.php'); ?>
    <main class="container py-5" style="padding: 10px !important;">
        <?= $content ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>