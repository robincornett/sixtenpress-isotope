<?php

$rows = isset( $args['rows'] ) ? $args['rows'] : 3;
printf( '<textarea class="large-text" rows="%4$s" id="%1$s[%2$s]" name="%1$s[%2$s]">%3$s</textarea>',
	esc_attr( $this->page ),
	esc_attr( $args['id'] ),
	esc_textarea( $this->setting[ $args['id'] ] ),
	(int) $rows
);
printf( '<br /><label for="%1$s[%2$s]">%3$s</label>', esc_attr( $this->page ), esc_attr( $args['id'] ), esc_html( $args['label'] ) );
