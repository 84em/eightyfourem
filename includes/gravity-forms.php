<?php

namespace EightyFourEM;

defined( 'ABSPATH' ) || exit;

// customizes the Gravity Forms error message.
\add_filter(
    hook_name: 'gform_validation_message',
    callback: function ( $message, $form ) {
        $message = "<div class='validation_error gfield_validation_message' style='color:rgb(192, 43, 10)'>";
        $message .= "<p>The following fields need to be filled out.  Please scroll down for details and try again.</p>";
        $message .= '<ul>';
        foreach ( $form['fields'] as $field ) {
            if ( $field->failed_validation ) {
                $message .= sprintf( '<li><strong style="font-weight:700">%s</strong></li>', \GFCommon::get_label( $field ) );
            }
        }
        $message .= "</div>";

        return $message;
    },
    priority: 10,
    accepted_args: 2 );
