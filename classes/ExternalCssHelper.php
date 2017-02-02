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

    static function extendFilesPath($arrFiles) {

        if(!$arrFiles || !is_array($arrFiles)) {
            return array();
        }

        $tmpFiles = array();
        foreach ($arrFiles as $file) {

            $file = explode('|', $file);
            $file = $file[0];

            if(is_readable($file)) {
                $tmpFiles[TL_ROOT.'/'.$file] = '/'.dirname($file);
            }
        }

        return $tmpFiles;

    }

    static function prepareFile($arrFiles, $arrOptions) {

        $options = array();
        $variables = array();
        $tmpFolder = 'assets/css';

        if($arrOptions['lessOptions']) {
            $options = $arrOptions['lessOptions'];
        }
        if($arrOptions['lessVariables']) {
            $variables = $arrOptions['lessVariables'];
        }
        if($arrOptions['tmpFolder']) {
            $tmpFolder = $arrOptions['tmpFolder'];
        }



        $file = \Less_Cache::Get($arrFiles, $options, $variables);
        $filePath = $tmpFolder.'/'.$file;
        
        $imgs = array();
        $strCss = file_get_contents($filePath);
        $re = '/url\(\s*[\'"]?(\S*\.(?:jpe?g|gif|png))[\'"]?\s*\)[^;}]*?/i';
        if (preg_match_all($re, $strCss, $matches)) {
            $imgs = $matches[1];
        }

        $embedFile = str_replace('.css', '_embed.css', $filePath);

        if(!is_file($embedFile)) {

            $arrParsed = array();
            $strCss = file_get_contents($filePath);

            foreach ($imgs as $img) {
                $imgPath = TL_ROOT.$img;

                if(in_array($img, $arrParsed)) {
                    continue;
                }

                if(is_file($imgPath)) {

                    $size = filesize($imgPath);
                    $mb = $size / 1048576;

                    if($mb < 0.2) {

                        $ext = pathinfo($imgPath, PATHINFO_EXTENSION);

                        $b64 = file_get_contents($imgPath);
                        $b64 = base64_encode($b64);

                        $base64 = 'data:image/'.$ext.';base64,'.$b64;
                        $strCss = str_replace($img, $base64, $strCss);

                        $arrParsed[] = $img;

                    }

                }
            }

            file_put_contents($embedFile, $strCss);

        }

        $filePath = $embedFile;

        return $filePath;

    }

}
