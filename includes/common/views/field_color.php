<?php

printf( '<input type="text" name="%3$s[%1$s]" value="%2$s" class="color-field">',
	esc_attr( $args['setting'] ),
	esc_attr( $this->setting[ $args['setting'] ] ),
	esc_attr( $this->get_setting_name() )
);
$this->do_description( $args['setting'] );
