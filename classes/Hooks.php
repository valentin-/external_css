<?php

namespace ExternalCSS;

class Hooks extends \Controller {

	public function generateCSS(\PageModel $objPage, \LayoutModel $objLayout, \PageRegular $objPageRegular) {

		$db = \Database::getInstance();

		$lessFolder = 'assets/css';

		$options = array(
			'compress'=>true,
			'cache_dir' => TL_ROOT.'/'.$lessFolder
		);

		$objFiles = unserialize($objLayout->external_css);

		if(!$objFiles || !is_array($objFiles)) {
			return;
		} 

		$objFiles = $db->query("SELECT * FROM tl_external_css WHERE id IN(".implode(',', $objFiles).") ORDER BY sorting")->fetchAllAssoc();
		$arrFiles = array();

		if($objFiles) {
			foreach ($objFiles as $file) {
				
				if($file['type'] == 'url' && $file['url']) {
					$GLOBALS['TL_HEAD'][] = \Template::generateStyleTag($file['url'], '', false);
				}

				if($file['type'] == 'file') {
					$obj = \FilesModel::findByUuid($file['file']);

					if($obj) {
						$arrFiles[] = $obj->path;
					} else {
						if(is_file($file['file']))  {
							$arrFiles[] = $file['file'];
						}
					}
				}

			}
		}

		if (isset($GLOBALS['TL_HOOKS']['addExternalCssFiles']) && is_array($GLOBALS['TL_HOOKS']['addExternalCssFiles']))
		{
			foreach ($GLOBALS['TL_HOOKS']['addExternalCssFiles'] as $callback)
			{
				$this->import($callback[0]);
				$arrFiles = $this->$callback[0]->$callback[1]($arrFiles);
			}
		}


		if($arrFiles) {
			$tmpFiles = array();
			foreach ($arrFiles as $file) {
				if(is_readable($file)) {
					$tmpFiles[TL_ROOT.'/'.$file] = '/'.dirname($file);
				}
			}
		} else {
			return;
		}

		$variables = array();
		$arrVars = array();

		$objTheme = \ThemeModel::findByPk($objLayout->pid);

		if($objTheme->vars) {
			$arrVars = deserialize($objTheme->vars);

			foreach ($arrVars as $var) {
				$k = preg_replace('/\$/', '@', $var['key'], 1);

				if($k[0] != '@') {
					$k = '@'.$k;
				}

				$variables[$k] = $var['value'];
			}
		}

		if (isset($GLOBALS['TL_HOOKS']['addExternalCssVariables']) && is_array($GLOBALS['TL_HOOKS']['addExternalCssVariables']))
		{
			foreach ($GLOBALS['TL_HOOKS']['addExternalCssVariables'] as $callback)
			{
				$this->import($callback[0]);
				$variables = $this->$callback[0]->$callback[1]($variables);
			}
		}

		$arrFiles = $tmpFiles;
		
		$file = \Less_Cache::Get($arrFiles, $options, $variables);

		if(!$file) {
			return;
		}

		if($_COOKIE['BE_USER_AUTH']) {
			$DB = \Database::getInstance();
			$Session = $DB->prepare('SELECT pid FROM tl_session WHERE name="BE_USER_AUTH" AND hash=?')->limit(1)->execute($_COOKIE['BE_USER_AUTH']);
			$User = \Database::getInstance()->prepare('SELECT external_css_livereload FROM tl_user WHERE id=?')->execute($Session->pid)->fetchAssoc();

			if($User['external_css_livereload']) {

				$reload = false;

				$filePath = $lessFolder.'/livereload.css';
				$strCss = file_get_contents($lessFolder.'/'.$file);
				$strOldCss = file_get_contents($filePath);

				if($strCss != $strOldCss) {
					$reload = true;
					file_put_contents($filePath, $strCss);
				}
				
				$filemtime = filemtime($filePath);
				$fileSRC = '<link id="livereload" rel="stylesheet" href="'.$filePath.'?v='.$filemtime.'" />';

				if(\Input::get('action') == 'getLiveCSS') {
					echo json_encode(array(
						'reload' => $reload,
						'file' => $fileSRC
					));
					die;
				}

				$GLOBALS['TL_HEAD'][] = $fileSRC;
				$GLOBALS['TL_JQUERY'][] = '<script src="system/modules/external_css/assets/j/livereload.js"></script>';
				return;
			}

		}

		$filePath = $lessFolder.'/'.$file;

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
		$GLOBALS['TL_HEAD'][] = \Template::generateStyleTag(\Controller::addStaticUrlTo($filePath), '', false);

	}

}