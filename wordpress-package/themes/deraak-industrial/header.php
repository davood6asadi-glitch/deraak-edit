<?php
/**
 * Header Template for Deraak Industrial Theme
 *
 * @package Deraak_Industrial
 */

if (!defined('ABSPATH')) {
    exit;
}
?><!doctype html>
<html <?php language_attributes(); ?> dir="rtl">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    
    <!-- PWA & Mobile Web App Meta -->
    <meta name="theme-color" content="#031A54">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="دراک PWA">
    
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Offline Connectivity Warning -->
<div id="derak-offline-bar" class="derak-offline-banner hidden">
    <span>⚠️ شما در حالت آفلاین هستید. اطلاعات کاتالوگ از پایگاه داده محلی مرورگر فراخوانی می‌شود.</span>
</div>

<!-- Main Top Header -->
<header class="derak-header">
    <div class="derak-container derak-header-inner">
        <!-- Brand Title & Identity -->
        <a href="<?php echo esc_url(home_url('/')); ?>" class="derak-brand">
            <div class="derak-brand-icon">D</div>
            <div class="derak-brand-titles">
                <span class="derak-brand-name">دراک</span>
                <span class="derak-brand-sub">سامانه ابزار دقیق و اتوماسیون صنعتی</span>
            </div>
        </a>

        <!-- Fast SKU & Product Search -->
        <form role="search" method="get" class="derak-search-form" action="<?php echo esc_url(home_url('/')); ?>">
            <input type="hidden" name="post_type" value="product">
            <input type="search" class="derak-search-input" placeholder="جستجوی مدل (مانند PR 4116، FMR53، سنسور فشار)..." value="<?php echo get_search_query(); ?>" name="s" autocomplete="off" />
            <span class="derak-search-icon">🔍</span>
        </form>

        <!-- Header Actions: Consultation & RFQ -->
        <div class="derak-header-actions">
            <?php 
            $catalog_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/products');
            ?>
            <a href="<?php echo esc_url($catalog_url); ?>" class="derak-btn derak-btn-outline" style="display: none;">
                کاتالوگ
            </a>
            
            <a href="tel:02133900000" class="derak-btn derak-btn-accent">
                <span>مشاوره فنی لاله‌زار</span>
            </a>
        </div>
    </div>
</header>
<main class="derak-main-content">
