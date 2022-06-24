<?php
/*
Template Name: Add New Product
*/
?>
<?php get_header(); ?>
<div id="primary" class="content-area">
	<main id="main" class="site-main">
		<form method="post">
			<div>
				<h4>Product Data</h4>
			</div>
			<div>
				<div><label>Product Name</label></div>
				<div><input type="text" name="proname" class="proname" /></div>
			</div>
			<div>
				<div><label>Product Description</label></div>
				<div><textarea name="prodesc" class="prodesc"></textarea></div>
			</div>
			<div>
				<div><label>Product Price</label></div>
				<div><input type="text" name="proprice" class="proprice" /></div>
			</div>
			<div>
				<h4>Custom Meta</h4>
			</div>
			<div>
				<div><label>Product Custom1</label></div>
				<div><input type="text" name="procust1" class="procust1" /></div>
			</div>
			<div>
				<div><label>Product Custom2</label></div>
				<div><input type="text" name="procust2" class="procust2" /></div>
			</div>
			<div>
				<input type="submit" value="Submit" class="submit" onclick="prodsubmit();" />
			</div>
		</form>
	</main>
</div>


<?php
if (isset($_POST["submit"])) {
	global $wpdb;
	$post_data = array(
		'post_title' => $_POST['proname'],
		'post_content' => $_POST['prodesc'],
		'post_type' => 'product',
		'post_status' => 'publish'
	);
	$post_id = wp_insert_post($post_data);
	update_post_meta($post_id, '_regular_price', $_POST['proprice']);
	update_post_meta($post_id, 'procust1', $_POST['procust1']);
	update_post_meta($post_id, 'procust2', $_POST['procust2']);
}
?>
<?php get_footer(); ?>
