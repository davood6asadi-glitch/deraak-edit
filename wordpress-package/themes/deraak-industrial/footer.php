<?php
/**
 * Footer Template for Deraak Industrial Theme
 *
 * @package Deraak_Industrial
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
</main><!-- .derak-main-content -->

<!-- Industrial Footer -->
<footer class="derak-footer">
    <div class="derak-container">
        <div class="derak-footer-content">
            <div>
                <h4 style="color:#FFFFFF; margin-top:0;">دراک - تأمین تجهیزات ابزار دقیق و اتوماسیون</h4>
                <p style="margin: 4px 0 10px 0; max-width: 480px; line-height: 1.6;">
                    مرجع فنی استعلام بی‌واسطه ملزومات صنعتی، نفت، گاز، پتروشیمی، نیروگاهی و کارخانجات سیمان از بازار لاله‌زار تهران.
                </p>
                <span class="derak-iso-badge">ISO 9001:2015 Certified Quality</span>
            </div>

            <div>
                <h4 style="color:#FFFFFF; margin-top:0;">نشانی و اطلاعات تماس</h4>
                <p style="margin:4px 0;">تهران، خیابان لاله‌زار جنوبی، پاساژ صنعت، واحد ۱۲</p>
                <p style="margin:4px 0;" dir="ltr">تلفن: ۰۲۱-۳۳۹۰۰۰۰۰ | ایمیل: info@add.co.ir</p>
                <p style="margin:4px 0; font-size:11px; color:var(--derak-gold-light);">وب‌سایت مرجع بین‌المللی: <a href="https://deraak.com" target="_blank" rel="noopener noreferrer" style="color:var(--derak-gold-light); text-decoration:underline;">deraak.com</a></p>
            </div>
        </div>

        <div style="border-top: 1px solid var(--derak-blue-deep); margin-top: 24px; padding-top: 16px; text-align: center; font-size: 11px; color: var(--derak-stone-neutral);">
            کلیه حقوق این سامانه متعلق به مجموعه مهندسی دراک می‌باشد. © <?php echo date('Y'); ?> add.co.ir
        </div>
    </div>
</footer>

<!-- Mobile Bottom Navigation Dock -->
<nav class="derak-bottom-dock">
    <a href="<?php echo esc_url(home_url('/')); ?>" class="derak-dock-item <?php echo is_front_page() ? 'active' : ''; ?>">
        <span>🏠</span>
        <span>خانه</span>
    </a>
    <?php 
    $shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop');
    ?>
    <a href="<?php echo esc_url($shop_url); ?>" class="derak-dock-item <?php echo (function_exists('is_shop') && (is_shop() || is_product_taxonomy())) ? 'active' : ''; ?>">
        <span>📦</span>
        <span>محصولات</span>
    </a>
    <a href="<?php echo esc_url(home_url('/inquiry')); ?>" class="derak-dock-item">
        <span>📋</span>
        <span>استعلام</span>
        <span id="derak-rfq-count-badge" class="derak-dock-badge" style="display:none;">0</span>
    </a>
    <a href="<?php echo esc_url(home_url('/contact')); ?>" class="derak-dock-item">
        <span>📞</span>
        <span>تماس</span>
    </a>
</nav>

<?php wp_footer(); ?>
</body>
</html>
