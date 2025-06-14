<?php
/**
 * Plugin Name:       Tomatillo Design ~ Yak Herd
 * Description:       Quickly bulk-create posts, pages, or taxonomy terms by pasting plain text — one item per line.
 * Version:           0.1
 * Author:            Chris Liu-Beers @ Tomatillo Design
 * Author URI:        https://www.tomatillodesign.com
 * Text Domain:       yak-herd
 */

defined( 'ABSPATH' ) || exit;

// 🔗 Admin menu page
add_action( 'admin_menu', 'yak_herd_add_admin_page' );
function yak_herd_add_admin_page() {
	add_management_page(
		'Yak Herd',
		'Yak Herd',
		'manage_options',
		'yak-herd',
		'yak_herd_render_admin_page'
	);
}

// 📨 Form submission handler
add_action( 'admin_post_yak_herd_submit', 'yak_herd_handle_form' );

// 📄 Admin page UI
function yak_herd_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have permission to access this page.', 'yak-herd' ) );
	}
	?>
    <div id="yak-herd" class="wrap">
		<h1>Yak Herd</h1>
		<?php yak_herd_show_messages(); ?>
		<p>Paste a list of titles or terms below to create them in bulk.</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'yak_herd_submit', 'yak_herd_nonce' ); ?>
			<input type="hidden" name="action" value="yak_herd_submit" />

			<p>
				<label for="yak_herd_mode"><strong>What are you creating?</strong></label><br>
				<select name="yak_herd_mode" id="yak_herd_mode">
					<option value="terms">Taxonomy Terms</option>
					<option value="posts">Posts / Pages</option>
				</select>
			</p>

            <div class="yak-mode-section yak-mode-terms">
            <p>
                <label for="yak_herd_taxonomy"><strong>Taxonomy:</strong></label><br>
                <select name="yak_herd_taxonomy" id="yak_herd_taxonomy">
                    <?php
                    $taxonomies = get_taxonomies( [ 'public' => true, 'show_ui' => true ], 'objects' );
                    foreach ( $taxonomies as $slug => $tax ) {
                        printf(
                            '<option value="%s">%s</option>',
                            esc_attr( $slug ),
                            esc_html( $tax->labels->name )
                        );
                    }
                    ?>
                </select>
            </p>

            <?php
                $selected_tax = $_POST['yak_herd_taxonomy'] ?? 'category';
                $taxonomies = get_taxonomies( [ 'public' => true, 'show_ui' => true ], 'objects' );

                foreach ( $taxonomies as $slug => $tax ) {
                    if ( ! $tax->hierarchical ) {
                        continue;
                    }

                    $terms = get_terms( [
                        'taxonomy'   => $slug,
                        'hide_empty' => false,
                        'parent'     => 0,
                    ] );
                    ?>
                    <div class="yak-parent-selector" data-taxonomy="<?php echo esc_attr( $slug ); ?>" style="display: none;">
                        <p>
                            <label for="yak_herd_parent_term_<?php echo esc_attr( $slug ); ?>"><strong>Parent Term (optional):</strong></label><br>
                            <select name="yak_herd_parent_term" id="yak_herd_parent_term_<?php echo esc_attr( $slug ); ?>">
                                <option value="">None (top level)</option>
                                <?php foreach ( $terms as $term ) : ?>
                                    <option value="<?php echo esc_attr( $term->term_id ); ?>">
                                        <?php echo esc_html( $term->name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                    </div>
                    <?php
                }
                ?>
                </div>

                <div class="yak-mode-section yak-mode-posts">
                <p>
                    <label for="yak_herd_post_type"><strong>Post Type:</strong></label><br>
                    <select name="yak_herd_post_type" id="yak_herd_post_type">
                        <?php
                        $post_types = get_post_types( [ 'public' => true, 'show_ui' => true ], 'objects' );
                        foreach ( $post_types as $slug => $type ) {
                            if ( $slug === 'attachment' ) {
                                continue;
                            }
                            printf(
                                '<option value="%s">%s</option>',
                                esc_attr( $slug ),
                                esc_html( $type->labels->name )
                            );
                        }
                        ?>
                    </select>
                </p>
                </div>

			<p>
				<label for="yak_herd_text"><strong>Items to create (one per line):</strong></label><br>
				<textarea name="yak_herd_text" id="yak_herd_text" rows="10" cols="60" style="width: 100%; max-width: 600px;"></textarea>
			</p>

			<p>
				<input type="submit" class="button button-primary" value="Create Items">
			</p>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const taxonomySelect = document.getElementById('yak_herd_taxonomy');
                    const parentSelectors = document.querySelectorAll('.yak-parent-selector');

                    function updateParentVisibility() {
                        const selected = taxonomySelect.value;
                        parentSelectors.forEach(div => {
                            div.style.display = (div.dataset.taxonomy === selected) ? 'block' : 'none';
                        });
                    }

                    taxonomySelect.addEventListener('change', updateParentVisibility);
                    updateParentVisibility(); // Run on page load
                });
            </script>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const modeSelect = document.getElementById('yak_herd_mode');
                    const sections = document.querySelectorAll('.yak-mode-section');

                    function updateVisibility() {
                        const mode = modeSelect.value;
                        sections.forEach(section => {
                            const show = section.classList.contains(`yak-mode-${mode}`);
                            section.style.display = show ? 'block' : 'none';
                        });
                    }

                    modeSelect.addEventListener('change', updateVisibility);
                    updateVisibility(); // run on load
                });
            </script>

		</form>
	</div>
	<?php
}



