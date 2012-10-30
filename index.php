<?php
/*
Plugin Name: dpb_scheduler
Plugin URI: 
Description: A flexible scheduling plugin with customizable taxonomies and shortcodes.
Author: Dan Breczinski
Version: 0.1
Author URI: http://dpb.me/
Cedits/reference:
	http://codex.wordpress.org/Function_Reference/add_meta_box
	http://jqueryui.com/datepicker/
	http://yoast.com/custom-post-type-snippets/
*/

/*

Todo:
	create code for not displaying outdated events
	create option for open registration events
	Add available spots and registration option
	Internationalize text using __('...') format
	Validate date inputs
	find out what the 10 and 2 are for in this: add_action( "manage_posts_custom_column", "custom_columns", 10, 2 );
	Research re variable scope in PHP
	create field for location and allow it to be included in table
*/


global $post;

/********************************************************************************

		Register post status

********************************************************************************/

// function custom_status()
// {
// 	register_post_status('expired');
// }

// add_action('init', 'custom_status');




/********************************************************************************

		Create a custom post type for events

********************************************************************************/

function dpb_scheduler_create_post_type_schedule()
{
	$labels = array(
		'name' => __( 'dpb_scheduler','your_text_domain'),
		'singular_name' => __( 'Event','your_text_domain' ),
		'add_new' => __('Create New','your_text_domain'),
		'add_new_item' => __('Create A New Event','your_text_domain'),
		'edit_item' => __('Edit Event','your_text_domain'),
		'new_item' => __('Create Event','your_text_domain'),
		'view_item' => __('View Event','your_text_domain'),
		'search_items' => __('Search Events','your_text_domain'),
		'not_found' =>  __('Sorry, no events found.','your_text_domain'),
		'not_found_in_trash' => __('No events found in trash.','your_text_domain'), 
		'parent_item_colon' => ''
	  );
	  
	  $args = array(
		'labels' => $labels,
		'public' => true,
		'exclude_from_search' => false,
		'publicly_queryable' => true,
		'show_ui' => true, 
		'query_var' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'menu_position' => 5,
		// Uncomment the following line to change the slug; 
		// You must also save your permalink structure to prevent 404 errors
		//'rewrite' => array( 'slug' => 'dpb_scheduler' ), 
		//'supports' => array('title','editor','thumbnail','custom-fields','page-attributes','excerpt'),
		'supports' => array('title','editor','thumbnail'),
		'taxonomies' => array('dpb_scheduler_event_categories','dpb_scheduler_event_tags')
	  ); 
	  
	  register_post_type(__( 'dpb_scheduler' ),$args);
}
	
add_action( 'init', 'dpb_scheduler_create_post_type_schedule');

/********************************************************************************

		Create a custom taxonomy for events

********************************************************************************/

function dpb_scheduler_event_categroies_init()
{
	register_taxonomy(
		'dpb_scheduler_event_categories',
		'dpb_scheduler',
		array(
			'labels' => array(
				'name' => 'Event categories',
				'singular_name' => 'Event category'
				),
			'hierarchical' => true
		)
	);
}

function dpb_scheduler_event_tags_init()
{
	register_taxonomy(
		'dpb_scheduler_event_tags',
		'dpb_scheduler',
		array(
			'labels' => array(
				'name' => 'Event tags',
				'singular_name' => 'Event tag'
			)
		)
	);
}

add_action('init', 'dpb_scheduler_event_categroies_init');
add_action('init', 'dpb_scheduler_event_tags_init');

/********************************************************************************

		Manage event dates via meta-box in admin panel

********************************************************************************/

//add meta boxes
add_action( 'add_meta_boxes', 'dpb_scheduler_create_meta_box');

function dpb_scheduler_create_meta_box()
{
	add_meta_box('dpb_scheduler_meta_box_event_info', 
		'Event information and options', 
		'dpb_scheduler_meta_box_callback', 
		'dpb_scheduler',
		'normal',
		'high'
		);
}

