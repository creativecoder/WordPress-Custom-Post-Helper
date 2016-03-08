<?php
/*
Plugin Name: Custom Post Type Helper
Plugin URI:  https://github.com/creativecoder/WordPress-Custom-Post-Helper
Description: Helper for creating custom post types
Version:     0.2.6
Author:      Grant Kinney
Author URI:  https://grant.mk
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: custom-post-helper
*/

/**
 * Create and register custom post types and taxonomies
 *
 * Example: `$post = new Custom_Post_Type( $name, $args, $labels );`
 *
 * @package WordPress
 */
class Custom_Post_Type {
	/**
	 * Programatic name of post type
	 * @var string
	 */
	public $post_type_name;

	/**
	 * Post type settings
	 * @var array
	 */
	public $post_type_args;

	/**
	 * Post type labels
	 * @var array
	 */
	public $post_type_labels;

	/**
	 * Create a new custom post type
	 *
	 * @param string $name
	 * @param array  $args   Settings
	 * @param array  $labels
	 */
	public function __construct( $name, $args = array(), $labels = array() ) {
		// Set some important variables
		$this->post_type_name   = self::uglify( $name );
		$this->post_type_args   = $args;
		$this->post_type_labels = $labels;

		// Add action to register the post type, if the post type doesn't exist
		if ( !post_type_exists( $this->post_type_name ) ) {
			add_action( 'init', array( &$this, 'register_post_type' ) );

			// Add custom messages
			add_filter('post_updated_messages', array( &$this, 'set_messages' ) );
		}
	}

