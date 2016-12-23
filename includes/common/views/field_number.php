<?php

$setting = isset( $this->setting[ $args['setting'] ] ) ? $this->setting[ $args['setting'] ] : 0;
$label   = $args['setting'];
if ( isset( $args['key'] ) ) {
	$setting = isset( $this->setting[ $args['key'] ][ $args['setting'] ] ) ? $this->setting[ $args['key'] ][ $args['setting'] ] : 0;
	$label   = "{$args['key']}][{$args['setting']}";
}
printf( '<label for="%5$s[%3$s]"><input type="number" step="%6$s" min="%1$s" max="%2$s" id="%5$s[%3$s]" name="%5$s[%3$s]" value="%4$s" class="small-text" />%7$s</label>',
	$args['min'],
	(int) $args['max'],
	esc_attr( $label ),
	esc_attr( $setting ),
	esc_attr( $this->get_setting_name() ),
	isset( $args['step'] ) ? esc_attr( $args['step'] ) : (int) 1,
	isset( $args['value'] ) ? esc_attr( $args['value'] ) : ''
);
$this->do_description( $args['setting'] );
