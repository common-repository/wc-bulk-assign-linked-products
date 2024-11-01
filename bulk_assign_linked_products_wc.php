<?php
/*
* Plugin Name: Bulk Assign Linked Products For WooCommerce
* Version: 2.1
* Description: This plugin will gives ability to bulk assign Cross-sell and upsell products in one click and will save your lots of time that you spend on every product at multiple screens. 
* Author: vinod vaswani
* Author URI: https://www.facebook.com/vinodvaswani9
* License: GPLv2 or later
*/
define('BLPW_PLUGIN_TITLE', "Bulk Assign Linked Products For WooCommerce");
function blpw_filter_related_products($args) {
	global $post;
	$related = get_post_meta( $post->ID, '_upsell_ids', true );
	if($related) { // remove category based filtering
		$args['post__in'] = $related;
	}
	else{
		
		$args['post__in'] = array(0);
	}
	return $args;
}
add_filter( 'woocommerce_related_products_args', 'blpw_filter_related_products' );
add_action( 'wp_ajax_load_products', 'blpw_add_bulk_load_products' );
// add_action( 'wp_ajax_nopriv_load_products', 'blpw_add_bulk_load_products' );
function blpw_add_bulk_load_products()
{	
	$html = '';
	
	
	if(sanitize_text_field($_POST['section'])==1)
		$html.= '<label><input type="checkbox" id="select_all_left">Select All</label><br><br>';
	else
		$html.= '<label><input type="checkbox" id="select_all_right">Select All</label><br><br>';

    /* Get products by ID */
	$args = array(
        'posts_per_page' => -1,
        'product_cat' => sanitize_text_field($_POST['p_slug']),
        'post_type' => 'product',
		'post_status' => 'publish',
        'orderby' => 'title',
    );
	$the_query = new WP_Query( $args );
	// The Loop
	
	while ( $the_query->have_posts() ) {
		$the_query->the_post();
		
		$pro = new WC_Product(get_the_ID());
		
		if(sanitize_text_field($_POST['section'])==1)
		{
			$html.= '<label for="product-'.get_the_ID().'"><input type="checkbox" class="bulk_product" data-name="'.get_the_title().'" data-id="'.get_the_ID().'" id="product-'.get_the_ID().'" value="'.get_the_ID().'">'.get_the_title().' - <strong>('.($pro->is_in_stock()>0 ? 'In-Stock':'out of stock').') </strong></label><br>';
		}
		elseif(sanitize_text_field($_POST['section'])==2)
		{
			$html.= '<label for="product-2-'.get_the_ID().'"><input type="checkbox" class="bulk_product_2" data-name-2="'.get_the_title().'" data-id-2="'.get_the_ID().'" id="product-2-'.get_the_ID().'" value="'.get_the_ID().'">'.get_the_title().' - <strong> ('.($pro->is_in_stock()>0 ? 'In-Stock':'out of stock').') </strong></label><br>';
		}
	}
    echo $html;
	exit;
}
/* AJAX FUNCTION FOR GETTING PRODUCTS FOR SHOW ASSIGNED PRODUCTS */
add_action( 'wp_ajax_show_load_products', 'blpw_show_load_products' );
// add_action( 'wp_ajax_nopriv_load_products', 'blpw_show_load_products' );
function blpw_show_load_products()
{	
    /* Get products by ID */
	$args = array(
        'posts_per_page' => -1,
        'product_cat' => sanitize_text_field($_POST['p_slug']),
        'post_type' => 'product',
		'post_status' => 'publish',
        'orderby' => 'title',
    );
	$the_query = new WP_Query( $args );
	// The Loop

	$html = '<option value="">Select Product</option>';
	while ( $the_query->have_posts() ) {
		$the_query->the_post();

		$pro = new WC_Product(get_the_ID());

		$html.= '<option value="'.get_the_ID().'">'.get_the_title().' - ('.($pro->is_in_stock()>0 ? 'In-Stock':'out of stock').')'.'</option>';
	}
	echo $html;
    exit;
}
/* FUNCTION THAT WILL ASSIGN LINKED PRODUCTS BASED ON LINKED PRODUCT TYPE UPSELL OR CROSSSELL */
add_action( 'wp_ajax_add_related_products', 'blpw_add_bulk_related_products' );
add_action( 'wp_ajax_nopriv_add_related_products', 'blpw_add_bulk_related_products' );
function blpw_add_bulk_related_products()
{	
			$product_ids 			= array();
			$related_product_ids	= array();
			//$prev_val				= array();

			$linked_product_type = sanitize_text_field($_POST['linked_product_type']);


			if(!empty(sanitize_text_field($_POST['related_product_ids'])))
			{	
				$temp = array();
				$temp = explode(',', sanitize_text_field($_POST['related_product_ids']));
				$product_ids = $temp;
			}

			if(!empty(sanitize_text_field($_POST['product_ids'])))
			{
				$temp = array();
				$temp = explode(',', sanitize_text_field($_POST['product_ids']));
				$related_product_ids = $temp;
			}

			$process_finished = false;


			if($linked_product_type=="cross_sell")
			{
				$meta_key_linked_products = '_crosssell_ids';
			}
			elseif($linked_product_type=="upsell")
			{
				$meta_key_linked_products = '_upsell_ids';
			}

			
			foreach ($product_ids as $single_id)
			{
				$prev_val = get_post_meta($single_id,$meta_key_linked_products,true);

				if(is_array($prev_val))
				{	
					$final_val = array_merge($prev_val, $related_product_ids);
					$final_val = array_unique($final_val);
					$final_val = array_values($final_val);
					update_post_meta($single_id, $meta_key_linked_products, $final_val);
					$process_finished = true;
				}
				else
				{
					update_post_meta($single_id, $meta_key_linked_products, $related_product_ids);
					$process_finished = true;
				}
			}
			if($process_finished)
			{
				die(json_encode(array('response'=>'TRUE')));
			}
}
/* FUNCTION THAT WILL ASSIGN LINKED PRODUCTS BASED ON LINKED PRODUCT TYPE UPSELL OR CROSSSELL */
/* Add script and style */
function blpw_style() {
	$protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';
	$params = array(
			// Get the url to the admin-ajax.php file using admin_url()
			'ajaxurl' => admin_url( 'admin-ajax.php', $protocol ),
			'remove_img' => plugins_url('images/remove.png', __FILE__)
		);
	wp_register_style( 'add-bulk-style', plugins_url('css/add-bulk-style.css', __FILE__) );
	wp_enqueue_style('add-bulk-style');
	//wp_register_style( 'bootstrap', plugins_url('css/bootstrap.min.css', __FILE__) );
	// wp_enqueue_style('bootstrap');
	wp_register_script( 'ajax-functions', plugins_url('js/ajax-functions.js', __FILE__) );
	wp_localize_script( 'ajax-functions', 'ajaxObject', $params );
	wp_enqueue_script('ajax-functions');
	// wp_register_script( 'list-js-plugin', plugins_url('js/list.min.js', __FILE__) );
	// wp_enqueue_script('list-js-plugin');
}
add_action( 'admin_enqueue_scripts', 'blpw_style' );
/* Add script and style */


