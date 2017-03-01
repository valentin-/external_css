<?php

namespace ExternalCSS;

use Contao\Input;
use Contao\LayoutModel;

class Hooks extends \Controller
{

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
            'compress' => true,
            'cache_dir' => TL_ROOT . '/' . $tmpFolder
        );

        $objFiles = unserialize($objLayout->external_css);

        if (!$objFiles || !is_array($objFiles)) {
            return;
        }

        $objFiles = $db->query("SELECT * FROM tl_external_css WHERE id IN(" . implode(',', $objFiles) . ") ORDER BY sorting")->fetchAllAssoc();

        if ($objFiles) {
            foreach ($objFiles as $file) {

                $filePath = '';

                if ($file['type'] == 'url' && $file['url']) {
                    $GLOBALS['TL_HEAD'][] = \Template::generateStyleTag($file['url'], '', false);
                }

                if ($file['type'] == 'file') {
                    $obj = \FilesModel::findByUuid($file['file']);

                    if ($obj) {
                        $filePath = $obj->path;
                    } else {
                        if (is_file($file['file'])) {
                            $filePath = $file['file'];
                        }
                    }
                }

                if ($filePath) {
                    if ($file['atf']) {
                        $hasAtf = true;
                        $arrAtf[] = $filePath;
                    } else {
                        $arrFiles[] = $filePath;
                    }
                }
            }
        }

        if (isset($GLOBALS['TL_HOOKS']['addExternalCssFiles']) && is_array($GLOBALS['TL_HOOKS']['addExternalCssFiles'])) {
            foreach ($GLOBALS['TL_HOOKS']['addExternalCssFiles'] as $callback) {
                $this->import($callback[0]);
                $arrFiles = $this->$callback[0]->$callback[1]($arrFiles);
            }
        }


        $variables = array();
        $arrVars = array();

        $objTheme = \ThemeModel::findByPk($objLayout->pid);

        if ($objTheme->vars) {
            $arrVars = deserialize($objTheme->vars);
            foreach ($arrVars as $var) {
                $k = preg_replace('/\$/', '@', $var['key'], 1);
                if ($k[0] != '@') {
                    $k = '@' . $k;
                }
                $variables[$k] = $var['value'];
            }
        }

        if (isset($GLOBALS['TL_HOOKS']['addExternalCssVariables']) && is_array($GLOBALS['TL_HOOKS']['addExternalCssVariables'])) {
            foreach ($GLOBALS['TL_HOOKS']['addExternalCssVariables'] as $callback) {
                $this->import($callback[0]);
                $variables = $this->$callback[0]->$callback[1]($variables);
            }
        }


        if ($GLOBALS['TL_CSS'] && is_array($GLOBALS['TL_CSS'])) {

            $arrFiles = array_merge($GLOBALS['TL_CSS'], $arrFiles);
            unset($GLOBALS['TL_CSS']);
        }


        $arrFiles = \ExternalCssHelper::extendFilesPath($arrFiles);
        $arrAtf = \ExternalCssHelper::extendFilesPath($arrAtf);


        $session = $db->prepare('SELECT pid FROM tl_session WHERE name="BE_USER_AUTH" AND hash=?')->limit(1)->execute($_COOKIE['BE_USER_AUTH']);
        $user = \Database::getInstance()->prepare('SELECT external_css_livereload FROM tl_user WHERE id=?')->execute($session->pid)->fetchAssoc();

        if($_COOKIE['BE_USER_AUTH'] && $user['external_css_livereload']) {

            $arrF = array();

            foreach (array_merge($arrAtf, $arrFiles) as $k => $v) {
                $arrF[] = str_replace(TL_ROOT.'/', '', $k);
            }

            foreach ($arrF as $k => $file) {

                $dir = dirname($file);
                $filename = basename($file);
                $filePath = $dir.'/css/'.$filename;
                $filePath = str_replace('.less', '.css', $filePath);

                if(is_readable(TL_ROOT.'/'.$filePath)) {
                    $GLOBALS['TL_BODY'][] = \Template::generateStyleTag(\Controller::addStaticUrlTo($filePath), '', false);
                } else {

                    if(strpos($filename, '.less') !== false) {

                        $parser = new \Less_Parser();
                        $parser->parseFile($file, $k);
                        $css = $parser->getCss();

                        if(!is_dir(TL_ROOT.'/'.$dir.'/css')) {
                            mkdir(TL_ROOT.'/'.$dir.'/css', 0755, true);
                        }

                        file_put_contents(TL_ROOT.'/'.$filePath, $css);

                        $GLOBALS['TL_BODY'][] = \Template::generateStyleTag(\Controller::addStaticUrlTo($filePath), '', false);


                    } else {
                        $GLOBALS['TL_BODY'][] = \Template::generateStyleTag(\Controller::addStaticUrlTo($file), '', false);
                    }

                }
            }

        } else {


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

            if ($hasAtf) {
                $atfCss = file_get_contents($atfFile);
                $GLOBALS['TL_HEAD'][] = '<style>' . $atfCss . '</style>';
//            $GLOBALS['TL_HEAD'][] = \Template::generateStyleTag(\Controller::addStaticUrlTo($atfFile), '', false);
                $GLOBALS['TL_BODY'][] = \Template::generateStyleTag(\Controller::addStaticUrlTo($lessFile), '', false);
            } else {
                $GLOBALS['TL_HEAD'][] = \Template::generateStyleTag(\Controller::addStaticUrlTo($lessFile), '', false);
            }

        }


        return $strBuffer;

    }

}
