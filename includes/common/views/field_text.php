<?php

printf( '<input type="text" id="%3$s[%1$s]" aria-label="%3$s[%1$s]" name="%3$s[%1$s]" value="%2$s" class="regular-text" />',
	esc_attr( $args['id'] ),
	esc_attr( $this->setting[ $args['id'] ] ),
	esc_attr( $this->get_setting_name() )
);
