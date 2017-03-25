<?php

$function = isset( $args['options'] ) ? 'pick_' . $args['options'] : '';
$options  = method_exists( $this, $function ) ? $this->$function() : $args['choices'];
$array    = $this->get_select_setting( $args );
$setting  = $array['setting'];
$label    = $array['label'];
$class    = isset( $args['class'] ) ? sprintf( ' class=%s', $args['class'] ) : '';
printf( '<label for="%s[%s]">', esc_attr( $this->get_setting_name() ), esc_attr( $label ) );
printf( '<select id="%1$s[%2$s]" name="%1$s[%2$s]"%3$s>', esc_attr( $this->get_setting_name() ), esc_attr( $label ), esc_attr( $class ) );
foreach ( (array) $options as $name => $key ) {
	printf( '<option value="%s" %s>%s</option>', esc_attr( $name ), selected( $name, $setting, false ), esc_attr( $key ) );
}
echo '</select></label>';
