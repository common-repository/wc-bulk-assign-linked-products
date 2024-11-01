jQuery(document).ready(function(){

	var linked_product_type = "";

	/* For section 1 */
	jQuery("form#add-bulk").on('change',"#product_category",function(){
		var p_slug = jQuery(this).val();
		var this_elm = jQuery(this);
		if(p_slug=='')
		{
			jQuery("#products").html("Please select category to load products");
			return false;
		}

		jQuery("#submit_loader").show();

		jQuery.ajax({
			url: ajaxObject.ajaxurl,
			data: {"p_slug":p_slug,"action":"load_products","section":1},
			type: "POST",
			success: function(result)
			{
        		jQuery("#products").html(result);
        		jQuery("#submit_loader").hide();
    		},
    		complete: function(result)
    		{
    			jQuery("#selected-products li").each(function(i,e){
    				jQuery('#product-'+jQuery(this).attr('data-id')).attr('checked', true);
    			});

    			select_all_button_left();

    			jQuery("#submit_loader").hide();
    			jQuery('#select-related-products').show();
    		}
    	});
	});


	function select_all_button_left()
	{
		if(jQuery('.bulk_product:checked').length==jQuery('.bulk_product').length)
		jQuery("#select_all_left").prop('checked', true);
		else
		jQuery("#select_all_left").prop('checked', false);
	}
	


	/* For section 2 */
	jQuery("form#add-bulk").on('change',"#product_category_2",function(){
		var p_slug_2 = jQuery(this).val();
		var this_elm = jQuery(this);

		if(p_slug_2=='')
		{
			jQuery("#products_2").html("Please select category to load products");
			return false;
		}

		jQuery("#submit_loader").show();

		jQuery.ajax({
			url: ajaxObject.ajaxurl,
			data: {"p_slug":p_slug_2,"action":"load_products","section":2},
			type: "POST",
			success: function(result)
			{
        		jQuery("#products_2").html(result);
        		jQuery("#submit_loader").hide();
    		},
    		complete: function(result)
    		{
    			jQuery("#selected-products-2 li").each(function(i,e){
    				jQuery('#product-2-'+jQuery(this).attr('data-id-2')).attr('checked', true);
    			});

    			select_all_button_right();
    			jQuery("#submit_loader").hide();
    			jQuery('#select-related-products-2').show();
    		}
    	});
	});


	function select_all_button_right()
	{
		if(jQuery('.bulk_product_2:checked').length==jQuery('.bulk_product_2').length)
		jQuery("#select_all_right").prop('checked', true);
		else
		jQuery("#select_all_right").prop('checked', false);
	}


	/* For section 1 */
	jQuery("form#add-bulk").on('click','.bulk_product', function(){
		select_product(jQuery(this));
	});

	/* For section 2 */
	jQuery("form#add-bulk").on('click','.bulk_product_2', function(){
		select_product_2(jQuery(this));
	});



	function select_product(elm)
	{
		var bulk_product_id = elm.attr('data-id');
		var bulk_product_name = elm.attr('data-name');

		if(elm.is(':checked'))
		{
			if(jQuery('li[data-id="'+bulk_product_id+'"]').length==0)
			jQuery('#selected-products').append('<li class="selected-item" data-id="'+bulk_product_id+'">'+bulk_product_name+'<span class="remove_elm"><img src="'+ajaxObject.remove_img+'"></span></li>');
		}
		else
		jQuery('.selected-item[data-id="'+bulk_product_id+'"]').remove();


		jQuery('#selected-related-products').show();
		select_all_button_left();
	}

	function select_product_2(elm)
	{
		var bulk_product_id_2 = elm.attr('data-id-2');
		var bulk_product_name_2 = elm.attr('data-name-2');

		if(jQuery('li[data-id-2="'+bulk_product_id_2+'"]').length==0)
		jQuery('#selected-products-2').append('<li class="selected-item-2" data-id-2="'+bulk_product_id_2+'">'+bulk_product_name_2+'<span class="remove_elm_2"><img src="'+ajaxObject.remove_img+'"></span></li>');
		else
		jQuery('.selected-item-2[data-id-2="'+bulk_product_id_2+'"]').remove();

		jQuery('#selected-related-products-2').show();
		select_all_button_right();
	}




	/* Remove selected item from list and also uncheck checkbox  */
	jQuery("#selected-products").on('click', ".remove_elm", function(){
		var chk_id = jQuery(this).parent().attr('data-id');
		jQuery("#product-"+chk_id).prop('checked', false);
		jQuery(this).parent('li').remove();	
		select_all_button_left();
	});

	/* Remove selected item from list and also uncheck checkbox  */
	jQuery("#selected-products-2").on('click', ".remove_elm_2", function(){
		var chk_id2 = jQuery(this).parent().attr('data-id-2');
		jQuery("#product-2-"+chk_id2).prop('checked', false);	
		jQuery(this).parent('li').remove();
		select_all_button_right();
	});



	// Remove all selected products
	jQuery(document).on('click', ".remove_left_selected", function(){
		/*Empty UL*/
		jQuery('#selected-products').empty();
		jQuery("#products input:checkbox").prop('checked', false);
	});


	// Remove all selected products
	jQuery(document).on('click', ".remove_right_selected", function(){
		/*Empty UL*/
		jQuery('#selected-products-2').empty();
		jQuery("#products_2 input:checkbox").prop('checked', false);
	});


	jQuery(".add_bulk_related_container").on('click', ".bulk_action_btn", function(){
		var action = jQuery(this).attr('data-action');
		linked_product_type = action;
	});
	
    

	jQuery(document).on('submit',"form#add-bulk",function(e){

		e.preventDefault();

		var product_ids = [];
		var related_product_ids = [];


		jQuery("#selected-products li").each(function(){
			product_ids.push(jQuery(this).attr('data-id'));
		});

		jQuery("#selected-products-2 li").each(function(){
			related_product_ids.push(jQuery(this).attr('data-id-2'));
		});


		if(product_ids.length==0 || related_product_ids.length==0)
		{
			alert("Please select minimum 1 product in both sections to make related");
			return false;
		}


		if(!confirm("Are you sure want to assing products as linked products ?"))
		{
			return false;
		}


		product_ids = product_ids.toString();
		related_product_ids = related_product_ids.toString();

		jQuery("#submit_loader").show();


		// Get the linked produt type either upsell or cross-sell
		// Type of the linked product is hidden in FORM in field naem ( linked_product_type  values are upsell and cross_sell )

		

		jQuery.ajax({
			url: ajaxObject.ajaxurl,
			data: {"linked_product_type":linked_product_type,"product_ids":product_ids,"related_product_ids":related_product_ids,"action":"add_related_products"},
			type: "POST",
			success: function(result)
			{
        		var obj = jQuery.parseJSON(result);
    			if(obj.response==='TRUE')
    			{
	        			alert('Related products added successfully');
	        			jQuery("#submit_loader").hide();
    			}

    		}
    	});

	});





	/* ####################### UPDATE SUBMIT FORM ##############################*/
	jQuery(document).on('submit',"form#update-form",function(e){

		e.preventDefault();

		var product_ids = [];
		var related_product_ids = [];

		jQuery("#submit_loader").show();

		jQuery.ajax({
			url: ajaxObject.ajaxurl,
			data: {"data":jQuery("#update-form").serialize(),"action":"update_related_products"},
			type: "POST",
			success: function(result)
			{	
				jQuery("#submit_loader").hide();
        		var obj = jQuery.parseJSON(result);
    			if(obj.response==='TRUE')
    			{
	        			alert("Updated successfully");
    			}
    			else if(obj.response==='FALSE')
    			{
	        			alert('oops something went wrong!');
    			}
    		}
    	});

	});

	/* ####################### UPDATE SUBMIT FORM ##############################*/
	


	/* ########################### manage related products ##############################*/
	jQuery("form#manage-related").on('change',"#manage_product_category",function(){
		var p_slug = jQuery(this).val();
		var this_elm = jQuery(this);

		if(p_slug=='')
		{
			jQuery("#manage_products").html("Please select category to load products");
			return false;
		}
		
		jQuery("#submit_loader").show();

		jQuery.ajax({
			url: ajaxObject.ajaxurl,
			data: {"p_slug":p_slug,"action":"manage_load_products"},
			type: "POST",
			success: function(result)
			{
        		jQuery("#manage_products").html(result);
        		jQuery("#submit_loader").hide();
        		jQuery('ul#fetch-related-products').empty();

        		jQuery("#manage-select-products").show();
    		}
    	});
	});




	jQuery("form#manage-related").on('click',".existing_related_product",function(){
        if(jQuery(this).is(':checked'))
        {
            var product_id = jQuery(this).attr('data-id');

            jQuery("#related_loader").show();

            jQuery.ajax({
			url: ajaxObject.ajaxurl,
			data: {"product_id":product_id,"action":"get_related_products"},
			type: "POST",
			success: function(result)
			{
        		jQuery("#fetch-related-products").html(result);
        		jQuery("#related_loader").hide();
    		},
    		complete: function(result)
    		{
    			// jQuery("#selected-products li").each(function(i,e){
    			// 	jQuery('#product-'+jQuery(this).attr('data-id')).attr('checked', true);
    			// });

            	jQuery("#manage-select-products-fetch").show();
    		}
    		});

        }
    });

	jQuery("form#manage-related").on('click',".remove_related",function(){
        if(confirm("Are you sure want to delete this from related products."))
        {
            var data_product_id = jQuery(this).parent("li").attr('data-product-id');
            var data_related_id = jQuery(this).parent("li").attr('data-related-id');
            var elm = jQuery(this).parent("li");

            jQuery.ajax({
			url: ajaxObject.ajaxurl,
			data: {"data_product_id":data_product_id,"data_related_id":data_related_id,"action":"remove_related_products"},
			type: "POST",
			success: function(result)
			{
        		var obj = jQuery.parseJSON(result);
    			if(obj.response==='TRUE')
    			{
	        			elm.remove();
    			}
    			else if(obj.response==='FALSE')
    			{
	        			alert('oops something went wrong!');
    			}

    		}
    		});

        }
    });






	/* FUNCTIONS FOR SHOW ASSIGNED PRODUCTS */
	jQuery(document).on('change',"#show_load_products",function(){
		var p_slug = jQuery(this).val();
		var this_elm = jQuery(this);

		if(p_slug=='')
		{
			jQuery("#manage_products").html("Please select category to load products");
			return false;
		}
		
		jQuery("#submit_loader").show();

		jQuery.ajax({
			url: ajaxObject.ajaxurl,
			data: {"p_slug":p_slug,"action":"show_load_products"},
			type: "POST",
			success: function(result)
			{	
				jQuery("#submit_loader").hide();
        		jQuery("#show_panel_products").html(result);
    		}
    	});
	});
	/* FUNCTIONS FOR SHOW ASSIGNED PRODUCTS */


	/* Show assigned product for the selected product */
	jQuery(document).on('change',"#show_panel_products",function(){
		var p_slug = jQuery(this).val();
		var this_elm = jQuery(this);

		if(p_slug=='')
		{
			jQuery("#manage_products").html("Please select category to load products");
			return false;
		}
		
		jQuery("#submit_loader").show();

		jQuery.ajax({
			url: ajaxObject.ajaxurl,
			data: {"p_slug":p_slug,"action":"show_assigned_products"},
			type: "POST",
			success: function(result)
			{	
				jQuery("#submit_loader").hide();
        		jQuery("#show_assigned_products_panel").html(result);
    		}
    	});
	});




	/* Toggle select all products */
	jQuery(document).on('change',"#select_all_left",function(event){
        if (this.checked) {
            jQuery('.bulk_product').each(function () { //loop through each checkbox
                jQuery(this).prop('checked', true); //check 

                /* Do the add all items to the list Magic begins here */
                jQuery('.bulk_product').each(function () {
                	/* pass checkbox one by one for adding into the list */
    				select_product(jQuery(this));
					// alert(jQuery(this).val());
				});

            });
        } else {
            jQuery('.bulk_product').each(function () { //loop through each checkbox
                jQuery(this).prop('checked', false); //uncheck    
                jQuery('li.selected-item[data-id="'+jQuery(this).val()+'"]').remove();

            });
            
        }
    });


    jQuery(document).on('change',"#select_all_right",function(event){
        if (this.checked) {
            jQuery('.bulk_product_2').each(function () { //loop through each checkbox
                jQuery(this).prop('checked', true); //check 

                /* Do the add all items to the list Magic begins here */
                jQuery('.bulk_product_2').each(function () {
                	/* pass checkbox one by one for adding into the list */
    				select_product_2(jQuery(this));
					// alert(jQuery(this).val());
				});


            });
        } else {
            jQuery('.bulk_product_2').each(function () { //loop through each checkbox
                jQuery(this).prop('checked', false); //uncheck  
                jQuery('li.selected-item-2[data-id="'+jQuery(this).val()+'"]').remove();            
            });

        }
    });

	



	/* Script for update cross sell & upsell products select all */
	jQuery(document).on('change',"#select_all_cross_sell",function(event){
        if(this.checked){
            jQuery('.update_cross_sell').each(function(){
                this.checked = true;
            });
        }else{
             jQuery('.update_cross_sell').each(function(){
                this.checked = false;
            });
        }
    });
    jQuery(document).on('click',".update_cross_sell",function(event){
        if(jQuery('.update_cross_sell:checked').length == jQuery('.update_cross_sell').length){
            jQuery('#select_all_cross_sell').prop('checked',true);
        }else{
            jQuery('#select_all_cross_sell').prop('checked',false);
        }
    });



    jQuery(document).on('change',"#select_all_up_sell",function(event){
        if(this.checked){
            jQuery('.update_up_sell').each(function(){
                this.checked = true;
            });
        }else{
             jQuery('.update_up_sell').each(function(){
                this.checked = false;
            });
        }
    });
    jQuery(document).on('click',".update_up_sell",function(event){
        if(jQuery('.update_up_sell:checked').length == jQuery('.update_up_sell').length){
            jQuery('#select_all_up_sell').prop('checked',true);
        }else{
            jQuery('#select_all_up_sell').prop('checked',false);
        }
    });
    
	

})