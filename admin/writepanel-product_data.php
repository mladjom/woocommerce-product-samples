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
}

