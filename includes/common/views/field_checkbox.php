<?php

$get_things = $this->get_checkbox_setting( $args );
$label   = $get_things['label'];
$setting = $get_things['setting'];
$style   = isset( $args['style'] ) ? sprintf( 'style=%s', $args['style'] ) : '';
printf( '<input type="hidden" name="%s[%s]" value="0" />', esc_attr( $this->get_setting_name() ), esc_attr( $label ) );
printf( '<label for="%1$s[%2$s]" %5$s><input type="checkbox" name="%1$s[%2$s]" id="%1$s[%2$s]" value="1" %3$s class="code" />%4$s</label>',
	esc_attr( $this->get_setting_name() ),
	esc_attr( $label ),
	checked( 1, esc_attr( $setting ), false ),
	esc_attr( $args['label'] ),
	$style
);