	/**
	 * Register the custom post type
	 * @return void
	 */
	public function register_post_type() {
		// Capitalize the words and make it plural
		$name   = self::beautify( $this->post_type_name );
		$plural = self::pluralize( $name );
		$slug   = self::slugify( $this->post_type_name );

		// We set the default labels based on the post type name and plural. We overwrite them with the given labels.
		$labels = array_merge(

			// Default
			array(
				// General name for the post type, usually plural. The same and overridden
				// by $post_type_object->label. Default is Posts/Pages
				'name'                  => sprintf( _x( '%s', 'post type general name', 'custom-post-helper' ), $plural ), 

				// name for one object of this post type. Default is Post/Page
				'singular_name'         => sprintf( _x( '%s', 'post type singular name', 'custom-post-helper' ), $name ),

				// Default is Add New for both hierarchical and non-hierarchical types.
				// When' internationalizing this string, please use a gettext context
				// @link https://codex.wordpress.org/I18n_for_WordPress_Developers#Disambiguation_by_context}
				// matching your post type. Example: `_x( 'Add New', 'product' );`.
				'add_new'               => _x( 'Add New', 'add new custom post type', 'custom-post-helper' ),

				// Default is Add New Post/Add New Page.
				'add_new_item'          => sprintf( _x( 'Add New %s', 'add new custom post type', 'custom-post-helper' ), $name ),

				// Default is Edit Post/Edit Page.
				'edit_item'             => sprintf( _x( 'Edit %s', 'edit custom post type', 'custom-post-helper' ), $name ),

				// Default is New Post/New Page.
				'new_item'              => sprintf( _x( 'New %s', 'create new custom post type', 'custom-post-helper' ), $name ),

				// Default is View Post/View Page.
				'view_item'             => sprintf( _x( 'View %', 'view custom post type', 'custom-post-helper' ), $name ),

				// Default is Search Posts/Search Pages.
				'search_items'          => sprintf( _x( 'Search %s', 'search custom post type', 'custom-post-helper' ), $plural ),

				// Default is No posts found/No pages found.
				'not_found'             => sprintf( _x( 'No %s found', 'no custom post types found', 'custom-post-helper' ), $plural ),

				// Default is No posts found in Trash/No pages found in Trash.
				'not_found_in_trash'    => sprintf( _x( 'No %s found in Trash', 'no custom post types found', 'custom-post-helper' ), $plural ),

				// This string isn't used on non-hierarchical types. In hierarchical ones the default is 'Parent Page:'.
				'parent_item_colon'     => sprintf( _x( 'Parent %s', 'custom post type name', 'custom-post-helper' ), $name ),

				// String for the submenu. Default is All Posts/All Pages.
				'all_items'             => sprintf( _x( 'All %s', 'custom post type plural name', 'custom-post-helper' ), $plural ),

				// String for use with archives in nav menus. Default is Post Archives/Page Archives.
				'archives'              => sprintf( _x( '%s Archives', 'custom post type archives', 'custom-post-helper' ), $name ),

				// String for the media frame button. Default is Insert into post/Insert into page.
				'insert_into_item'      => sprintf( _x( 'Insert into %s', 'insert into custom post type', 'custom-post-helper' ), $name ),

				// String for the media frame filter. Default is Uploaded to this post/Uploaded to this page.
				'uploaded_to_this_item' => sprintf( _x( 'Uploaded to this %s', 'uploaded to custom post type', 'custom-post-helper' ), $name ),

				// Default is Featured Image.
				'featured_image'        => __( 'Featured Image', 'custom-post-helper' ),

				// Default is Set featured image.
				'set_featured_image'    => __( 'Set featured image', 'custom-post-helper' ),

				// Default is Remove featured image.
				'remove_featured_image' => __( 'Remove featured image', 'custom-post-helper' ),

				// Default is Use as featured image.
				'use_featured_image'    => __( 'Use as featured image', 'custom-post-helper' ),

				// Default is the same as `name`.
				'menu_name'             => sprintf( _x( '%s', 'post type plural name', 'custom-post-helper' ), $plural ),

				// String for the table views hidden heading.
				'filter_items_list'     => sprintf( _x( 'Filter %s list', 'filter custom post type list', 'custom-post-helper' ), $plural ),

				// String for the table pagination hidden heading.
				'items_list_navigation' => sprintf( _x( '%s list navigation', 'custom post type list navigation', 'custom-post-helper' ), $plural ),

				// String for the table hidden heading.
				'items_list'            => sprintf( _x( '%s list', 'custom post type list', 'custom-post-helper' ), $plural ),

				// String for use in New in Admin menu bar. Default is the same as `singular_name`.
				'name_admin_bar'        => _x( $name, 'post type singular name', 'custom-post-helper' ),
			),

			// Given labels
			$this->post_type_labels

		);

		// Same principle as the labels. We set some default and overwrite them with the given arguments.
		$args = array_merge(

			// Default
			array(
				// A plural descriptive name for the post type marked for translation.
				// Default: Value of $labels['name']
				// 'label' => $labels['name'],

				// An array of labels for this post type. By default, post labels are used for non-hierarchical post types and page labels for hierarchical ones.
				// Default: if empty, 'name' is set to value of 'label', and 'singular_name' is set to value of 'name'.
				'labels' => $labels,

				// A short descriptive summary of what the post type is.
				// Default: blank
				// 'description'       => '',

				// Controls how the type is visible to authors (show_in_nav_menus, show_ui) and readers (exclude_from_search, publicly_queryable).
				// Default: false
				'public' => true,

				// Whether to exclude posts with this post type from front end search results.
				// Default: value of the opposite of public argument
				// 'exclude_from_search' => false,

				// Whether queries can be performed on the front end as part of parse_request().
				// Default: value of public argument
				// 'publicly_queryable' => true,

				// Whether to generate a default UI for managing this post type in the admin.
				// Default: value of public argument
				// 'show_ui'           => true,

				// Whether post_type is available for selection in navigation menus.
				// Default: value of public argument
				// 'show_in_nav_menus' => true,

				// Where to show the post type in the admin menu. show_ui must be true.
				// Default: value of show_ui argument
				// 'show_in_menu' => true,

				// Whether to make this post type available in the WordPress admin bar.
				// Default: value of the show_in_menu argument
				// 'show_in_admin_bar' => true,

				// The position in the menu order the post type should appear. show_in_menu must be true.
				// Default: null - defaults to below Comments
				// 'menu_position'     => null,

				// The url to the icon to be used for this menu or the name of the icon from the iconfont [1]
				// Default: null - defaults to the posts icon
				// 'menu_icon'         => null,

				// The string to use to build the read, edit, and delete capabilities. Use an array to specify singular and plural.
				// Default: "post"
				// 'capability_type' => array( strtolower( $name ), strtolower( $plural ) ),

				// An array of the capabilities for this post type.
				// Default: capability_type is used to construct
				// 'capabilities'      => array(
				// 	'edit_post'              =>              'edit_' . strtolower( $name ),
				// 	'read_post'              =>              'read_' . strtolower( $name ),
				// 	'delete_post'            =>            'delete_' . strtolower( $name ),
				// 	'edit_posts'             =>             'edit_' . strtolower( $plural ),
				// 	'edit_others_posts'      =>      'edit_others_' . strtolower( $plural ),
				// 	'publish_posts'          =>          'publish_' . strtolower( $plural ),
				// 	'read_private_posts'     =>     'read_private_' . strtolower( $plural ),
				// 	'delete_posts'           =>           'delete_' . strtolower( $plural ),
				// 	'delete_private_posts'   =>   'delete_private_' . strtolower( $plural ),
				// 	'delete_published_posts' => 'delete_published_' . strtolower( $plural ),
				// 	'delete_others_posts'    =>    'delete_others_' . strtolower( $plural ),
				// 	'edit_private_posts'     =>     'edit_private_' . strtolower( $plural ),
				// 	'edit_published_posts'   =>   'edit_published_' . strtolower( $plural ),
				// 	'create_posts'           =>           'create_' . strtolower( $plural ),
				// ),

				// Whether to use the internal default meta capability handling.
				// Default: null
				// 'map_meta_cap' => true,

				// Whether the post type is hierarchical (e.g. page). Allows Parent to be specified. The 'supports' parameter should contain 'page-attributes' to show the parent select box on the editor page.
				// Default: false
				// 'hierarchical' => false,

				// An alias for calling add_post_type_support() directly. As of 3.5, boolean false can be passed as value instead of an array to prevent default (title and editor) behavior.
				// Default: title and editor
				// 'supports' => array( 'title', 'editor' ),

				// Provide a callback function that will be called when setting up the meta boxes for the edit form. The callback function takes one argument $post, which contains the WP_Post object for the currently edited post. Do remove_meta_box() and add_meta_box() calls in the callback.
				// Default: None
				// 'register_meta_box_cb' => null,

				// An array of registered taxonomies like category or post_tag that will be used with this post type. This can be used in lieu of calling register_taxonomy_for_object_type() directly. Custom taxonomies still need to be registered with register_taxonomy().
				// Default: no taxonomies
				// 'taxonomies' => null,

				// Enables post type archives. Will use $post_type as archive slug by default.
				// Default: false
				'has_archive' => true,

				// Triggers the handling of rewrites for this post type. To prevent rewrites, set to false.
				// Default: true and use $post_type as slug
				'rewrite' => array(
					// Customize the permalink structure slug. Defaults to the $post_type value. Should be translatable.
					'slug' => sprintf( _x( '%s', 'custom post type slug for url', 'custom-post-helper' ), $slug ),

					// Should the permalink structure be prepended with the front base. (example: if your permalink structure is /blog/, then your links will be: false->/news/, true->/blog/news/). Defaults to true
					// 'with_front' => true,

					// Should a feed permalink structure be built for this post type. Defaults to has_archive value.
					// 'feeds' => true,

					// Should the permalink structure provide for pagination. Defaults to true
					// 'pages' => true,

					// Assign an endpoint mask for this post type.
					// 'ep_mask' => null,
				),

				// Sets the query_var key for this post type.
				// Default: true - set to $post_type
				// 'query_var' => $slug,

				//  Can this post_type be exported.
				// Default: true
				// 'can_export' => true,

				// Whether to expose this post type in the REST API.
				// Default: false
				// 'show_in_rest' => false,

				// The base slug that this post type will use when accessed using the REST API.
				// Default: $post_type
				// 'rest_base' => $slug,

				// An optional custom controller to use instead of WP_REST_Posts_Controller. Must be a subclass of WP_REST_Controller.
				// Default: WP_REST_Posts_Controller
				// 'rest_controller_class' => 'WP_REST_Posts_Controller',

				// Whether this post type is a native or "built-in" post_type. Note: this Codex entry is for documentation - core developers recommend you don't use this when registering your own post type
				// '_builtin' => false,

				// Link to edit an entry with this post type. Note: this Codex entry is for documentation - core developers recommend you don't use this when registering your own post type
				// '_edit_link' => 'post.php?post=%d',
			),

			// Given args
			$this->post_type_args

		);

		// Register the post type
		register_post_type( $this->post_type_name, $args );
	}
	
