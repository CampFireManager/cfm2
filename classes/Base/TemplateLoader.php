<?php
/**
 * CampFire Manager is a scheduling tool predominently used at BarCamps to 
 * schedule talks based, mainly, on the number of people attending each talk
 * receives.
 *
 * PHP version 5
 *
 * @category Default
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */
/**
 * This class runs the template loader.
 *
 * @category Base_TemplateLoader
 * @package  CampFireManager2
 * @author   Jon Spriggs <jon@sprig.gs>
 * @license  http://www.gnu.org/licenses/agpl.html AGPLv3
 * @link     https://github.com/JonTheNiceGuy/cfm2 Version Control Service
 */

class Base_TemplateLoader
{
    /**
     * This function ensures we've got the Smarty library loaded, and then
     * starts the template associated to it.
     *
     * @param string $template       Template to load
     * @param array  $arrAssignments Variables to be assigned to the template
     *
     * @return void
     */
    static function render($template = '', $arrAssignments = array())
    {
        $libSmarty = Base_ExternalLibraryLoader::loadLibrary("Smarty");
        if ($libSmarty == false) {
            die("Failed to load Smarty");
        }
        $libSmarty .= '/libs/Smarty.class.php';
        $baseSmarty = dirname(__FILE__) . '/../../SmartyTemplates/';
        $smarty_debugging = (Base_Config::getConfig('smarty_debug', 'true'));
        include_once $libSmarty;
        $objSmarty = new Smarty();
        if ($smarty_debugging) {
            $objSmarty->debugging = true;
        }
        $objSmarty->setTemplateDir($baseSmarty . 'Source');
        $objSmarty->setCompileDir($baseSmarty . '.compiled');
        if (is_array($arrAssignments) and count($arrAssignments) > 0) {
            foreach ($arrAssignments as $key=>$value) {
                $objSmarty->assign($key, $value);
            }
        }
        foreach (Base_Config::getConfig() as $key=>$value) {
            $config[$key] = $value['value'];
        }
        $arrRequestData = Base_Request::getRequest();
        $config['baseurl'] = $arrRequestData['basePath'] . $arrRequestData['pathSite'];
        $objSmarty->assign('SiteConfig', $config);
        if (file_exists($baseSmarty . 'Source/' . $template . '.html')) {
            $objSmarty->display($template . '.html');
        } else {
            $objSmarty->display('Generic_Object.html');
        }
        
    }

}