add_action( 'admin_head', 'yak_herd_inline_styles' );
function yak_herd_inline_styles() {
	$screen = get_current_screen();
	if ( $screen && $screen->id !== 'tools_page_yak-herd' ) {
		return;
	}
	?>
	<style>
		.yak-mode-section {
			margin-bottom: 1.5rem;
			padding: 1rem;
			border: 1px solid #ccd0d4;
			border-radius: 6px;
			background: #f9f9f9;
            max-width: 600px;
		}

		.yak-mode-section label {
			font-weight: 600;
			display: block;
			margin-bottom: -1rem;
		}

		.yak-mode-section select,
		.yak-mode-section textarea {
			width: 100%;
			max-width: 600px;
			padding: 0.5rem;
			font-size: 1rem;
			border: 1px solid #ccd0d4;
			border-radius: 4px;
			background: #fff;
		}

		.yak-mode-section textarea {
			min-height: 150px;
			line-height: 1.4;
			resize: vertical;
		}

		#yak-herd .button-primary {
			padding: 0.6rem 1.2rem;
			font-size: 1rem;
		}
	</style>
	<?php
}




// ✅ Message display using transient
function yak_herd_show_messages() {
	$messages = get_transient( 'yak_herd_messages' );
	if ( ! $messages || ! is_array( $messages ) ) {
		return;
	}

	foreach ( $messages as $msg ) {
		$type = $msg['type'] ?? 'info';
		$text = esc_html( $msg['message'] ?? '' );

		printf(
			'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
			esc_attr( $type ),
			$text
		);
	}

	delete_transient( 'yak_herd_messages' );
}

