<?php
// Start Session
session_start();

// Include Config
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get Page Parameter
$page = isset($_GET['page']) ? clean_input($_GET['page']) : 'home';

// Include Header
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="content-wrapper">
    <?php
    // Router - Switch Case
    switch ($page) {
        case 'home':
            include 'pages/home.php';
            break;
            
        case 'about':
            include 'pages/about.php';
            break;
            
        case 'news':
            include 'pages/news.php';
            break;
            
        case 'news_detail':
            include 'pages/news_detail.php';
            break;
            
        case 'gallery':
            include 'pages/gallery.php';
            break;
            
        case 'contact':
            include 'pages/contact.php';
            break;
            
        case 'admission_info':
            include 'pages/admission_info.php';
            break;
            
        case 'quota_form':
            include 'pages/quota_form.php';
            break;
            
        case 'regular_form':
            include 'pages/regular_form.php';
            break;
            
        case 'form_submit':
            include 'pages/form_submit.php';
            break;
            
        case 'check_status':
            include 'pages/check_status.php';
            break;
            
        default:
            include 'pages/404.php';
            break;
    }
    ?>
</div>

<?php
// Include Footer
include 'includes/footer.php';
?>