	/**
	 * Custom status messages in wp-admin using post type name
	 *
	 * @param array $messages Set of status messages for actions on a post (updating, scheduling, etc)
	 * @return array          Filtered array of messages
	 */
	public function set_messages( $messages ) {
		// Get post object and post ID
		global $post, $post_ID;
		
		// Capitalize the words and make it plural
		$name = self::beautify( $this->post_type_name );
		
		$messages[$this->post_type_name] = array(
			0 => '', // Unused. Messages start at index 1.
			1 => sprintf(
				__( '%1$s updated. <a href="%2$s">View %1$s</a>', 'custom-post-helper' ),
				$name,
				esc_url( get_permalink( $post_ID ) )
			),
			2 => __( 'Custom field updated.', 'custom-post-helper' ),
			3 => __( 'Custom field deleted.', 'custom-post-helper' ),
			4 => sprintf( _x( '%s updated.', 'custom post type updated', 'custom-post-helper' ), $name ),
			5 => isset( $_GET['revision'] ) ? sprintf( __( '%s restored to revision from %s', 'custom-post-helper' ), $name, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
			6 => sprintf( __( '%s published. <a href="%s">View %s</a>', 'custom-post-helper' ), $name, esc_url( get_permalink( $post_ID ) ), strtolower( $name ) ),
			7 => sprintf( __( '%s saved.', 'custom-post-helper' ), $name ),
			8 => sprintf( __( '%s submitted. <a target="_blank" href="%s">Preview %s</a>', 'custom-post-helper' ), $name, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), strtolower( $name ) ),
			9 => sprintf( __( '%s scheduled for: <strong>%s</strong>. <a target="_blank" href="%s">Preview %s</a>', 'custom-post-helper' ), $name, date_i18n( __( 'M j, Y @ G:i', 'custom-post-helper' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ), strtolower( $name ) ),
			10 => sprintf( __( '%s draft updated. <a target="_blank" href="%s">Preview %s</a>', 'custom-post-helper' ), $name, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), strtolower( $name ) ),
		);

		return $messages;
	}

