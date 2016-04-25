<?php

$GLOBALS['BE_MOD']['design']['themes']['tables'][] = 'tl_external_css';

$GLOBALS['TL_HOOKS']['generatePage'][] = array('ExternalCSS\Hooks', 'generateCSS');

$GLOBALS['TL_EASY_THEMES_MODULES']['external_css'] = array
(
    'label'         => &$GLOBALS['TL_LANG']['tl_theme']['theme_plus_stylesheet'][0],
    'title'         => &$GLOBALS['TL_LANG']['tl_theme']['theme_plus_stylesheet'][1],
    'href_fragment' => 'table=tl_external_css',
    'icon'          => 'system/modules/external_css/assets/i/css.png',
    'appendRT'      => true,
);