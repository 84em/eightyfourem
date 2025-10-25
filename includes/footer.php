<?php
/**
 * Footer functionality
 * Handles anchor link highlighting on specific pages
 *
 * @package EightyFourEM
 */

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

add_action( 'wp_footer', function () {
    if ( is_singular() && get_the_ID() === 2934 ) {
        ?>
        <style><?php include \get_template_directory() . '/assets/css/highlight.min.css'; ?></style>
        <script><?php include \get_template_directory() . '/assets/js/highlight.min.js'; ?></script>
        <?php
    }
} );