function yak_herd_handle_form() {
	if (
		! current_user_can( 'manage_options' ) ||
		! isset( $_POST['yak_herd_nonce'] ) ||
		! wp_verify_nonce( $_POST['yak_herd_nonce'], 'yak_herd_submit' )
	) {
		wp_die( 'Access denied or bad nonce.' );
	}

	$mode       = sanitize_text_field( $_POST['yak_herd_mode'] ?? '' );
	$raw_input  = $_POST['yak_herd_text'] ?? '';
	$items      = yak_herd_parse_input( $raw_input );
	$messages   = [];

	if ( empty( $items ) ) {
		set_transient( 'yak_herd_messages', [
			[ 'type' => 'error', 'message' => 'No valid input items found.' ]
		], 60 );
		wp_safe_redirect( admin_url( 'tools.php?page=yak-herd' ) );
		exit;
	}

	if ( $mode === 'terms' ) {
		$taxonomy   = sanitize_text_field( $_POST['yak_herd_taxonomy'] ?? '' );
		$parent_id  = intval( $_POST['yak_herd_parent_term'] ?? 0 );

		if ( ! taxonomy_exists( $taxonomy ) ) {
			$messages[] = [
				'type' => 'error',
				'message' => 'Invalid taxonomy selected.',
			];
			set_transient( 'yak_herd_messages', $messages, 60 );
			wp_safe_redirect( admin_url( 'tools.php?page=yak-herd' ) );
			exit;
		}

		$results = yak_herd_create_terms( $items, $taxonomy, $parent_id );

		$count_created = count( $results['created'] );
		$count_skipped = count( $results['skipped'] );

		if ( $count_created ) {
			$messages[] = [
				'type' => 'success',
				'message' => "$count_created terms created.",
			];
		}
		if ( $count_skipped ) {
			$messages[] = [
				'type' => 'warning',
				'message' => "$count_skipped items skipped (existing or failed).",
			];
		}
	} else if ( $mode === 'posts' ) {
        $post_type = sanitize_text_field( $_POST['yak_herd_post_type'] ?? 'post' );

        if ( ! post_type_exists( $post_type ) || $post_type === 'attachment' ) {
            $messages[] = [
                'type' => 'error',
                'message' => 'Invalid post type selected.',
            ];
            set_transient( 'yak_herd_messages', $messages, 60 );
            wp_safe_redirect( admin_url( 'tools.php?page=yak-herd' ) );
            exit;
        }

        $results = yak_herd_create_posts( $items, $post_type );

        $count_created = count( $results['created'] );
        $count_skipped = count( $results['skipped'] );

        if ( $count_created ) {
            $messages[] = [
                'type' => 'success',
                'message' => "$count_created posts created.",
            ];
        }
        if ( $count_skipped ) {
            $messages[] = [
                'type' => 'warning',
                'message' => "$count_skipped skipped (duplicate title or failed).",
            ];
        }
    } else {
		$messages[] = [
			'type' => 'error',
			'message' => 'Invalid creation mode selected.',
		];
	}

	set_transient( 'yak_herd_messages', $messages, 60 );
	wp_safe_redirect( admin_url( 'tools.php?page=yak-herd' ) );
	exit;
}


// 🧹 Parse input text
function yak_herd_parse_input( $raw, $delimiter = "\n" ) {
	$raw = str_replace( [ "\r\n", "\r" ], "\n", $raw );
	$items = explode( $delimiter, $raw );
	$clean = [];

	foreach ( $items as $item ) {
		$trimmed = trim( $item );
		if ( $trimmed !== '' ) {
			$clean[] = $trimmed;
		}
	}

	return array_values( array_unique( $clean ) );
}

function yak_herd_create_terms( $items, $taxonomy = 'category', $parent_id = 0 ) {
	$results = [
		'created' => [],
		'skipped' => [],
	];

	foreach ( $items as $name ) {
		if ( term_exists( $name, $taxonomy ) ) {
			$results['skipped'][] = $name;
			continue;
		}

		$args = [];
		if ( $parent_id > 0 ) {
			$args['parent'] = $parent_id;
		}

		$term = wp_insert_term( $name, $taxonomy, $args );

		if ( is_wp_error( $term ) ) {
			$results['skipped'][] = $name;
		} else {
			$results['created'][] = $name;
		}
	}

	return $results;
}




function yak_herd_create_posts( $items, $post_type = 'post' ) {
	$results = [
		'created' => [],
		'skipped' => [],
	];

	foreach ( $items as $title ) {
		// Skip if post with same title and type already exists
		$existing = get_page_by_title( $title, OBJECT, $post_type );
		if ( $existing ) {
			$results['skipped'][] = $title;
			continue;
		}

		$post_id = wp_insert_post( [
			'post_title'  => $title,
			'post_status' => 'publish',
			'post_type'   => $post_type,
		] );

		if ( is_wp_error( $post_id ) ) {
			$results['skipped'][] = $title;
		} else {
			$results['created'][] = $title;
		}
	}

	return $results;
}
