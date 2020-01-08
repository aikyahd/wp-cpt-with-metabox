<?php
/**
 * All the code below should be in theme's functions.php (parent theme or child)
 * It can also be placed under plugin file.
*/

/**
 * Register a custom post type called "project".
 *
 * @see get_post_type_labels() for label keys.
 */
function wpdocs_codex_project_init() {
    $labels = array(
        'name'                  => _x( 'Projects', 'Post type general name', 'textdomain' ),
        'singular_name'         => _x( 'Project', 'Post type singular name', 'textdomain' ),
        'menu_name'             => _x( 'Projects', 'Admin Menu text', 'textdomain' ),
        'name_admin_bar'        => _x( 'Project', 'Add New on Toolbar', 'textdomain' ),
        'add_new'               => __( 'Add New', 'textdomain' ),
        'add_new_item'          => __( 'Add New Project', 'textdomain' ),
        'new_item'              => __( 'New Project', 'textdomain' ),
        'edit_item'             => __( 'Edit Project', 'textdomain' ),
        'view_item'             => __( 'View Project', 'textdomain' ),
        'all_items'             => __( 'All Projects', 'textdomain' ),
        'search_items'          => __( 'Search Projects', 'textdomain' ),
        'parent_item_colon'     => __( 'Parent Projects:', 'textdomain' ),
        'not_found'             => __( 'No projects found.', 'textdomain' ),
        'not_found_in_trash'    => __( 'No projects found in Trash.', 'textdomain' ),
        'featured_image'        => _x( 'Project Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'textdomain' ),
        'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'textdomain' ),
        'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'textdomain' ),
        'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'textdomain' ),
        'archives'              => _x( 'Project archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'textdomain' ),
        'insert_into_item'      => _x( 'Insert into project', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'textdomain' ),
        'uploaded_to_this_item' => _x( 'Uploaded to this project', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'textdomain' ),
        'filter_items_list'     => _x( 'Filter projects list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'textdomain' ),
        'items_list_navigation' => _x( 'Projects list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'textdomain' ),
        'items_list'            => _x( 'Projects list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'textdomain' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'project' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
    );

    register_post_type( 'project', $args );
}
add_action( 'init', 'wpdocs_codex_project_init' );


/**
 * Add metabox to the post editor window, right sidebar.
 */
add_action( 'add_meta_boxes', 'add_events_metaboxes' );
function add_events_metaboxes() {
	add_meta_box(
		'wpt_events_location',
		'Event Location', // Meta name
		'wpt_events_location',
		'project', //post_type
		'side', //placement
		'default'
	);
}


/**
 * Create the metabox input field
 */
function wpt_events_location() {
	global $post;
	// Nonce field to validate form request came from current site
	wp_nonce_field( basename( __FILE__ ), 'event_fields' );
	// Get the location data if it's already been entered
	$location = get_post_meta( $post->ID, 'location', true );
	// Output the field
	echo '<input type="text" name="location" value="' . esc_textarea( $location )  . '" class="widefat">';
}


/**
 * Save the metabox data
 */
function wpt_save_events_meta( $post_id, $post ) {
	// Return if the user doesn't have edit permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}
	// Verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times.
	if ( ! isset( $_POST['location'] ) || ! wp_verify_nonce( $_POST['event_fields'], basename(__FILE__) ) ) {
		return $post_id;
	}
	// Now that we're authenticated, time to save the data.
	// This sanitizes the data from the field and saves it into an array $events_meta.
	$events_meta['location'] = esc_textarea( $_POST['location'] );
	// Cycle through the $events_meta array.
	// Note, in this example we just have one item, but this is helpful if you have multiple.
	foreach ( $events_meta as $key => $value ) :
		// Don't store custom data twice
		if ( 'revision' === $post->post_type ) {
			return;
		}
		if ( get_post_meta( $post_id, $key, false ) ) {
			// If the custom field already has a value, update it.
			update_post_meta( $post_id, $key, $value );
		} else {
			// If the custom field doesn't have a value, add it.
			add_post_meta( $post_id, $key, $value);
		}
		if ( ! $value ) {
			// Delete the meta key if there's no value
			delete_post_meta( $post_id, $key );
		}
	endforeach;
}
add_action( 'save_post', 'wpt_save_events_meta', 1, 2 );
?>
