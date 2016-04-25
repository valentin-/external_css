<?php

// Fields
$GLOBALS['TL_DCA']['tl_user']['fields']['external_css_livereload'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_user']['external_css_livereload'],
    'default'                 => '',
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('tl_class' => 'w50 m12'),
    'sql'                     => "char(1) NOT NULL default ''"
);

foreach($GLOBALS['TL_DCA']['tl_user']['palettes'] as $k => $v)
{
    if($k == '__selector__') continue;
    $GLOBALS['TL_DCA']['tl_user']['palettes'][$k] = str_replace(';{password_legend',';{external_css_legend},external_css_livereload;{password_legend',$v);
}
