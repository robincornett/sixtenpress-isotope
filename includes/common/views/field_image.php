<?php

$id = $this->setting[ $args['id'] ];
if ( ! empty( $id ) ) {
	echo wp_kses_post( $this->render_image_preview( $id ) );
}
$this->render_buttons( $id, $args );
