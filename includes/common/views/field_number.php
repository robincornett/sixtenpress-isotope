<?php

$id      = isset( $args['id'] ) ? $args['id'] : $args['setting'];
$setting = isset( $this->setting[ $id ] ) ? $this->setting[ $id ] : 0;
if ( isset( $args['key'] ) ) {
	$setting = isset( $this->setting[ $args['key'] ][ $id ] ) ? $this->setting[ $args['key'] ][ $id ] : 0;
	$id      = "{$args['key']}][{$args['setting']}";
}
printf( '<label for="%5$s[%3$s]"><input type="number" step="%6$s" min="%1$s" max="%2$s" id="%5$s[%3$s]" name="%5$s[%3$s]" value="%4$s" class="small-text" />%7$s</label>',
	$args['min'],
	(int) $args['max'],
	esc_attr( $id ),
	esc_attr( $setting ),
	esc_attr( $this->get_setting_name() ),
	isset( $args['step'] ) ? esc_attr( $args['step'] ) : (int) 1,
	isset( $args['value'] ) ? esc_attr( $args['value'] ) : ''
);