	/**
	 * Associate a taxonomy to the custom post type
	 *
	 * @param string $name   Name of taxonomy to add
	 * @param array  $args   Settings for the taxonomy, used if it isn't a built in taxonomy and hasn't already been registered
	 * @param array  $labels Labels for the taxonomy, used if it isn't a built in taxonomy and hasn't already been registered
	 */
	public function add_taxonomy( $name, $args = array(), $labels = array() ) {
		if ( $name ) {
			// We need to know the post type name, so the new taxonomy can be attached to it.
			$post_type_name = $this->post_type_name;

			// Taxonomy properties
			$taxonomy_name   = self::uglify( $name );
			$taxonomy_labels = $labels;
			$taxonomy_args   = $args;

			if ( ! taxonomy_exists( $taxonomy_name ) ) {
				//Capitilize the words and make it plural
				$name	= self::beautify( $name );
				$plural	= self::pluralize( $name );

				// Default labels, overwrite them with the given labels.
				$labels = array_merge(
					// Default
					array(
						// General name for the taxonomy, usually plural. The same as and overridden by $tax->label.
						// Default is _x( 'Post Tags', 'taxonomy general name' ) or _x( 'Categories', 'taxonomy general name' ).
						// When internationalizing this string, please use a gettext context matching your post type. Example: _x('Writers', 'taxonomy general name');
						'name' => sprintf( _x( '%s', 'taxonomy general name', 'custom-post-helper' ), $plural ),

						// Name for one object of this taxonomy.
						// Default is _x( 'Post Tag', 'taxonomy singular name' ) or _x( 'Category', 'taxonomy singular name' ).
						// When internationalizing this string, please use a gettext context matching your post type. Example: _x('Writer', 'taxonomy singular name');
						'singular_name' => sprintf( _x( '%s', 'taxonomy singular name', 'custom-post-helper' ), $name ),

						// The menu name text. This string is the name to give menu items. If not set, defaults to value of name label.
						'menu_name' => sprintf( _x( '%s', 'taxonomy general name', 'custom-post-helper' ), $plural ),

						// The all items text.
						// Default is __( 'All Tags' ) or __( 'All Categories' )
						'all_items' => sprintf( _x( 'All %s', 'all taxonomy terms', 'custom-post-helper' ), $plural ),

						// the edit item text.
						// Default is __( 'Edit Tag' ) or __( 'Edit Category' )
						'edit_item' => sprintf( _x( 'Edit %s', 'edit taxonomy term', 'custom-post-helper' ), $name ),

						// The view item text.
						// Default is __( 'View Tag' ) or __( 'View Category' )
						'view_item' => sprintf( _x( 'View %s', 'view taxonomy term', 'custom-post-helper' ), $name ),

						// The update item text.
						// Default is __( 'Update Tag' ) or __( 'Update Category' )
						'update_item' => sprintf( _x( 'Update %s', 'taxonomy term', 'custom-post-helper'), $name ),

						// The add new item text.
						// Default is __( 'Add New Tag' ) or __( 'Add New Category' )
						'add_new_item' => sprintf( _x( 'Add New %s', 'add new taxonomy term', 'custom-post-helper'), $name ),

						// The new item name text.
						// Default is __( 'New Tag Name' ) or __( 'New Category Name' )
						'new_item_name' => sprintf( _x( 'New %s Name', 'taxonomy term', 'custom-post-helper'), $name ),
						
						// The parent item text. This string is not used on non-hierarchical taxonomies such as post tags.
						// Default is null or __( 'Parent Category' )
						'parent_item' => sprintf( _x( 'Parent %s', 'parent taxonomy term', 'custom-post-helper' ), $name ),
						
						// The same as parent_item, but with colon : in the end null, __( 'Parent Category:' )
						'parent_item_colon' => sprintf( _x( 'Parent %s:', 'parent taxonomy term', 'custom-post-helper' ), $name ),

						// The search items text.
						// Default is __( 'Search Tags' ) or __( 'Search Categories' )
						'search_items' => sprintf( _x( 'Search %s', 'search taxonomy', 'custom-post-helper' ), $plural ),

						// The popular items text. This string is not used on hierarchical taxonomies.
						// Default is __( 'Popular Tags' ) or null
						'popular_items' => sprintf( _x( 'Popular %s', 'popular taxonomy terms', 'custom-post-helper' ), $plural ),

						// The separate item with commas text used in the taxonomy meta box. This string is not used on hierarchical taxonomies.
						// Default is __( 'Separate tags with commas' ), or null
						'separate_items_with_commas' => sprintf( _x( 'Separate %s with commas', 'separate taxonomy terms with commas', 'custom-post-helper' ), strtolower( $plural ) ),
						
						// The add or remove items text and used in the meta box when JavaScript is disabled. This string is not used on hierarchical taxonomies.
						// Default is __( 'Add or remove tags' ) or null
						'add_or_remove_items' => sprintf( _x( 'Add or remove %s', 'add or remove taxonomy terms', 'custom-post-helper' ), strtolower( $plural ) ),

						// the choose from most used text used in the taxonomy meta box. This string is not used on hierarchical taxonomies.
						// Default is __( 'Choose from the most used tags' ) or null
						'choose_from_most_used' => sprintf( _x( 'Choose from the most used %s', 'taxonomy terms', 'custom-post-helper' ), strtolower( $plural ) ),

						// the text displayed via clicking 'Choose from the most used tags' in the taxonomy meta box when no tags are available and (4.2+) -
						// the text used in the terms list table when there are no items for a taxonomy.
						// Default is __( 'No tags found.' ) or __( 'No categories found.' )
						'not_found' => sprintf( _x( 'No %s found.', 'taxonomy terms', 'custom-post-helper' ), strtolower( $plural ) ),
					),

					// Given labels
					$taxonomy_labels

				);

				// Default arguments, overwitten with the given arguments
				$args = array_merge(

					// Default
					array(
						// A plural descriptive name for the taxonomy marked for translation.
						// Default: overridden by $labels->name
						// 'label'             => $labels['name'],

						//  An array of labels for this taxonomy. By default tag labels are used for non-hierarchical types and category labels for hierarchical ones.
						// Default: if empty, name is set to label value, and singular_name is set to name value
						'labels'            => $labels,

						// If the taxonomy should be publicly queryable.
						// Default: true
						// 'public'            => true,

						// Whether to generate a default UI for managing this taxonomy.
						// Default: if not set, defaults to value of public argument. As of 3.5, setting this to false for attachment taxonomies will hide the UI.
						// 'show_ui'           => true,
						
						// Where to show the taxonomy in the admin menu. show_ui must be true.
						// Default: value of show_ui argument
						// 'show_in_nav_menus' => true,

						// True makes this taxonomy available for selection in navigation menus.
						// Default: if not set, defaults to value of public argument
						// 'show_in_nav_menus' => true,
						
						// Whether to allow the Tag Cloud widget to use this taxonomy.
						// Default: if not set, defaults to value of show_ui argument
						// 'show_tagcloud' => true,

						// Whether to show the taxonomy in the quick/bulk edit panel. (Available since 4.2)
						// Default: if not set, defaults to value of show_ui argument
						// 'show_in_quick_edit' => true,

						// Provide a callback function name for the meta box display. (Available since 3.8)
						// Default: null
						// 'meta_box_cb' => null,

						// Whether to allow automatic creation of taxonomy columns on associated post-types table. (Available since 3.5)
						// Default: false
						// 'show_admin_column' => false,

						//  Include a description of the taxonomy.
						// Default: ""
						// 'description' => '',
						
						// Is this taxonomy hierarchical (have descendants) like categories or not hierarchical like tags.
						// Default: false
						// 'hierarchical' => false,
						
						// A function name that will be called when the count of an associated $object_type, such as post, is updated. Works much like a hook.
						// Default: None - but see Note, below.
						// 'update_count_callback' => '',

						// False to disable the query_var, set as string to use custom query_var instead of default which is $taxonomy, the taxonomy's "name".
						// Default: $taxonomy
						// 'query_var' => $slug,

						// Set to false to prevent automatic URL rewriting a.k.a. "pretty permalinks". Pass an $args array to override default URL settings for permalinks as outlined below:
						// Default: true
						'rewrite' => array(
							//  Used as pretty permalink text (i.e. /tag/) - defaults to $taxonomy (taxonomy's name slug)
							'slug' => sprintf( _x( '%s', 'taxonomy slug for url', 'custom-post-helper' ), $slug ),

							// Allowing permalinks to be prepended with front base - defaults to true
							// 'with_front' => true,

							// true or false allow hierarchical urls (implemented in Version 3.1) - defaults to false
							// 'hierarchical' => false,

							// Assign an endpoint mask for this post type.
							// 'ep_mask' => null,
						),

						// An array of the capabilities for this taxonomy.
						// Default: None
						// 'capabilities' => array(
						// 	'assign_terms' => 'assign_' . $slug,
						// 	'delete_terms' => 'delete_' . $slug,
						// 	'edit_terms'   => 'edit_' . $slug,
						// 	'manage_terms' => 'manage_' . $slug,
						// ),

						// Whether this taxonomy should remember the order in which terms are added to objects.
						// Default: None
						// 'sort' => false,

						// Whether this taxonomy is a native or "built-in" taxonomy. Note: this Codex entry is for documentation - core developers recommend you don't use this when registering your own taxonomy
						// '_builtin'          => false,
					),
	
					// Given
					$taxonomy_args

				);

				// Add the taxonomy to the post type
				add_action( 'init', function() use( $taxonomy_name, $post_type_name, $args ) {
						register_taxonomy( $taxonomy_name, $post_type_name, $args );
					}
				);
			} else {
				add_action( 'init', function() use( $taxonomy_name, $post_type_name ) {
						register_taxonomy_for_object_type( $taxonomy_name, $post_type_name );
					}
				);
			}
		}
	}

