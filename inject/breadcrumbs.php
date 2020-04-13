<?php

$cmb->add_field([
	'id'      => 'breadcrumbs_inject_after',
	'type'    => 'text',
	'name'    => esc_html__( 'Inject Breadcrumb After', 'rank-math' ),
	'desc'    => esc_html__( 'Selector to inject breadcrumb.', 'rank-math' ),
	'dep'     => $dependency,
]);