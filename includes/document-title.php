<?php

/**
 * Filters the document title to integrate a custom title set in the post meta (_genesis_title).
 *
 * This function retrieves the `_genesis_title` meta field for the current post and applies it
 * as the document title if it exists and is not empty. The meta value is sanitized using
 * `wp_strip_all_tags` before replacing the default title.
 *
 * @hook document_title
 *
 * @param  string  $title  The current document title.
 *
 * @return string The filtered document title.
 */

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

\add_filter(
    hook_name: 'document_title',
    callback: function ( $title ) {
        $_genesis_title = \get_post_meta( \get_the_ID(), '_genesis_title', true );
        if ( ! empty( $_genesis_title ) ) {
            $title = \wp_strip_all_tags( $_genesis_title );
        }

        return $title;
    },
    priority: 100 );