function dpb_scheduler_meta_box_callback($post)
{
  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'dpb_scheduler_nonce' );

  $start_date = date_format(date_create(get_post_meta($post->ID, "dpb_scheduler_start_date", true)), 'm/d/Y');
  $end_date = date_format(date_create(get_post_meta($post->ID, "dpb_scheduler_end_date", true)), 'm/d/Y');
  $date_time_info = get_post_meta($post->ID, "dpb_scheduler_date_time_info", true);
  $cost = get_post_meta($post->ID, "dpb_scheduler_cost", true);
  $available_spots = get_post_meta($post->ID, "dpb_scheduler_avail", true);

  $close_registration = get_post_meta($post->ID, "dpb_scheduler_close_registration", true);
  $close_registration_after_start_date = get_post_meta($post->ID, "dpb_scheduler_close_registration_after_start_date", true);

  if($close_registration == 'true'){
  	$close_registration = 'checked="true"';
  }else{
  	$close_registration = '';
  }

  if($close_registration_after_start_date == 'true'){
  	$close_registration_after_start_date = 'checked="true"';
  }else{
  	$close_registration_after_start_date = '';
  }

  // Include JQueryUI for dates
  echo '<link rel="stylesheet" href="http://code.jquery.com/ui/1.9.0/themes/base/jquery-ui.css" />';
  echo '<script src="http://code.jquery.com/jquery-1.8.2.js"></script>';
  echo '<script src="http://code.jquery.com/ui/1.9.0/jquery-ui.js"></script>';
  // Date inputs
  echo '<script> $(function() { $( ".dpb_scheduler_datepicker" ).datepicker(); }); </script>';
  echo '<p>Start date: <input type="text" id="dpb_scheduler_start_date" name="dpb_scheduler_start_date" class="dpb_scheduler_datepicker" value='.$start_date.'></input></p>';
  echo '<p>End date: <input type="text" id="dpb_scheduler_end_date" name="dpb_scheduler_end_date" class="dpb_scheduler_datepicker" value='.$end_date.'></input></p>';
  //Date and time info.
  echo '<p>Date/time info.:</p>';
  echo '<textarea rows="5" cols="60" id="dpb_scheduler_date_time_info" name="dpb_scheduler_date_time_info">'.$date_time_info.'</textarea>';
  echo '<p>Cost: <input type="text" id="dpb_scheduler_cost" name="dpb_scheduler_cost" value='.$cost.'></input></p>';
  echo '<p>Available spots: <input type="text" id="dpb_scheduler_avail" name="dpb_scheduler_avail" value='.$available_spots.'></input></p>';
  
  echo '<br /><input type="checkbox" name="dpb_scheduler_close_registration" value="true" '.$close_registration.'> Close registration</input>';
  echo '<br /><input type="checkbox" name="dpb_scheduler_close_registration_after_start_date" value="true" '.$close_registration_after_start_date.'> Close registration after start date</input>';


}

// do something with the data entered
add_action( 'save_post', 'dpb_scheduler_save_postdata' );

function dpb_scheduler_save_postdata($post_id)
{

	// verify if this is an auto save routine. 
	// If it is our form has not been submitted, so we dont want to do anything
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		return;

	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if ( !wp_verify_nonce( $_POST['dpb_scheduler_nonce'], plugin_basename( __FILE__ ) ) )
	  return;
	// Check permissions
	if ( 'page' == $_POST['post_type'] ) 
	{
	  if ( !current_user_can( 'edit_page', $post_id ) )
		return;
	}
	else
	{
		if ( !current_user_can( 'edit_post', $post_id ) )
			return;
	}

	$start_date = date_create($_POST['dpb_scheduler_start_date']);
	$end_date = date_create($_POST['dpb_scheduler_end_date']);
	$date_time_info = $_POST['dpb_scheduler_date_time_info'];
	$cost = $_POST['dpb_scheduler_cost'];
	$available_spots = $_POST['dpb_scheduler_avail'];
	$date_time_info = $_POST['dpb_scheduler_date_time_info'];

	$close_registration = $_POST['dpb_scheduler_close_registration'];
	$close_registration_after_start_date = $_POST['dpb_scheduler_close_registration_after_start_date'];

	update_post_meta($post_id, 'dpb_scheduler_start_date', date_format($start_date, 'Y-m-d'));
	update_post_meta($post_id, 'dpb_scheduler_end_date', date_format($end_date, 'Y-m-d'));
	update_post_meta($post_id, 'dpb_scheduler_date_time_info', $date_time_info);
	update_post_meta($post_id, 'dpb_scheduler_cost', $cost);
	update_post_meta($post_id, 'dpb_scheduler_avail', $available_spots);

	if($close_registration == 'true'){
		update_post_meta($post_id, 'dpb_scheduler_close_registration', 'true');
	}else{
		update_post_meta($post_id, 'dpb_scheduler_close_registration', 'false');
	}
	if($close_registration_after_start_date == 'true'){
		update_post_meta($post_id, 'dpb_scheduler_close_registration_after_start_date', 'true');
	}else{
		update_post_meta($post_id, 'dpb_scheduler_close_registration_after_start_date', 'false');
	}
}

/********************************************************************************

		Cusomize columns in admin interface

********************************************************************************/

//add columns

function dpb_scheduler_add_columns($cols)
{
	//return an array of existing columns merged with themes custom columns
	return array_merge
	(
		$cols, 
		array
		(
			'cb'       => '<input type="checkbox" />',
			//'post_status' => __( 'Post status',      'trans' ),
			'start_date'      => __( 'Start date',      'trans' ),
			'end_date' => __( 'End date', 'trans')
		)
	);
}

