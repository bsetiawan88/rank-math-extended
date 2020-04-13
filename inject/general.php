<?php

$cmb->add_field( array(
	'id'   => 'cdn_domain',
	'type' => 'text',
	'name' => esc_html__( 'Use CDN for images', 'rank-math' ),
	'desc' => esc_html__( 'Enter your CDN domain url.', 'rank-math' ),
) );