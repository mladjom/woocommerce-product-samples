<?php
/**
 * Product Data Panel - Related Tab
 *
 * Functions to modify the Product Data Panel - Related Tab to add the
 * samples fields
 */
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

add_action('woocommerce_product_options_related', 'wc_samples_product_options');

/**
 * Display our custom product meta fields in the product edit page
 */
function wc_samples_product_options() {
    global $woocommerce, $post;
    echo '<div class="options_group">';
// Checkbox
    woocommerce_wp_checkbox(
            array(
                'id' => '_wcs_sample_enable',
                'label' => __('Enable or disable sample product for this item.', 'wcs'),
                'desc_tip' => 'true',
                'description' => __('Enable sample', 'wcs')
            )
    );
    ?>
    <p class="form-field"><label for="sample_product_ids"><?php _e('Sample Products', 'wcs'); ?></label>
        <input type="hidden" class="wc-product-search" style="width: 50%;" id="sample_product_ids" name="sample_product_ids" data-placeholder="<?php _e('Search for a product&hellip;', 'wcs'); ?>" data-action="woocommerce_json_search_products" data-multiple="true" data-selected="<?php
        $product_ids = array_filter(array_map('absint', (array) get_post_meta($post->ID, '_sample_product_ids', true)));
        $json_ids = array();

        foreach ($product_ids as $product_id) {
            $product = wc_get_product($product_id);
            if (is_object($product)) {
                $json_ids[$product_id] = wp_kses_post(html_entity_decode($product->get_formatted_name()));
            }
        }

        echo esc_attr(json_encode($json_ids));
        ?>" value="<?php echo implode(',', array_keys($json_ids)); ?>" /> <img class="help_tip" data-tip='<?php _e('Sample products are products which are used for selling samples of main product.', 'wcs') ?>' src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" /></p>
    <?php
    echo '</div>';
}

add_action('woocommerce_process_product_meta', 'wc_samples_process_product_meta');

/**
 * Save our custom product meta fields
 */
function wc_samples_process_product_meta($post_id) {
    // Enable Sample
    $woocommerce_checkbox = isset($_POST['_wcs_sample_enable']) ? 'yes' : 'no';
    update_post_meta($post_id, '_wcs_sample_enable', $woocommerce_checkbox);
    // Samples ids
    $sample_product_ids = isset($_POST['sample_product_ids']) ? array_filter(array_map('intval', explode(',', $_POST['sample_product_ids']))) : array();
    update_post_meta($post_id, '_sample_product_ids', $sample_product_ids);
}
