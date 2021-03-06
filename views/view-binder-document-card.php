<?php
/**
 * View Binder Document Card
 *
 * If you wish to override this file, you can do so by creating a version in your
 * theme, and using the `MKDO_CARDS_FOR_BINDER_PREFIX . '_view_template_folder` hook
 * to set the right location.
 *
 * @package mkdo\cards_for_binder
 */


global $post;

/**
 * Variables
 *
 * The following variables can be used in this view.
 */
$attr = wp_parse_args(
	(array) $attr,
	array(
		'version'          => '',
		'alternative_text' => '',
		'file_size'        => '',
		'date'             => '',
		'extension'        => '',
		'show_version'     => '',
		'icon'             => '',
		'image'            => '',
		'document_reader'  => '',
	)
);

$document                  = $document_post;
$version                   = esc_html( $attr['version'] );
$alternative_text          = esc_html( $attr['alternative_text'] );
$show_file_size            = 'true' === $attr['file_size'] ? true : false;
$show_file_date            = 'true' === $attr['date'] ? true : false;
$show_file_extension       = 'true' === $attr['extension'] ? true : false;
$show_file_version         = 'true' === $attr['show_version'] ? true : false;
$show_file_icon            = 'true' === $attr['icon'] ? true : false;
$show_file_image           = 'true' === $attr['image'] ? true : false; // Only used on card-list and card-grid views.
$show_document_reader_text = 'true' === $attr['document_reader'] ? true : false; // The text is set in the plugin settings.
$show_image                = ( isset( $attr['show_image'] ) && true === $attr['show_image'] ) ? true : false;

if ( empty( $document ) ) {
	return;
}

$document = \mkdo\binder\Binder::get_latest_document_by_post_id( $document_post->ID );
if ( ! empty( $version ) ) {
	$document = \mkdo\binder\Binder::get_document_by_version( $document_post->ID, $version );
}
$name     = $document_post->post_title;
$link     = get_the_permalink( $document_post->ID );
$excerpt  = get_the_excerpt( $document );
$size     = $document->size;
$image    = $document->get_thumbnail( $document->binder_id, 'large' );
$uploaded = $document->upload_date;
$uploads  = wp_upload_dir();
$type     = '';
$icon     = '';
$term     = wp_get_object_terms( $document_post->ID, 'binder_type' );

if ( ! empty( $term ) ) {
	$term = $term[0];
	$type = $term->name;
	$icon = get_term_meta( $term->term_id, MKDO_BINDER_PREFIX . '_type_icon', true );
}

// Term fallback.
if ( empty( $term ) ) {
	$type = $document->type;
	$term = get_term_by( 'slug', $type, 'binder_type' );
	if ( ! empty( $term ) ) {
		$term = $term;
		$type = $term->name;
		$icon = get_term_meta( $term->term_id, MKDO_BINDER_PREFIX . '_type_icon', true );
	}
}

$document_meta = array(

	'post_id' => $document_post->ID,
	'name'    => $name,
	'link'    => $link,
	'excerpt' => $excerpt,
	'size'    => $size,
	'image'   => $image,
	'icon'    => $icon,
	'type'    => $type,
	'date'    => $uploaded,
);

/**
 * Output
 *
 * Here is the HTML output, this can be styled however.
 * Do not alter this file, instead duplicate it into your theme.
 */
if ( ! empty( $document_meta ) ) {
	$version_meta = '';
	$date_meta    = '';
	$meta         = '';
	$query_string = '';

	if ( 'latest' !== $version ) {
		$query_string = '?v=' . $version;
	}

	$image_src = get_the_post_thumbnail_url( $document_meta['post_id'], 'large' );
	if ( empty( $image_src ) ) {
		$image_src = $document_meta['image'];
	}

	if ( ! $show_image ) {
		$image_src = '';
	}

	$document_class = 'binder-link';
	$document_class = apply_filters( MKDO_BINDER_PREFIX . '_document_link_class', $document_class, $document_meta['post_id'] );

	$excerpt = mkdo\cards_for_binder\Helper::the_excerpt( $document_meta['post_id'] );
	?>
	<div class="c-binder-card-wrapper">
		<article class="o-block o-block--left | c-binder-card">
			<?php
			if ( ! empty( $image_src ) ) {
				?>
				<div class="o-block__img" style="background-image: url('<?php echo esc_url( $image_src );?>');">
				</div>
				<?php
			}
			?>
			<div class="o-block__body">
				<h1 class="c-binder-card__title">
					<a href="<?php echo esc_url( $document_meta['link'] . $query_string );?>" class="c-binder-card-link | c-binder-link <?php echo esc_attr( $document_class );?>">
						<?php
						if ( ! empty( $alternative_text ) ) {
							echo esc_html( $alternative_text );
						} else {
							echo esc_html( $document_meta['name'] );
						}
						?>
					</a>
				</h1>
				<?php
				if ( $show_file_date || $show_file_icon || $show_file_extension || $show_file_size || ( $show_file_version && 'latest' !== $version ) ) {
				?>
				<ul class="c-binder-card__meta">
					<?php
					if ( $show_file_date ) {
					?>
					<li class="c-binder-card__meta-item c-binder-card__meta-item--date">
						<i class="fa fa-calendar">
							<span class="u-hidden-visually | sr-only">
								<?php esc_html_e( 'Date', 'binder' );?>
							</span>
						</i>
						<?php
						$date = $document_meta['date'];
						$date = DateTime::createFromFormat( 'Y-m-d H:i:s', $date );
						echo ' ' . esc_html( date_format( $date, 'jS F Y' ) );
						?>
					</li>
					<?php
					}
					if ( $show_file_icon || $show_file_extension || $show_file_size ) {
					?>
					<li class="c-binder-card__meta-item c-binder-card__meta-item--file">
					<?php
					if ( $show_file_icon ) {
						?>
						<i class="fa fa-<?php echo esc_attr( $document_meta['icon'] );?>">
							<span class="u-hidden-visually | sr-only">
								<?php echo esc_html( $document_meta['type'] );?>
							</span>
						</i>
						<?php
					}
					if ( $show_file_extension ) {
						echo esc_html( $document_meta['type'] );
					}
					if ( $show_file_size ) {
						echo ' (' . esc_html( $document_meta['size'] ) . ')';
					}
					?>
					</li>
					<?php
					}
					if ( $show_file_version && 'latest' !== $version ) {
					?>
					<li class="c-binder-card__meta-item c-binder-card__meta-item--version">
						<i class="fa fa-code-fork">
							<span class="u-hidden-visually | sr-only">
								<?php esc_html_e( 'Version', 'binder' );?>
							</span>
						</i>
						<?php echo ' ' . esc_html( $version ); ?>
					</li>
					<?php
					}
					?>
				</ul>
				<?php
				}
				?>
				<div class="c-binder-card__excerpt">
					<?php echo wp_kses_post( apply_filters( 'the_content', $excerpt ) );?>
				</div>
				<div class="c-binder-card__cta">
					<a href="<?php echo esc_url( $document_meta['link'] . $query_string );?>" class="c-btn c-binder-card-link | c-binder-link <?php echo esc_attr( $document_class );?>">
						<?php esc_html_e( 'Download', 'binder' );?>
						<i class="fa fa-download"></i>
						<span class="u-hidden-visually | sr-only"> - <?php echo esc_html( $document_meta['name'] );?></span>
					</a>
				</div>
			</div>
		</article>
	</div>
	<?php
}
