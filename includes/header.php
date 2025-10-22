<?php
$site_name = get_setting('site_name', 'NC-Admission');
$current_page = isset($_GET['page']) ? $_GET['page'] : 'home';
$page_title = get_page_title($current_page);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ระบบรับสมัครนักเรียนออนไลน์ <?php echo $site_name; ?>">
    <meta name="keywords" content="รับสมัคร, นักเรียน, อาชีวศึกษา, นครปฐม">
    <meta name="author" content="<?php echo $site_name; ?>">
    <title><?php echo $page_title; ?> - <?php echo $site_name; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="shortcut icon" type="image/png" href="assets/images/logo.png">
    <link rel="apple-touch-icon" href="assets/images/logo.png">
    
    <!-- Bootstrap 5 CSS (Local) -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- AOS Animation CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/custom.css">
</head>
<body>