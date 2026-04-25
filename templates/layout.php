<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title ?? 'Strux Framework') ?></title>
    <link rel="icon" type="image/png" href="<?= asset('images/favicon.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/welcome.css') ?>">
</head>
<body class="h-full bg-slate-900 text-slate-200 font-inter antialiased">
    
    <div class="min-h-full flex flex-col">
        <?= $this->insert('partials::header') ?>

        <main class="flex-grow">
            <?= $this->section('content') ?>
        </main>

        <?= $this->insert('partials::footer') ?>
    </div>

</body>
</html>
