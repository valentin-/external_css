<?php

/**
 * External CSS
 *
 * Copyright (C) 2015 Valentin Sampl
 *
 * @package    External CSS
 * @author     Valentin Sampl <valentin.sampl@gmail.com>
 */

$GLOBALS['TL_DCA']['tl_theme']['config']['ctable'][] = 'tl_external_css';

$intOffset = array_search('css', array_keys($GLOBALS['TL_DCA']['tl_theme']['list']['operations'])) + 1;

$GLOBALS['TL_DCA']['tl_theme']['list']['operations'] = array_merge
(
	array_slice($GLOBALS['TL_DCA']['tl_theme']['list']['operations'], 0, $intOffset),
	array
	(
		'external_css_stylesheet'     => array
		(
			'label'               => &$GLOBALS['TL_LANG']['tl_theme']['external_css'],
			'href'                => 'table=tl_external_css',
			'icon'                => 'system/modules/external_css/assets/i/css.png',
			// 'button_callback'     => array('Bit3\Contao\ThemePlus\DataContainer\Theme', 'editStylesheet')
		),
	),
	array_slice($GLOBALS['TL_DCA']['tl_theme']['list']['operations'], $intOffset)
);

