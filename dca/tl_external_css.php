<?php

/**
 * Table tl_external_css
 */
$GLOBALS['TL_DCA']['tl_external_css'] = array
(

	// Config
	'config'          => array
	(
		'dataContainer'    => 'Table',
		'ptable'           => 'tl_theme',
		'enableVersioning' => true,
		'onload_callback'  => array(
			array('tl_external_css', 'changeFile')
		),
		'sql'              => array
		(
			'keys' => array
			(
				'id'  => 'primary',
				'pid' => 'index'
			)
		),
	),
	// List
	'list'            => array
	(
		'sorting'           => array
		(
			'mode'                  => 4,
			'flag'                  => 11,
			'fields'                => array('sorting'),
			'panelLayout'           => 'filter;limit',
			'headerFields'          => array('name', 'author', 'tstamp'),
			'child_record_callback' => array('tl_external_css', 'listFile'),
		),
		'global_operations' => array
		(
			'all'     => array
			(
				'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'       => 'act=select',
				'class'      => 'header_edit_all',
				'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"'
			)
		),
		'operations'        => array
		(
			'edit'   => array
			(
				'label' => &$GLOBALS['TL_LANG']['tl_external_css']['edit'],
				'href'  => 'act=edit',
				'icon'  => 'edit.gif'
			),
			'copy'   => array
			(
				'label' => &$GLOBALS['TL_LANG']['tl_external_css']['copy'],
				'href'  => 'act=paste&amp;mode=copy',
				'icon'  => 'copy.gif'
			),
			'cut'    => array
			(
				'label'      => &$GLOBALS['TL_LANG']['tl_external_css']['cut'],
				'href'       => 'act=paste&amp;mode=cut',
				'icon'       => 'cut.gif',
				'attributes' => 'onclick="Backend.getScrollOffset();"'
			),
			'delete' => array
			(
				'label'      => &$GLOBALS['TL_LANG']['tl_external_css']['delete'],
				'href'       => 'act=delete',
				'icon'       => 'delete.gif',
				'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show'   => array
			(
				'label' => &$GLOBALS['TL_LANG']['tl_external_css']['show'],
				'href'  => 'act=show',
				'icon'  => 'show.gif'
			)
		)
	),
	// Palettes
	'palettes'        => array
	(
		'__selector__' => array('type'),
		'default' => '{source_legend},type;{layouts_legend},layouts,atf'
	),
	'subpalettes' => array(
		'type_file' => 'filesource,file',
		'type_url' => 'url'
	),
	'fields'          => array
	(
		'id'                       => array
		(
			'sql' => "int(10) unsigned NOT NULL auto_increment"
		),
		'pid'                      => array
		(
			'foreignKey' => 'tl_style_sheet.name',
			'sql'        => "int(10) unsigned NOT NULL default '0'",
			'relation'   => array('type' => 'belongsTo', 'load' => 'lazy')
		),
		'sorting'                  => array
		(
			'sql' => "int(10) unsigned NOT NULL default '0'"
		),
		'tstamp'                   => array
		(
			'sql' => "int(10) unsigned NOT NULL default '0'"
		),
		'type'                     => array
		(
			'label'         => &$GLOBALS['TL_LANG']['tl_external_css']['type'],
			'default'       => 'file',
			'inputType'     => 'select',
			'filter'        => true,
			'options'       => array('file', 'url'),
			'reference'     => &$GLOBALS['TL_LANG']['tl_external_css'],
			'eval'          => array(
				'submitOnChange'     => true,
				'tl_class'           => 'w50'
			),
			'sql'           => "varchar(32) NOT NULL default 'file'"
		),
        'filesource'                            => array
        (
            'label'         => &$GLOBALS['TL_LANG']['tl_external_css']['filesource'],
            'default'       => $GLOBALS['TL_CONFIG']['uploadPath'],
            'inputType'     => 'select',
            'filter'        => true,
            'options'       => array($GLOBALS['TL_CONFIG']['uploadPath'], 'assets', 'system/modules', 'composer/vendor'),
            'eval'          => array(
	            	'submitOnChange'    => true,
	                'tl_class'          => 'w50'
                ),
            'sql'           => "varchar(32) NOT NULL default '{$GLOBALS['TL_CONFIG']['uploadPath']}'"
        ),
		'file'                     => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_external_css']['file'],
			'inputType' => 'fileTree',
			'eval'      => array(
				'mandatory'  => true,
				'fieldType'  => 'radio',
				'files'      => true,
				'filesOnly'  => true,
				'extensions' => 'css,less,scss,sass',
				'tl_class'   => 'clr'
			),
            'sql'       => "blob NULL"
		),
		'url'                      => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_external_css']['url'],
			'inputType' => 'text',
			'eval'      => array(
				'mandatory'      => true,
				'decodeEntities' => true,
				'tl_class'       => 'long clr'
			),
			'sql'       => "blob NULL"
		),
        'layouts'                               => array
        (
            'label'     => &$GLOBALS['TL_LANG']['tl_external_css']['layouts'],
            'exclude'   => true,
            'inputType' => 'checkbox',
			'options_callback' => array('tl_external_css', 'listLayouts'),
            'load_callback' => array(array('tl_external_css', 'loadLayouts')),
            'save_callback' => array(array('tl_external_css', 'saveLayouts')),
            'eval'      => array('multiple' => true, 'doNotSaveEmpty' => true),
        ),
        'atf' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_external_css']['atf'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array(),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
	)
);

