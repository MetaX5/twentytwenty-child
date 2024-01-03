<?php

/**
 * The Template for displaying books in a genre.
 */
get_header();
?>

<main class="genre">
	<div class="genre__wrapper">
		<?php
		$queried_object = get_queried_object();
		$term_id = $queried_object->term_id;
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

		$args = [
			'orderby' => 'date',
			'post_type' => 'Books',
			'post_status' => 'publish',
			'paged' => $paged,
			'tax_query' => [
				[
					'taxonomy' => 'Genre',
					'field' => 'term_id',
					'terms' => $term_id,
				],
			],
		];


		$the_query = new WP_Query($args);
		if ($the_query->have_posts()) : ?>
			<?php while ($the_query->have_posts()) :
				$the_query->the_post(); ?>
				<div class="genre__wrapper_item">
					<a href="<?php the_permalink(); ?>">
						<?php the_post_thumbnail('thumbnail'); ?>
						<h2 class="genre__wrapper_title">
							<?php the_title(); ?>
						</h2>
					</a>
				</div>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		<?php else : ?>
			<p>Sorry, there are no genres to display</p>
		<?php endif; ?>
	</div>
	<div class="pagination">
		<?php
		$big = 999999999;
		echo paginate_links([
			'base' => str_replace($big, '%#%', get_pagenum_link($big)),
			'format' => '?paged=%#%',
			'current' => max(1, get_query_var('paged')),
			'total' => $the_query->max_num_pages,
			'prev_text' => '&laquo;',
			'next_text' => '&raquo;',
		]);
		?>
	</div>

</main>

<?php
get_footer();
