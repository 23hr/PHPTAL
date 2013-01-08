<?php
/**
 * PHPTAL templating engine
 *
 * PHP Version 5
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Laurent Bedubourg <lbedubourg@motion-twin.com>
 * @author   Kornel Lesiński <kornel@aardvarkmedia.co.uk>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version  SVN: $Id$
 * @link     http://phptal.org/
 */


/**
 * i18n:source
 *
 *  The i18n:source attribute specifies the language of the text to be
 *  translated. The default is "nothing", which means we don't provide
 *  this information to the translation services.
 *
 *
 * @package PHPTAL
 * @subpackage Php.attribute.i18n
 */
class PHPTAL_Php_Attribute_I18N_Source extends PHPTAL_Php_Attribute
{
    public function before(PHPTAL_Php_CodeWriter $codewriter)
    {
        // ensure that a sources stack exists or create it
        $codewriter->doIf(new PHPTAL_Expr_PHP('!isset($_i18n_sources)'));
        $codewriter->pushCode(new PHPTAL_Expr_PHP('$_i18n_sources = array()'));
        $codewriter->end();

        // push current source and use new one
        $codewriter->pushCode(new PHPTAL_Expr_PHP('$_i18n_sources[] = ',$codewriter->getTranslatorReference(),'->setSource(',new PHPTAL_Expr_String($this->expression),')'));
    }

    public function after(PHPTAL_Php_CodeWriter $codewriter)
    {
        // restore source
        $code = $codewriter->getTranslatorReference().'->setSource(array_pop($_i18n_sources))';
        $codewriter->pushCode($code);
    }
}