class tl_external_css {

	public function listFile($row)
	{

		if($row['type'] == 'file') {

			$path = $row['file'];

			$objFile = \FilesModel::findByUuid($row['file']);

			if($objFile) {
				$path = $objFile->path;
			}

		}

		if($row['type'] == 'url') {
			$path = $row['url'];
		}

		$label = preg_replace('#/([^/]+)$#', '/<strong>$1</strong>', $path);

		if(strlen($row['position'])) {
			$label = '[' . strtoupper($row['position']) . '] ' . $label;
		}

		if (strlen($row['cc'])) {
			$label .= ' <span style="padding-left: 3px; color: #B3B3B3;">[' . $row['cc'] . ']</span>';
		}


		return '<div>'. $label . "</div>\n";

	}

	public function listLayouts()
	{

		$layout = \Database::getInstance()
			->query(
				'SELECT l.*, t.name AS theme
				 FROM tl_layout l
				 INNER JOIN tl_theme t
				 ON t.id=l.pid
				 ORDER BY t.name, l.name');

		$options = array();

		while ($layout->next()) {
			$options[$layout->theme][$layout->id] = $layout->name;
		}

		return $options;
	}

	public function changeFile($dc)
	{
		$file = \Database::getInstance()->query('SELECT * FROM ' . $dc->table . ' WHERE id=' . intval($dc->id));

		if ($file->filesource != $GLOBALS['TL_CONFIG']['uploadPath'] && $file->type == 'file') {
			$GLOBALS['TL_DCA'][$dc->table]['fields']['file']['inputType'] = 'fileSelector';
			$GLOBALS['TL_DCA'][$dc->table]['fields']['file']['eval']['path'] = $file->filesource;
		}
	}

	public function loadLayouts($value, $dc)
	{
		// return $this->loadLayoutsFor('theme_plus_stylesheets', $dc);

		$field = 'external_css';

		$objLayouts = \Database::getInstance()->query('SELECT * FROM tl_layout')->fetchAllAssoc();

		$arrOptions = array();

		foreach ($objLayouts as $layout) {
			$selected = deserialize($layout[$field], true);
			if (in_array($dc->id, $selected)) {
				$arrOptions[] = $layout['id'];
			}
		}

		return $arrOptions;

	}

	public function saveLayouts($value, $dc)
	{

		$field = 'external_css';

		$layouts = deserialize($value, true);

		$layout = \Database::getInstance()
			->query('SELECT * FROM tl_layout');

		while ($layout->next()) {
			$selected = deserialize($layout->$field, true);

			// select a new layout
			if (in_array($layout->id, $layouts) && !in_array($dc->id, $selected)) {
				$selected[] = $dc->id;
			}

			// deselect a layout
			else if (!in_array($layout->id, $layouts) && in_array($dc->id, $selected)) {
				$index = array_search($dc->id, $selected);
				unset($selected[$index]);
			}

			// nothing changed
			else {
				continue;
			}

			\Database::getInstance()
				->prepare('UPDATE tl_layout %s WHERE id=?')
				->set(array($field => serialize(array_values($selected))))
				->execute($layout->id);
		}

		return null;
	}
}
