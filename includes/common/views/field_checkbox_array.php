<?php

foreach ( $args['choices'] as $key => $label ) {
	$setting = isset( $this->setting[ $args['setting'] ][ $key ] ) ? $this->setting[ $args['setting'] ][ $key ] : 0;
	printf( '<input type="hidden" name="%s[%s][%s]" value="0" />', esc_attr( $this->get_setting_name() ), esc_attr( $args['setting'] ), esc_attr( $key ) );
	printf( '<label for="%4$s[%5$s][%1$s]" style="margin-right:12px;"><input type="checkbox" name="%4$s[%5$s][%1$s]" id="%4$s[%5$s][%1$s]" value="1"%2$s class="code"/>%3$s</label>',
		esc_attr( $key ),
		checked( 1, $setting, false ),
		esc_html( $label ),
		esc_attr( $this->get_setting_name() ),
		esc_attr( $args['setting'] )
	);
	echo isset( $args['clear'] ) && $args['clear'] ? '<br />' : '';
}
$this->do_description( $args['setting'] );
