<?php
/**
 * Header Template
 */
$dir = getDirection();
$langCode = __('lang_code');
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $langCode; ?>" dir="<?php echo $dir; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo __('site_title'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php
    // Determine correct path to assets
    $basePath = '';
    if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
        $basePath = '../';
    }
    ?>
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/style.css">
</head>
<body class="<?php echo $dir === 'rtl' ? 'rtl' : 'ltr'; ?>">
