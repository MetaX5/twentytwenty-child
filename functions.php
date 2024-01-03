<?php

add_action('wp_enqueue_scripts', 'twentytwenty_child_enqueue_styles');
function twentytwenty_child_enqueue_styles() {
	wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
	wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/assets/css/styles.css');
}

add_action('wp_enqueue_scripts', 'my_scripts_method');
function my_scripts_method() {
	wp_enqueue_script(
		'custom-script',
		get_stylesheet_directory_uri() . '/assets/js/scripts.js',
		['jquery'],
		null,
		true
	);
	wp_localize_script(
		'custom-script',
		'frontend_ajax_object',
		[
			'ajaxurl' => admin_url('admin-ajax.php'),
		]
	);
}

function custom_tax_query_change($query) {
	if (!is_admin() && $query->is_tax('Genre')) {
		$query->set('posts_per_page', 5);
	}
}
add_action('pre_get_posts', 'custom_tax_query_change');

add_action('init', 'mm_register_post_type_and_taxonomy');
function mm_register_post_type_and_taxonomy() {
	$post_type_args = [
		'label' => esc_html__('Books', 'twentytwenty-child'),
		'labels' => [
			'menu_name' => esc_html__('Books', 'twentytwenty-child'),
			'name_admin_bar' => esc_html__('Books', 'twentytwenty-child'),
			'add_new' => esc_html__('Add Book', 'twentytwenty-child'),
			'add_new_item' => esc_html__('Add new Book', 'twentytwenty-child'),
			'new_item' => esc_html__('New Book', 'twentytwenty-child'),
			'edit_item' => esc_html__('Edit Book', 'twentytwenty-child'),
			'view_item' => esc_html__('View Book', 'twentytwenty-child'),
			'update_item' => esc_html__('View Book', 'twentytwenty-child'),
			'all_items' => esc_html__('All Books', 'twentytwenty-child'),
			'search_items' => esc_html__('Search Books', 'twentytwenty-child'),
			'parent_item_colon' => esc_html__('Parent Book', 'twentytwenty-child'),
			'not_found' => esc_html__('No Books found', 'twentytwenty-child'),
			'not_found_in_trash' => esc_html__('No Books found in Trash', 'twentytwenty-child'),
			'name' => esc_html__('Books', 'twentytwenty-child'),
			'singular_name' => esc_html__('Book', 'twentytwenty-child'),
		],
		'public' => true,
		'exclude_from_search' => false,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_nav_menus' => true,
		'show_in_admin_bar' => true,
		'show_in_rest' => true,
		'capability_type' => 'post',
		'hierarchical' => false,
		'has_archive' => true,
		'query_var' => true,
		'can_export' => true,
		'rewrite_no_front' => false,
		'show_in_menu' => true,
		'menu_position' => 5,
		'menu_icon' => 'dashicons-book',
		'supports' => [
			'title',
			'editor',
			'thumbnail',
			'excerpt',
		],
		'taxonomies' => [
			'Genre',
		],
		'rewrite' => ['slug' => 'library'],
	];

	$taxonomy_args =
		[
			'label' => esc_html__('Genres', 'twentytwenty-child'),
			'hierarchical' => true,
			'rewrite' => ['slug' => 'book-genre'],
			'show_admin_column' => true,
			'show_in_rest' => true,
			'labels' => [
				'singular_name' => esc_html__('Genre', 'twentytwenty-child'),
				'all_items' => esc_html__('All Genres', 'twentytwenty-child'),
				'edit_item' => esc_html__('Edit Genre', 'twentytwenty-child'),
				'view_item' => esc_html__('View Genre', 'twentytwenty-child'),
				'update_item' => esc_html__('Update Genre', 'twentytwenty-child'),
				'add_new_item' => esc_html__('Add New Genre', 'twentytwenty-child'),
				'new_item_name' => esc_html__('New Genre Name', 'twentytwenty-child'),
				'search_items' => esc_html__('Search Genres', 'twentytwenty-child'),
				'parent_item' => esc_html__('Parent Genre', 'twentytwenty-child'),
				'parent_item_colon' => esc_html__('Parent Genre:', 'twentytwenty-child'),
				'not_found' => esc_html__('No Genres found', 'twentytwenty-child'),
			],
		];
	register_taxonomy('Genre', ['Books'], $taxonomy_args);
	register_post_type('Books', $post_type_args);
}

add_shortcode('recent_book', 'recent_book');
function recent_book() {
	$args = [
		'post_type' => 'Books',
		'posts_per_page' => 1,
	];
	$recent_post = wp_get_recent_posts($args, OBJECT);

	return $recent_post[0]->post_title;
}

add_shortcode('books_by_genre', 'books_by_genre');
function books_by_genre($atts) {
	if (empty($atts['genre'])) {
		return 'Please specify a genre. Example: [books_by_genre genre="`GENRE_ID`"].';
	}

	$args = [
		'post_type' => 'Books',
		'posts_per_page' => 5,
		'orderby' => 'post_title',
		'order' => 'ASC',
		'tax_query' => [
			[
				'taxonomy' => 'Genre',
				'field' => 'term_id',
				'terms' => $atts['genre'],
			],
		],
	];

	$books = '<ul>';

	$the_query = new WP_Query($args);
	if ($the_query->have_posts()) {
		while ($the_query->have_posts()) {
			$the_query->the_post();
			$books .= '<li>' . get_the_title() . '</li>';
		}
		$books .= '</ul>';
	} else {
		$books = 'Sorry, there are no books in this genre.';
	}
	wp_reset_postdata();

	return $books;
}

function get_ajax_posts() {
	$args = [
		'post_type' => 'Books',
		'posts_per_page' => 20,
	];

	$output = [];

	$the_query = new WP_Query($args);

	if ($the_query->have_posts()) {
		while ($the_query->have_posts()) {
			$the_query->the_post();
			$terms = get_the_terms(get_the_ID(), 'Genre');
			$genres = [];
			foreach ($terms as $term) {
				array_push($genres, $term->name);
			}
			array_push($output, [
				'name' => get_the_title(),
				'date' => get_the_date(),
				'genre' => $genres,
				'excerpt' => get_the_excerpt(),
			]);
		}
	} else {
		$output = 'Sorry, there are no books.';
	}
	wp_reset_postdata();

	echo json_encode($output);

	exit;
}

// Fire AJAX action for both logged in and non-logged in users
add_action('wp_ajax_get_ajax_posts', 'get_ajax_posts');
add_action('wp_ajax_nopriv_get_ajax_posts', 'get_ajax_posts');
