<?php

wp_editor( $this->setting[ $args['id'] ], $this->get_setting_name() . '-' . $args['id'], $args['args'] );
printf( '<label for="%1$s[%2$s]">%3$s</label>', esc_attr( $this->get_setting_name() ), esc_attr( $args['id'] ), $args['label'] );

