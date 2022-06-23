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

define('CP_DIR', plugin_dir_path(__FILE__));
define('CP_URI', plugin_dir_url(__FILE__));

class CP_Shortcode
{
	function __construct()
	{

		add_shortcode('cp_form', [$this, 'shortcode_form']);
	}

	function shortcode_form()
	{
		ob_start();
	?>
		<form action="POST" id="event-form" class="event-form">

			<?php
			foreach ($this->fields() as $key => $value) {
				$this->fields_form($key, $value);
			}
			?>

			<button type="submit" class="button submit-event" name="send_event">
				Добавить продукт
			</button>

		</form>
		<?php
		return ob_get_clean();
	}


	function fields()
	{
		return [
			'event_product' => [
				'type'              => 'text',
				'label'             => 'Название товара',
				'required'          => true,

			],
			'event_topics' => [
				'type'              => 'select',
				'label'             => 'тип товара',
				'options' => [
					'rare'   => 'rare',
					'frequent'   => 'frequent',
					'unusual' => 'unusual'
				],

			],

		];
	}



	function fields_form($key, $args, $value = null)
	{

		$defaults = array(
			'type'              => 'text',
			'label'             => '',
			'description'       => '',
			'placeholder'       => '',
			'maxlength'         => false,
			'required'          => false,
			'autocomplete'      => false,
			'id'                => $key,
			'class'             => array(),
			'label_class'       => array(),
			'input_class'       => array(),
			'return'            => false,
			'options'           => array(),
			'custom_attributes' => array(),
			'validate'          => array(),
			'default'           => '',
			'autofocus'         => '',
			'priority'          => '',
		);

		$args = wp_parse_args($args, $defaults);
		$args = apply_filters('afp_form_field_args', $args, $key, $value);

		if ($args['required']) {
			$args['class'][] = 'validate-required';
			$required        = '&nbsp;<abbr class="required" title="' . esc_attr__('required', 'afp') . '">*</abbr>';
		} else {
			$required = '&nbsp;<span class="optional">(' . esc_html__('optional', 'afp') . ')</span>';
		}

		if (is_string($args['label_class'])) {
			$args['label_class'] = array($args['label_class']);
		}

		if (is_null($value)) {
			$value = $args['default'];
		}

		// Custom attribute handling.
		$custom_attributes         = array();
		$args['custom_attributes'] = array_filter((array) $args['custom_attributes'], 'strlen');

		if ($args['maxlength']) {
			$args['custom_attributes']['maxlength'] = absint($args['maxlength']);
		}

		if (!empty($args['autocomplete'])) {
			$args['custom_attributes']['autocomplete'] = $args['autocomplete'];
		}

		if (true === $args['autofocus']) {
			$args['custom_attributes']['autofocus'] = 'autofocus';
		}

		if ($args['description']) {
			$args['custom_attributes']['aria-describedby'] = $args['id'] . '-description';
		}

		if (!empty($args['custom_attributes']) && is_array($args['custom_attributes'])) {
			foreach ($args['custom_attributes'] as $attribute => $attribute_value) {
				$custom_attributes[] = esc_attr($attribute) . '="' . esc_attr($attribute_value) . '"';
			}
		}

		if (!empty($args['validate'])) {
			foreach ($args['validate'] as $validate) {
				$args['class'][] = 'validate-' . $validate;
			}
		}

		$field           = '';
		$label_id        = $args['id'];
		$sort            = $args['priority'] ? $args['priority'] : '';
		$field_container = '<p class="form-row %1$s" id="%2$s" data-priority="' . esc_attr($sort) . '">%3$s</p>';

		switch ($args['type']) {
			case 'textarea':
				$field .= '<textarea name="' . esc_attr($key) . '" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" id="' . esc_attr($args['id']) .
					'" placeholder="' . esc_attr($args['placeholder']) . '" ' . (empty($args['custom_attributes']['rows']) ? ' rows="2"' : '') .
					(empty($args['custom_attributes']['cols']) ? ' cols="5"' : '') . implode(' ', $custom_attributes) . '>' . esc_textarea($value) . '</textarea>';

				break;
			case 'checkbox':
				$field = '<label class="checkbox ' . implode(' ', $args['label_class']) . '" ' . implode(' ', $custom_attributes) . '>
					<input type="' . esc_attr($args['type']) . '" class="input-checkbox ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($key) .
					'" id="' . esc_attr($args['id']) . '" value="1" ' . checked($value, 1, false) . ' /> ' . $args['label'] . $required . '</label>';

				break;
			case 'text':
			case 'password':
			case 'datetime':
			case 'datetime-local':
			case 'date':
			case 'month':
			case 'time':
			case 'week':
			case 'number':
			case 'email':
			case 'url':
			case 'file':
			case 'tel':
				$field .= '<input type="' . esc_attr($args['type']) . '" class="input-text ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($key) .
					'" id="' . esc_attr($args['id']) . '" placeholder="' . esc_attr($args['placeholder']) . '"  value="' . esc_attr($value) . '" ' .
					implode(' ', $custom_attributes) . ' />';

				break;
			case 'datepicker':
				wp_enqueue_script('jquery-ui-datepicker');
				wp_enqueue_style('jqueryui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css', false, null);

				$field .= '<input type="text" class="datepicker ' . esc_attr(implode(' ', $args['input_class'])) . '" name="' . esc_attr($key) . '" id="' .
					esc_attr($args['id']) . '" placeholder="' . esc_attr($args['placeholder']) . '"  value="' . esc_attr($value) . '" ' .
					implode(' ', $custom_attributes) . ' />';

				break;
			case 'select':
				$field   = '';
				$options = '';

				if (!empty($args['options'])) {
					foreach ($args['options'] as $option_key => $option_text) {
						if ('' === $option_key) {
							// If we have a blank option, select2 needs a placeholder.
							if (empty($args['placeholder'])) {
								$args['placeholder'] = $option_text ? $option_text : __('Choose an option', 'afp');
							}
							$custom_attributes[] = 'data-allow_clear="true"';
						}
						$options .= '<option value="' . esc_attr($option_key) . '" ' . selected($value, $option_key, false) . '>' . esc_attr($option_text) . '</option>';
					}

					$field .= '<select name="' . esc_attr($key) . '" id="' . esc_attr($args['id']) . '" class="select ' . esc_attr(implode(' ', $args['input_class'])) .
						'" ' . implode(' ', $custom_attributes) . ' data-placeholder="' . esc_attr($args['placeholder']) . '">
						' . $options . '
					</select>';
				}

				break;
			case 'multiselect':
				$field   = '';
				$options = '';

				if (!empty($args['options'])) {
					foreach ($args['options'] as $option_key => $option_text) {
						if ('' === $option_key) {
							if (empty($args['placeholder'])) {
								$args['placeholder'] = $option_text ? $option_text : __('Choose an option', 'afp');
							}
							$custom_attributes[] = 'data-allow_clear="true"';
						}
						$options .= '<option value="' . esc_attr($option_key) . '" ' . selected($value, $option_key, false) . '>' . esc_attr($option_text) . '</option>';
					}

					$field .= '<select multiple name="' . esc_attr($key) . '[]" id="' . esc_attr($args['id']) . '" class="multiselect ' .
						esc_attr(implode(' ', $args['input_class'])) . '" ' . implode(' ', $custom_attributes) . ' data-placeholder="' .
						esc_attr($args['placeholder']) . '">
						' . $options . '
					</select>';
				}

				break;
			case 'radio':
				$label_id .= '_' . current(array_keys($args['options']));

				if (!empty($args['options'])) {
					foreach ($args['options'] as $option_key => $option_text) {
						$field .= '<input type="radio" class="input-radio ' . esc_attr(implode(' ', $args['input_class'])) . '" value="' . esc_attr($option_key) .
							'" name="' . esc_attr($key) . '" ' . implode(' ', $custom_attributes) . ' id="' . esc_attr($args['id']) . '_' . esc_attr($option_key) .
							'"' . checked($value, $option_key, false) . ' />';
						$field .= '<label for="' . esc_attr($args['id']) . '_' . esc_attr($option_key) . '" class="radio ' . implode(' ', $args['label_class']) . '">' .
							$option_text . '</label>';
					}
				}

				break;
			case 'wysiwyg_editor':
				wp_localize_script(
					'afcp-script',
					'field_editor',
					[
						'key' => esc_attr($key),
					]
				);

				ob_start();
				wp_editor(
					esc_textarea($value),
					esc_attr($key),
					[
						'wpautop'          => $args['custom_attributes']['wpautop'],
						'media_buttons'    => $args['custom_attributes']['media_buttons'],
						'textarea_name'    => $key,
						'textarea_rows'    => $args['custom_attributes']['textarea_rows'],
						'tabindex'         => $args['custom_attributes']['tabindex'],
						'editor_css'       => $args['custom_attributes']['editor_css'],
						'editor_class'     => $args['custom_attributes']['editor_class'],
						'teeny'            => $args['custom_attributes']['teeny'],
						'dfw'              => $args['custom_attributes']['dfw'],
						'tinymce'          => $args['custom_attributes']['tinymce'],
						'quicktags'        => $args['custom_attributes']['quicktags'],
						'drag_drop_upload' => $args['custom_attributes']['drag_drop_upload'],
					]
				);
				$editor = ob_get_clean();

		?>
				<div class="<?php echo esc_attr(implode(' ', $args['class'])); ?>" id="<?php echo esc_attr($args['id']) . '_field'; ?>" style="margin: 0 0 20px;">
					<?php if (!empty($args['label'])) : ?>
						<label>
							<?php echo $args['label']; ?>
							<?php if (!empty($args['required'])) : ?>
								<abbr class="required" title="Обязательное">*</abbr>
							<?php endif; ?>
						</label>
					<?php endif; ?>
					<?php echo $editor; ?>

				</div>

<?php
				break;
		}

		if (!empty($field)) {
			$field_html = '';

			if ($args['label'] && 'checkbox' !== $args['type']) {
				$field_html .= '<label for="' . esc_attr($label_id) . '" class="' . esc_attr(implode(' ', $args['label_class'])) . '">' . $args['label'] . $required .
					'</label>';
			}

			$field_html .= '<span class="afp-input-wrapper">' . $field;

			if ($args['description']) {
				$field_html .= '<span class="description" id="' . esc_attr($args['id']) . '-description" aria-hidden="true">' . wp_kses_post($args['description']) . '</span>';
			}

			$field_html .= '</span>';

			$container_class = esc_attr(implode(' ', $args['class']));
			$container_id    = esc_attr($args['id']) . '_field';
			$field           = sprintf($field_container, $container_class, $container_id, $field_html);
		}

		if (!$args['return']) {
			echo $field; // WPCS: XSS ok.
		}

		return $field;
	}
}