	/**
	 * Convert text string to be human readable (convert `_` to spaces)
	 * 
	 * @param  string $string Text to beautify
	 * @return string         Beautified text
	 */
	public static function beautify( $string ) {
		return ucwords( str_replace( '_', ' ', $string ) );
	}
	
	/**
	 * Convert text string to be program readable
	 *
	 * @param  string $string Text to uglify
	 * @return string         Uglified text
	 */
	public static function uglify( $string ) {
		return strtolower( str_replace( ' ', '_', $string ) );
	}

	/**
	 * Convert word into plural form
	 *
	 * @param  string $string Singular text
	 * @return string         Plural text
	 */
	public static function pluralize( $string ) {
		$last = substr( $string, -1 );
		$nextlast = substr( $string, -2, 1 );
		$vowels = array( 'a', 'e', 'i', 'o', 'u' );

		if ( $last == 'y' && ! in_array( $nextlast, $vowels ) ) {
			$cut = substr( $string, 0, -1 );
			// remove "y" and add "ies"
			$plural = $cut . 'ies';
		} else {
			// just attach an s
			$plural = $string . 's';
		}

		return $plural;
	}

	/**
	 * Convert text to be used in uri (convert `_` to `-` and pluralize it)
	 *
	 * @param  string $string Programatic text
	 * @return string         Text for uri
	 */
	public static function slugify( $string ) {
		$string = strtolower( str_replace( '_', '-', $string ) );
		
		return self::pluralize( $string );
	}
}

