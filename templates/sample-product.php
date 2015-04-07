<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

/**
 * Single Product Samples
 */
add_action('woocommerce_single_product_summary', 'wcs_single_button', 40);

function wcs_single_button() {
    if (get_option('wcs_custom_css'))
        echo '<style>' . get_option('wcs_custom_css') . '</style>';

    global $product, $woocommerce_loop;

    $sample_enabled = get_post_meta(get_the_ID(), '_wcs_sample_enable', true);

    if ($sample_enabled == 'yes') {
        ?>

        <div class="sample-products">
            <form class="cart" method="post" enctype='multipart/form-data'>
                <input type="hidden" name="wcs-specimen-parent" value="<?php echo the_title(); ?>" />
                <input type="hidden" name="add-to-cart" value="<?php echo get_option('wcs_id'); ?>" />
                <button type="submit" data-quantity="1" data-product_id="<?php echo get_option('wcs_id'); ?>" class="btn btn-success button alt sample_add_to_cart single_add_to_cart_button"><?php echo 'Add Sample to cart'; ?></button>
            </form>                
        </div>

        <?php
    }
}
