<head>
	<style type="text/css">
		.entry-header.page-header {
			display:none;
		}
	</style>
</head>
<div class="su-posts su-posts-single-post">
	<?php
		// Prepare marker to show only one post
		$first = true;
		// Posts are found
		if ( $posts->have_posts() ) {
			while ( $posts->have_posts() ) :
				$posts->the_post();
				global $post;

				// Show oly first post
				if ( $first ) {
					$first = false;
					?>
					<div id="su-post-<?php the_ID(); ?>" class="su-post">
						<h1 class="su-post-title"><?php the_title(); ?></h1>
						<div class="su-post-meta">
						<?php _e( 'Birt', 'shortcodes-ultimate' ); ?>: <?php the_time( get_option( 'date_format' ) ); ?></div>
						<div class="su-post-content">
							<?php the_content(); ?>
						</div>
						<?php $url = wp_get_attachment_url( get_post_thumbnail_id($post->ID), 'thumbnail' ); ?>
						<img src="<?php echo $url ?>" style="display: block; margin-left: auto; margin-right: auto;"/>
					</div>
					<?php
				}
			endwhile;
		}
		// Posts not found
		else {
			echo '<h4>' . __( 'Posts not found', 'shortcodes-ultimate' ) . '</h4>';
		}
	?>
</div>