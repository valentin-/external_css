<?php

class ExternalCssHelper {

	public function getStylesheets($dc)
    {

    	$db = Database::getInstance();

        $objCss = $db->query("SELECT * FROM tl_external_css WHERE pid = ".$dc->activeRecord->pid." ORDER BY sorting")->fetchAllAssoc();

        $arrFiles = array();

        if($objCss) {

        	foreach ($objCss as $file) {

        		$label = '?';

        		if($file['type'] == 'url') {
                    $label = preg_replace('#/([^/]+)$#', '/<strong>$1</strong>', $file['url']);
        		}

        		if($file['type'] == 'file') {

        			$objFile = FilesModel::findByUuid($file['file']);

        			if($objFile) {
						$label = preg_replace('#/([^/]+)$#', '/<strong>$1</strong>', $objFile->path);
        			} else {
                   		$label = preg_replace('#/([^/]+)$#', '/<strong>$1</strong>', $file['file']);
        			}
        		}

        		$arrFiles[$file['id']] = $label;

        	}

        }

        return $arrFiles;

    }

}