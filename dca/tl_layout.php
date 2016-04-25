<?php

$GLOBALS['TL_DCA']['tl_layout']['palettes']['default'] = str_replace('loadingOrder', 'loadingOrder,external_css,', $GLOBALS['TL_DCA']['tl_layout']['palettes']['default']); 

$GLOBALS['TL_DCA']['tl_layout']['fields']['external_css'] = array
(
	'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['external_css'],
	'exclude'                 => true,
	'inputType'               => 'checkbox',
	'options_callback'        => array('ExternalCssHelper', 'getStylesheets'),
	'eval'                    => array(
		'multiple'=> true,
        'tl_class'=> 'clr'
    ),
	'sql'                     => 'blob NULL',
);