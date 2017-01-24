<?php

printf( '<label for="%1$s[%2$s]">%3$s</label>', esc_attr( $this->page ), esc_attr( $args['id'] ), $args['label'] );
wp_editor( $this->setting[ $args['id'] ], $this->page . '-' . $args['id'], $args['args'] );

