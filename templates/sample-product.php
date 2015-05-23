<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly


/**
 * Single Product Samples
 */
$position = get_option('wcs_button_position');

add_action('woocommerce_single_product_summary', 'wcs_single_button', $position);

function wcs_single_button() {
    if (get_option('wcs_custom_css'))
        echo '<style>' . get_option('wcs_custom_css') . '</style>';

    global $product, $woocommerce_loop;

    $sample_enabled = get_post_meta(get_the_ID(), '_wcs_sample_enable', true);

    if ($sample_enabled == 'yes') :

        $sample_ids = get_post_meta(get_the_ID(), '_sample_product_ids', true);
        
        $parent_title = get_the_title( $product->id );

        if (!empty($sample_ids)) {

            $meta_query = WC()->query->get_meta_query();

            $args = array(
                'post_type' => 'product',
                'ignore_sticky_posts' => 1,
                'no_found_rows' => 1,
                'posts_per_page' => -1,
                'orderby' => 'rand',
                'post__in' => $sample_ids,
                'post__not_in' => array($product->id),
                'meta_query' => $meta_query
            );

            $products = new WP_Query($args);

            if ($products->have_posts()) :
                ?>
                <div class="sample-products">
                    <?php while ($products->have_posts()) : $products->the_post(); ?>
                        <form class="cart" method="post" enctype='multipart/form-data'>
                            <?php //woocommerce_quantity_input();  ?>
                            <input type="hidden" name="wcs-specimen-parent" value="<?php echo $parent_title; ?>" />
                            <input type="hidden" name="add-to-cart" value="<?php echo get_the_ID(); ?>" />
                            <button type="submit" data-quantity="1" data-product_id="<?php echo get_the_ID(); ?>"
                                    class="btn btn-success button alt single_add_to_cart_button"><?php echo get_option('wcs_add_to_cart_button_text'); ?></button>
                        </form>                
                    <?php endwhile; // end of the loop.    ?>
                </div>
                <?php
            endif;

            wp_reset_postdata();
        } else {
            ?>

            <div class="sample-products">
                <form class="cart" method="post" enctype='multipart/form-data'>
                    <input type="hidden" name="wcs-specimen-parent" value="<?php echo the_title(); ?>" />
                    <input type="hidden" name="add-to-cart" value="<?php echo get_option('wcs_id'); ?>" />
                    <button type="submit" data-quantity="1" data-product_id="<?php echo get_option('wcs_id'); ?>" 
                            class="btn btn-success button alt sample_add_to_cart single_add_to_cart_button"><?php echo get_option('wcs_add_to_cart_button_text'); ?></button>
                </form>                
            </div>

            <?php
        }
    endif;
}
