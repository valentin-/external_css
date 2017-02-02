<?php

namespace ExternalCSS;

use Contao\Input;
use Contao\LayoutModel;

class Hooks extends \Controller {

    public function addCssFiles($strBuffer, $strTemplate)
    {

        global $objPage;
        $objLayout = LayoutModel::findByPk($objPage->layout);
        $db = \Database::getInstance();

        $arrFiles = array();
        $arrAtf = array();
        $hasAtf = false;

        $tmpFolder = 'assets/css';

        $options = array(
            'compress'=>true,
            'cache_dir' => TL_ROOT.'/'.$tmpFolder
        );

        $objFiles = unserialize($objLayout->external_css);

        if(!$objFiles || !is_array($objFiles)) {
            return;
        }

        $objFiles = $db->query("SELECT * FROM tl_external_css WHERE id IN(".implode(',', $objFiles).") ORDER BY sorting")->fetchAllAssoc();


        if($objFiles) {
            foreach ($objFiles as $file) {

                $filePath = '';

                if($file['type'] == 'url' && $file['url']) {
                    $GLOBALS['TL_HEAD'][] = \Template::generateStyleTag($file['url'], '', false);
                }

                if($file['type'] == 'file') {
                    $obj = \FilesModel::findByUuid($file['file']);

                    if($obj) {
                        $filePath = $obj->path;
                    } else {
                        if(is_file($file['file']))  {
                            $filePath = $file['file'];
                        }
                    }
                }

                if($filePath) {
                    if($file['atf']) {
                        $hasAtf = true;
                        $arrAtf[] = $filePath;
                    } else {
                        $arrFiles[] = $filePath;
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
        

        if($GLOBALS['TL_CSS'] && is_array($GLOBALS['TL_CSS'])) {

            $arrFiles = array_merge($GLOBALS['TL_CSS'], $arrFiles);
            unset($GLOBALS['TL_CSS']);
        }


        $arrFiles = \ExternalCssHelper::extendFilesPath($arrFiles);
        $arrAtf = \ExternalCssHelper::extendFilesPath($arrAtf);

        $lessFile = \ExternalCssHelper::prepareFile($arrFiles, array(
            'lessOptions' => $options,
            'lessVariables' => $variables,
            'tmpFolder' => $tmpFolder
        ));

        $atfFile = \ExternalCssHelper::prepareFile($arrAtf, array(
            'lessOptions' => $options,
            'lessVariables' => $variables,
            'tmpFolder' => $tmpFolder
        ));

        if($hasAtf) {
            $atfCss = file_get_contents($atfFile);
            $GLOBALS['TL_HEAD'][] = '<style>'.$atfCss.'</style>';
//            $GLOBALS['TL_HEAD'][] = \Template::generateStyleTag(\Controller::addStaticUrlTo($atfFile), '', false);
            $GLOBALS['TL_BODY'][] = \Template::generateStyleTag(\Controller::addStaticUrlTo($lessFile), '', false);
        } else {
            $GLOBALS['TL_HEAD'][] = \Template::generateStyleTag(\Controller::addStaticUrlTo($lessFile), '', false);
        }



        return $strBuffer;

    }

}