/* Add menu */
function blpw_register_my_custom_submenu_page() {
    add_submenu_page( 'woocommerce', "Bulk Assign Linked Products", "Bulk Assign Linked Products", 'manage_options', 'bulk-add-upsell-products', 'blpw_bulk_add_upsell_products_func' ); 

    add_submenu_page( 'woocommerce', "Update Upsell & Cross Sell", "Update Upsell & Cross Sell", 'manage_options', 'show-assigned-products', 'show_assigned_products_func' ); 
 
}
add_action('admin_menu', 'blpw_register_my_custom_submenu_page',99);
/* Add menu */

function blpw_bulk_add_upsell_products_func() { ?>
<div class="add_bulk_related_container">
<header class="bulk_assign_linked_plugin">
	<div class="author" style="position: absolute; right: 20px; color : white">
		<p><strong>Author:</strong> <a target="_blank" href="https://www.facebook.com/vinodvaswani9/">Vinod Vaswani</a></p>
	</div>
	<div class="overlay">
<!-- <h1>Simply The Best</h1> -->
<h1>Bulk Assign Linked Products For Wooocommerce ( Upsell & Cross-sell )</h1>
<!-- <h3>Reasons for Choosing US</h3> -->
<p>This plugin will gives ability to bulk assign Cross-sell and upsell products in one click and will save your lots of time that you spend on every product at multiple screens.</p>
	</div>
</header>

<form class="form-horizontal" id="add-bulk">
<div class="crow">
  <div class="ccolumn">
    
	<h3>Select Products that you want to link.</h3>
    <h4>Product Category</h4>
    <?php
	$defaults = array(
	'show_option_all'   => '',
	'show_option_none'  => '',
	'orderby'           => 'id',
	'order'             => 'ASC',
	'show_count'        => 0,
	'hide_empty'        => 0,
	'child_of'          => 0,
	'exclude'           => '',
	'echo'              => 0,
	'selected'          => 0,
	'hierarchical'      => 1,
	'name'              => 'product_category',
	'id'                => 'product_category',
	'class'             => 'postform',
	'depth'             => 0,
	'tab_index'         => 0,
	'taxonomy'          => 'product_cat',
	'hide_if_empty'     => false,
	'option_none_value' => -1,
	'value_field'       => 'slug',
	'required'          => false,
	);
	$select = wp_dropdown_categories($defaults);
	echo $select;
	?>

	<div class="row" id="select-related-products" style="display:none">
	  <div class="col-md-12">
	    <p>Below selected products are going to linked. </p>
	    <div id="products" name="products" class="form-control">
		
	    </div>
	  </div>
	</div>

	<div class="row" id="selected-related-products" style="display:none">
	  <div class="col-md-12">
	  <label class="col-md-12 control-label" for="selectbasic">Selected Related Products</label>

	  <a href="javascript:void(0)" class="clear_all_btn remove_left_selected">Remove All Selected</a>

	    <ul id="selected-products" name="selected-products" class="form-control" style="width:600px;height:200px;overflow:scroll">
	    </ul>
	  </div>
	</div>
  </div>
  <div class="ccolumn">
	
	<input type="hidden" name="linked_product_type" id="linked_product_type" value="">

	<p><input type="submit" data-action="upsell" value="Assign Upsell" class="bulk_action_btn gradient-button gradient-button-3"></p>
	
	<p><input type="submit" data-action="cross_sell" value="Assign Cross-sell " class="bulk_action_btn gradient-button gradient-button-3"></p>

	<img src="<?php echo plugins_url('images/ajax-loader.gif', __FILE__) ?>" style="display:none" id="submit_loader">

  </div>
  <div class="ccolumn">
		<h3>Link on these products</h3>
    	<h4>Product Category</h4>

		<?php
		$defaults = array(
		'show_option_all'   => '',
		'show_option_none'  => '',
		'orderby'           => 'id',
		'order'             => 'ASC',
		'show_count'        => 0,
		'hide_empty'        => 0,
		'child_of'          => 0,
		'exclude'           => '',
		'echo'              => 0,
		'selected'          => 0,
		'hierarchical'      => 1,
		'name'              => 'product_category_2',
		'id'                => 'product_category_2',
		'class'             => 'postform',
		'depth'             => 0,
		'tab_index'         => 0,
		'taxonomy'          => 'product_cat',
		'hide_if_empty'     => false,
		'option_none_value' => -1,
		'value_field'       => 'slug',
		'required'          => false,
		);
		$select = wp_dropdown_categories($defaults);
		echo $select;
		?>
	    
		<div class="row" id="select-related-products-2" style="display:none">
		  <div class="col-md-12">
		  
		   	<p>Products selected in right will be linked to each product selected below. </p>
		    <div id="products_2" name="products_2" class="form-control" style="width:600px;height:200px; overflow: scroll;">
		    </div>
		  </div>
		</div>

		<div class="row" id="selected-related-products-2" style="display:none">
		   <div class="col-md-12">
		   <label class="col-md-12 control-label" for="selectbasic">Selected Products</label>
		   <a href="javascript:void(0)" class="clear_all_btn remove_right_selected">Remove All Selected</a>
		    <ul id="selected-products-2" name="selected-products-2" class="form-control" style="width:600px;height:200px;overflow:scroll">
		    </ul>
		  </div>
		</div>
  </div>
</div>
</form>
</div>
<?php
}
//main thing is to use ABSPATH
$file = "update-related-products.php";    
require( $file ); // use include if you want.
?>