add_filter("manage_dpb_scheduler_posts_columns", "dpb_scheduler_add_columns");

//add content to columns

function dpb_scheduler_custom_columns( $column, $post_id ) {
  switch ( $column ) {
    case "start_date":
      echo get_post_meta( $post_id, 'dpb_scheduler_start_date', true);
      
      break;
    case "end_date":
      echo get_post_meta( $post_id, 'dpb_scheduler_end_date', true);
      break;
     // case "post_status":
     //  echo get_post_status($post_id);
  }
}

add_action( "manage_posts_custom_column", "dpb_scheduler_custom_columns", 10, 2 );

//make columns sortable

function dpb_scheduler_sortable_columns() {
  return array(
    'start_date' => 'start_date',
    'end_date' => 'end_date',
    'post_status' => 'post_status'
  );
}

add_filter( "manage_edit-dpb_scheduler_sortable_columns", "dpb_scheduler_sortable_columns" );

//Add dropdown boxes for custom taxonomies

function dpb_scheduler_taxonomy_filter() {
    global $typenow;

    // If you only want this to work for your specific post type,
    // check for that $type here and then return.
    // This function, if unmodified, will add the dropdown for each
    // post type / taxonomy combination.

    //Return if not this plugin
    if (!($typenow == 'dpb_scheduler')) return;

    $post_types = get_post_types( array( '_builtin' => false ) );

    if ( in_array( $typenow, $post_types ) ) {
    	$filters = get_object_taxonomies( $typenow );

        foreach ( $filters as $tax_slug ) {
            $tax_obj = get_taxonomy( $tax_slug );
            wp_dropdown_categories( array(
                'show_option_all' => __('Show All '.$tax_obj->label ),
                'taxonomy' 	  => $tax_slug,
                'name' 		  => $tax_obj->name,
                'orderby' 	  => 'name',
                'selected' 	  => $_GET[$tax_slug],
                'hierarchical' 	  => $tax_obj->hierarchical,
                'show_count' 	  => false,
                'hide_empty' 	  => true
            ) );
        }
    }
}

add_action( 'restrict_manage_posts', 'dpb_scheduler_taxonomy_filter');

//filter the query

function taxonomy_filter_post_type_request( $query )
{
  global $pagenow, $typenow;

  if ( 'edit.php' == $pagenow ) {
    $filters = get_object_taxonomies( $typenow );
    foreach ( $filters as $tax_slug ) {
      $var = &$query->query_vars[$tax_slug];
      if ( isset( $var ) ) {
        $term = get_term_by( 'id', $var, $tax_slug );
        $var = $term->slug;
      }
    }
  }
}

add_filter( 'parse_query', 'taxonomy_filter_post_type_request' );

/********************************************************************************

		Shortcodes

********************************************************************************/

