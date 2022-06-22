<?php

/**
 * Storefront engine room
 *
 * @package storefront
 */

/**
 * Assign the Storefront version to a var
 */
$theme              = wp_get_theme('storefront');
$storefront_version = $theme['Version'];

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if (!isset($content_width)) {
	$content_width = 980; /* pixels */
}

$storefront = (object) array(
	'version'    => $storefront_version,

	/**
	 * Initialize all the things.
	 */
	'main'       => require 'inc/class-storefront.php',
	'customizer' => require 'inc/customizer/class-storefront-customizer.php',
);

require 'inc/storefront-functions.php';
require 'inc/storefront-template-hooks.php';
require 'inc/storefront-template-functions.php';
require 'inc/wordpress-shims.php';

if (class_exists('Jetpack')) {
	$storefront->jetpack = require 'inc/jetpack/class-storefront-jetpack.php';
}

if (storefront_is_woocommerce_activated()) {
	$storefront->woocommerce            = require 'inc/woocommerce/class-storefront-woocommerce.php';
	$storefront->woocommerce_customizer = require 'inc/woocommerce/class-storefront-woocommerce-customizer.php';

	require 'inc/woocommerce/class-storefront-woocommerce-adjacent-products.php';

	require 'inc/woocommerce/storefront-woocommerce-template-hooks.php';
	require 'inc/woocommerce/storefront-woocommerce-template-functions.php';
	require 'inc/woocommerce/storefront-woocommerce-functions.php';
}

if (is_admin()) {
	$storefront->admin = require 'inc/admin/class-storefront-admin.php';

	require 'inc/admin/class-storefront-plugin-install.php';
}

/**
 * NUX
 * Only load if wp version is 4.7.3 or above because of this issue;
 * https://core.trac.wordpress.org/ticket/39610?cversion=1&cnum_hist=2
 */
if (version_compare(get_bloginfo('version'), '4.7.3', '>=') && (is_admin() || is_customize_preview())) {
	require 'inc/nux/class-storefront-nux-admin.php';
	require 'inc/nux/class-storefront-nux-guided-tour.php';
	require 'inc/nux/class-storefront-nux-starter-content.php';
}

/**
 * Note: Do not add any custom code here. Please use a custom plugin so that your customizations aren't lost during updates.
 * https://github.com/woocommerce/theme-customisations
 */



function woocommerce_product_custom_fields()
{

	$productDate = array(
		'id'                => 'number_field',
		'label'             => 'Дата создания товара',
		'type'              => 'date',

	);
	woocommerce_wp_text_input($productDate);

	$productTtype = array(
		'id'      => 'select',
		'label'   => 'типа продукта',
		'options' => array(
			'rare'   => __('rare', 'woocommerce'),
			'frequent'   => __('frequent', 'woocommerce'),
			'unusual' => __('unusual', 'woocommerce'),
		),
	);
	woocommerce_wp_select($productTtype);


	$removeFields = array(
		'id' => 'remove-button',
		'label' => 'Remove Fields',
		'type'  => 'button',
		'value' => 'Remove Custom Fields'
	);

	woocommerce_wp_text_input($removeFields);

	$updateFields = array(
		'id' => 'update-button',
		'type'  => 'button',
		'value' => 'UPDATE_ALL'
	);

	woocommerce_wp_text_input($updateFields);
}





add_action('woocommerce_product_options_general_product_data', 'woocommerce_product_custom_fields');




function save_woocommerce_product_custom_fields($post_id)
{
	$product = wc_get_product($post_id);

	$custom_fields_woocommerce_title = isset($_POST['select']) ? $_POST['select'] : '';
	$product->update_meta_data('select', sanitize_text_field($custom_fields_woocommerce_title));
	$custom_fields_woocommerce_title = isset($_POST['number_field']) ? $_POST['number_field'] : '';
	$product->update_meta_data('number_field', sanitize_text_field($custom_fields_woocommerce_title));
	$product->save();
}
add_action('woocommerce_process_product_meta', 'save_woocommerce_product_custom_fields');

function select_display()
{
	global $post;
	$product = wc_get_product($post->ID);
	$custom_fields_woocommerce_title = $product->get_meta('select');
	if ($custom_fields_woocommerce_title) {
		printf(
			esc_html($custom_fields_woocommerce_title)
		);
	}
}

add_action('woocommerce_after_add_to_cart_form', 'select_display');

function selectes_display()
{
	global $post;
	$product = wc_get_product($post->ID);
	$custom_fields_woocommerce_title = $product->get_meta('number_field');
	if ($custom_fields_woocommerce_title) {
		printf(
			esc_html($custom_fields_woocommerce_title)
		);
	}
}
add_action('woocommerce_simple_add_to_cart', 'selectes_display');





add_action('edit_form_advanced', 'reset_inputs');
function reset_inputs($post)
{
?>

	<script>
		const removeBtn = document.querySelector('#remove-button'),
			numberBtn = document.querySelector('input#number_field'),
			selectBtn = document.querySelector('#select')
		removeBtn.addEventListener('click', function(e) {
			event.preventDefault();
			numberBtn.value = "";
			selectBtn.value = "";
		});

		const updateBtn = document.querySelector("#update-button")
		const updatePublish = document.querySelector("#publish")
		updateBtn.addEventListener("click", () => {
			updatePublish.click()
		})
	</script>

	<style>
		input#number_field,
		#select,
		#update-button,
		#remove-button {
			width: 160px !important;
		}

		.update-button_field {
			display: flex;
			justify-content: end;
		}
	</style>

<?php
}
