<?php

/**
 * The Template for displaying single book.
 */
get_header();
?>

<main class="library">
	<div class="library__wrapper_left_date">
		Published At
		<?php echo get_the_date(); ?>
	</div>
	<div class="library__wrapper_left">
		<div class="library__wrapper_left_image">
			<?php
			if (has_post_thumbnail()) {
				the_post_thumbnail('medium');
			}
			?>
		</div>
	</div>
	<div class="library__wrapper_right">
		<h1 class="library__wrapper_right_title">
			<?php the_title(); ?>
		</h1>
		<p class="library__wrapper_right_genre">
			<?php echo get_the_term_list(get_the_ID(), 'Genre', 'Genre: ', ', '); ?>
		</p>
	</div>
</main>


<?php
get_footer();
