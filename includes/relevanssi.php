<?php

/**
 * Registers a WordPress shortcode 'last_updated' that displays the last modified
 * date of the current post in "Month Day, Year" format.
 *
 * The shortcode retrieves the global `$post` object to access the `post_modified`
 * property and formats the date using PHP's `date` and `strtotime` functions.
 *
 * Shortcode:
 * - Tag: 'last_updated'
 * - Callback: A closure that formats the last modified date of the post.
 */

namespace EightyFourEM;

use Relevanssi_SpellCorrector;

add_filter(
    hook_name: 'relevanssi_fallback',
    callback: function ( $args ) {
        global $relevanssi_dym_fallback;
        $query  = $args['args']['q'];
        $query  = htmlspecialchars_decode( $query );
        $tokens = relevanssi_tokenize( $query );

        $sc = new Relevanssi_SpellCorrector();

        $correct   = [];
        $new_query = $query;
        foreach ( array_keys( $tokens ) as $token ) {
            $token = trim( $token );
            $c     = $sc->correct( $token );
            if ( ! empty( $c ) && $c !== strval( $token ) ) {
                array_push( $correct, $c );
                $new_query = str_ireplace( $token, $c, $query );
            }
        }

        if ( $new_query !== $query ) {
            $relevanssi_dym_fallback = $new_query;

            $args['args']['q'] = $new_query;
            remove_filter( 'relevanssi_fallback', 'rlv_didyoumean_fallback' );
            $return = relevanssi_search( $args['args'] );
            add_filter( 'relevanssi_fallback', 'rlv_didyoumean_fallback' );
            $args['return'] = $return;
        }
        return $args;
    } );


global $relevanssi_dym_fallback;
if ( ! empty( $relevanssi_dym_fallback ) && $relevanssi_dym_fallback !== get_search_query() ) {
    echo "<h2 class='page-title'>Actually searched for: $relevanssi_dym_fallback</h2>";
}
