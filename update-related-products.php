<?php 
/* UPDATE RELATED PRODUCTS */
add_action( 'wp_ajax_update_related_products', 'blpw_update_related_products' );
add_action( 'wp_ajax_nopriv_add_related_products', 'blpw_update_related_products' );
function blpw_update_related_products()
{	
	parse_str($_POST['data'], $params);

	$upsell_ids 			= $params['upsell_ids'];
	$crosssell_ids			= $params['crosssell_ids'];
	
	$meta_key_crosssell_products = '_crosssell_ids';
	$meta_key_upsell_products = '_upsell_ids';
	
	update_post_meta($params['product_id'], $meta_key_crosssell_products, $crosssell_ids);
	update_post_meta($params['product_id'], $meta_key_upsell_products, $upsell_ids);
	
	die(json_encode(array('response'=>'TRUE')));
}
/* UPDATE RELATED PRODUCTS */
/* AJAX FUNCTION FOR GETTING PRODUCTS FOR SHOW ASSIGNED PRODUCTS */
add_action( 'wp_ajax_show_assigned_products', 'blpw_show_assigned_products' );
// add_action( 'wp_ajax_nopriv_load_products', 'blpw_show_assigned_products' );
function blpw_show_assigned_products()
{	

	$html = '<table width="100%">'; 
	$html.= '<tr>'; 
	$html.= '<td><h3>Cross Sell Products</h3></td>'; 
	$html.= '<td><h3>Upsell Products<h3></td>'; 
	$html.= '</tr>';

	$html.= '<tr>'; 

    $product_id = $_POST['p_slug'];
	$crosssellProductIds   =   get_post_meta( $product_id, '_crosssell_ids' );
	
	if($crosssellProductIds[0])
	{
			/* Get products by ID */
			$args = array(
		        'posts_per_page' => -1,
		        'post__in' => $crosssellProductIds[0],
		        'post_type' => 'product',
				'post_status' => 'publish',
		        'orderby' => 'title',
		    );
			$the_query = new WP_Query( $args );
			// The Loop
			$html.= '<td>'; 
			$html.= '<label><input type="checkbox" checked="checked" id="select_all_cross_sell">Select All</label><br><br>';
			$html.= '<ul>';
			while ( $the_query->have_posts() ) {
				$the_query->the_post();

				$pro = new WC_Product(get_the_ID());

				$html.= '<li><label for="crosssell-'.get_the_ID().'"><input type="checkbox" class="update_cross_sell" checked="checked" name="crosssell_ids[]" id="crosssell-'.get_the_ID().'" value="'.get_the_ID().'">'.get_the_title().' - <strong>('.($pro->is_in_stock()>0 ? 'In-Stock':'out of stock').') </strong></label></li>';
			}
			$html.= '</ul>';
			$html.= '</td>'; 
	}
	else
	{
		$html.'<td>No Products</td>';
	}

	/* Upsell IDS */
	$upsellProductIds   =   get_post_meta( $product_id, '_upsell_ids' );
	
	if($upsellProductIds[0])
	{
			/* Get products by ID */
			$args = array(
		        'posts_per_page' => -1,
		        'post__in' => $upsellProductIds[0],
		        'post_type' => 'product',
				'post_status' => 'publish',
		        'orderby' => 'title',
		    );
			$the_query = new WP_Query( $args );
			// The Loop
			$html.= '<td>'; 
			$html.= '<label><input type="checkbox" checked="checked" id="select_all_up_sell">Select All</label><br><br>';
			$html.= '<ul>';
			while ( $the_query->have_posts() ) {
				$the_query->the_post();

				$pro = new WC_Product(get_the_ID());

				$html.= '<li><label for="upsell-'.get_the_ID().'"><input type="checkbox" checked="checked" class="update_up_sell" name="upsell_ids[]" id="upsell-'.get_the_ID().'" value="'.get_the_ID().'">'.get_the_title().' - <strong>('.($pro->is_in_stock()>0 ? 'In-Stock':'out of stock').') </strong></label></li>';
			}
			$html.= '</ul>';
			$html.= '</td>'; 
	}
	else
	{
		$html.'<td>No Products</td>';
	}
	
	$html.='</tr>';
	$html.='<tr>';
	$html.='<td colspan="2">';

	$html.='<input type="submit" data-action="upsell" value="Update" class="bulk_action_btn gradient-button gradient-button-3">';

	$html.='</td>';
	$html.='</tr>';
	$html.='</table>';
	echo $html;
    exit;
}

function show_assigned_products_func(){
?>
<div class="add_bulk_related_container">
<header class="bulk_assign_linked_plugin">
	<div class="author" style="position: absolute; right: 20px; color : white">
		<p><strong>Author:</strong> <a target="_blank" href="https://www.facebook.com/vinodvaswani9/">Vinod Vaswani</a> </p>
	</div>
	<div class="overlay">
<!-- <h1>Simply The Best</h1> -->
<h1>Update Upsell & Cross-sell Assigned Products</h1>
<!-- <h3>Reasons for Choosing US</h3> -->
<p>This plugin will gives ability to bulk assign Cross-sell and upsell products in one click and will save your lots of time that you spend on every product at multiple screens.</p>
	</div>
</header>
<div class="ccolumn">
	<form action="" method="POST" id="update-form">
		
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
		'name'              => 'show_load_products',
		'id'                => 'show_load_products',
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
		<select id="show_panel_products" name="product_id">
			<option value="">Select Product</option>
		</select>
		<div id="show_assigned_products_panel">
		</div>

		<img src="<?php echo plugins_url('images/ajax-loader.gif', __FILE__) ?>" style="display:none" id="submit_loader">
	</form>
  </div>
</div>
<?php } ?>