add_action( 'admin_init', function () {
	if ( ! class_exists('WP_GitHub_Updater') ) {
		include_once( 'WordPress-GitHub-Plugin-Updater/updater.php' );
	}

	if ( ! defined( 'WP_GITHUB_FORCE_UPDATE' ) ) {
		define( 'WP_GITHUB_FORCE_UPDATE', true );
	}

	$config = array(
		'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
		'proper_folder_name' => 'custom-post-type-helper', // this is the name of the folder your plugin lives in
		'api_url' => 'https://api.github.com/repos/creativecoder/WordPress-Custom-Post-Helper', // the GitHub API url of your GitHub repo
		'raw_url' => 'https://raw.github.com/creativecoder/WordPress-Custom-Post-Helper/master', // the GitHub raw url of your GitHub repo
		'github_url' => 'https://github.com/creativecoder/WordPress-Custom-Post-Helper', // the GitHub url of your GitHub repo
		'zip_url' => 'https://github.com/creativecoder/WordPress-Custom-Post-Helper/zipball/master', // the zip url of the GitHub repo
		'sslverify' => true, // whether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
		'requires' => '3.0', // which version of WordPress does your plugin require?
		'tested' => '4.4', // which version of WordPress is your plugin tested up to?
		'readme' => 'readme.md', // which file to use as the readme for the version number
		'access_token' => '', // Access private repositories by authorizing under Appearance > GitHub Updates when this example plugin is installed
	);

	new WP_GitHub_Updater( $config );
});
