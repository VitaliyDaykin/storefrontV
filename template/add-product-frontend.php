<?php

/**
 * Template Name: Add product-frontend
 *
 * 
 *
 * @package storefront
 */






add_shortcode('themedomain_frontend_post', 'themedomain_frontend_post');
function themedomain_frontend_post()
{
	themedomain_post_if_submitted();

?>
	<form action="POST" id="new_post" name="new_post" method="post" enctype="multipart/form-data">


		<p><label for="title"><?php echo esc_html__('Название продукта', 'theme-domain'); ?></label><br />
			<input type="text" id="title" value="" tabindex="1" size="20" name="title" />
		</p>
		<p><label for=""><?php echo esc_html__('Описание продукта', 'theme-domain'); ?></label><br />
			<?php wp_editor('', 'content'); ?>
		</p>
		<p><?php wp_dropdown_categories('show_option_none=Category&tab_index=4&taxonomy=category'); ?></p>

		<p><label for="post_tags"><?php echo esc_html__('Tags', 'theme-domain'); ?></label>

			<input type="text" value="" tabindex="5" size="16" name="post_tags" id="post_tags" />
		</p>

		<input type="file" name="post_image" id="post_image" aria-required="true">

		<p><input type="submit" value="Publish" tabindex="6" id="submit" name="submit" /></p>

	</form>

<?php
} ?>

<?php


function themedomain_post_if_submitted()
{
	// Stop running function if form wasn't submitted
	if (!isset($_POST['title'])) {
		return;
	}

	// Add the content of the form to $post as an array
	$post = array(
		'post_title'    => $_POST['title'],
		'post_content'  => $_POST['content'],
		'post_category' => array($_POST['cat']),
		'tags_input'    => $_POST['post_tags'],
		'post_status'   => 'draft',   // Could be: publish
		'post_type' 	=> 'product' // Could be: 'page' or your CPT
	);
	$post_id = wp_insert_post($post);

	// For Featured Image
	if (!function_exists('wp_generate_attachment_metadata')) {
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		require_once(ABSPATH . "wp-admin" . '/includes/media.php');
	}
	if ($_FILES) {
		foreach ($_FILES as $file => $array) {
			if ($_FILES[$file]['error'] !== UPLOAD_ERR_OK) {
				return "upload error : " . $_FILES[$file]['error'];
			}
			$attach_id = media_handle_upload($file, $post_id);
		}
	}
	if ($attach_id > 0) {
		update_post_meta($post_id, '_thumbnail_id', $attach_id);
	}

	echo 'Saved your post successfully! :)';
} ?>

<?php echo do_shortcode('[themedomain_frontend_post]'); ?>