function dpb_scheduler_event_list($attributes)
{
	/*
	todo:
		include an argument to return query and take query
		if you pre-generate query's, it will save querying each time for all 
			categories, tags, etc.
	*/
	$return_string = '';
	$tag_array = array();
	$category_array = array();

	//create a list of categories and tags
	$dpb_categories = get_categories(array(
		'taxonomy' => 'dpb_scheduler_event_categories'));
	$dpb_tags = get_categories(array(
		'taxonomy' => 'dpb_scheduler_event_tags'));
	foreach ($dpb_categories as $category) {
	 	array_push($category_array, $category->name);
	}
	foreach ($dpb_tags as $tag) {
		array_push($tag_array, $tag->name);
	}

	//parse arguments
	extract(shortcode_atts(array(
		'tags' => '',
		'categories' => '',
		'relation' => 'AND'
		), $attributes));

	//validate tags and categories, and construct comma sep list of each for query
	$valid_tags = array();
	if($tags!='')
	{
		//$valid_tags = array();
		$explode_tags = explode(",", $tags);
		foreach ($explode_tags as $tag) {
			$tag = trim($tag);
			if(in_array($tag, $tag_array)){
				array_push($valid_tags, $tag);
			}
		}
		if(!(empty($valid_tags))){
			$tags = implode(',',$valid_tags);
		}else{
			$tags = '';
		}
	}

	//return $tags;

	$valid_categories = array();
	if($categories!='')
	{
		//$valid_categories = array();
		$explode_categories = explode(",", $categories);
		foreach ($explode_categories as $category) {
			$category = trim($category);
			if(in_array($category, $category_array)){
				array_push($valid_categories, $category);
			}
		}
		if(!(empty($valid_categories))){
			$categories = implode(',',$valid_categories);
		}else{
			$categories = '';
		}
	}
/*
	$arguments = array(
		'dpb_scheduler_event_tags' => $tags,
		'dpb_scheduler_event_categories' => $categories,
		//'operator' => 'AND',
		'orderby' => 'meta_value',
		'meta_key' => 'dpb_scheduler_start_date',
		'order' => 'ASC',
		'posts_per_page' => -1
		);
*/

	$arguments['tax_query'] = array(
		'relation' => $relation);
	//array_push($arguments['tax_query'], array('relation' => 'OR'));//'relation' => $relation);
	if(!(empty($valid_categories))){
		array_push($arguments['tax_query'],
			array(
				'taxonomy' => 'dpb_scheduler_event_categories',
				'terms' => $valid_categories,
				'field' => 'name'
			)
	    );
	}
	array_push($arguments['tax_query'],
		array(
			'taxonomy' => 'dpb_scheduler_event_categories',
			'terms' => array('expired'),
			'field' => 'name',
			'operator' => 'NOT IN'
			)
	    );

	if(!(empty($valid_tags))){
		array_push($arguments['tax_query'],
			array(
				'taxonomy' => 'dpb_scheduler_event_tags',
				'terms' => $valid_tags,
				'field' => 'name'
			)
	    );
	}
	    // array(
	    //     'taxonomy' => 'dpb_scheduler_event_categories',
	    //     'terms' => $valid_categories,
	    //     'field' => 'slug',
	    // ),

//array(
	         //'taxonomy' => 'dpb_scheduler_event_tags',
	         //'terms' => $valid_tags,
	         //'field' => 'slug',
//	     ),
//	);
	//query_posts($myquery);

	date_default_timezone_set('America/New_York');
	$todays_date = date('Y-m-d');

	$query = new WP_Query($arguments);
	
	//$todays_date = 
	//return $query;
	$return_string .= '<table><tr><th>Event</th><th>Start date</th><th>End date</th><th>Info.</th><th>Open spots</th></tr>';
	while($query->have_posts()) : $query->the_post();
		
		//$start_date = date_format(date_create(get_post_meta(get_the_ID(), "dpb_scheduler_start_date", true)), 'm/d/Y');
  		//$end_date = date_format(date_create(get_post_meta(get_the_ID(), "dpb_scheduler_end_date", true)), 'm/d/Y');


  		$start_date = get_post_meta(get_the_ID(), "dpb_scheduler_start_date", true);
  		$end_date = get_post_meta(get_the_ID(), "dpb_scheduler_end_date", true);
  		$registration_closed = (
  								 (get_post_meta(get_the_ID(), "dpb_scheduler_close_registration", true) == 'true') ||
  								 (
  								 	($start_date < $todays_date) &&
  								 	(get_post_meta(get_the_ID(), "dpb_scheduler_close_registration_after_start_date", true) == 'true')
  								 )
  								);

  		if ($end_date<$todays_date){
  			$start_date = "past";
  			$end_date = "past";
  			//$terms = wp_get_object_terms(get_the_ID(), 'dpb_scheduler_event_categories');
  			//$print_r($terms);
  			//array_push($terms, 'expired');
  			wp_set_object_terms( get_the_ID(), 'expired',  'dpb_scheduler_event_categories', true);

  		}else{
  			$start_date = date_format(date_create($start_date), 'm/d/Y');
  			$end_date = date_format(date_create($end_date), 'm/d/Y');

  			$return_string .= '<tr>';
  			$return_string .= '<td><a href ="'.get_permalink().'">'.get_the_title().'</a></td>';
  			$return_string .= '<td>'.$start_date.'</td>';
  			$return_string .= '<td>'.$end_date.'</td>';
  			$return_string .= '<td>'.get_post_meta(get_the_ID(), 'dpb_scheduler_date_time_info', true).'</td>';
  			$return_string .= '<td>'.get_post_meta(get_the_ID(), 'dpb_scheduler_avail', true).'</td>';
  			if($registration_closed){
  				$return_string .= '<td><a href="'.get_permalink().'">REGISTRATION CLOSED</a></td>';
  			}else{
  				$return_string .= '<td><a href="'.get_permalink().'"><button>Register</button></a></td>';
  			}
  			$return_string .= '</tr>';
  		}
	endwhile;
	$return_string .= '</table>';
	wp_reset_postdata(); //is this necessary?
	return $return_string;// $return_string;
}

add_shortcode('dpb_scheduler', 'dpb_scheduler_event_list');

/********************************************************************************

		Create template for registration

********************************************************************************/

function dpb_scheduler_registration_form($content){
	global $post;
	if($post->post_type == 'dpb_scheduler'){
		//$content = 'lalala';
	}
	return $content;
}

add_filter('the_content', 'dpb_scheduler_registration_form');

?>