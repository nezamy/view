<?php
namespace System\Views;
use System\Views\Compiler;
/**
 *
 */
class ViewCompiler extends Compiler
{

    function __construct(View $view)
    {
        // extendsCompiler =======================
        $this->setCompiler(
            'extendsCompiler',
            '/(@extends\()([\'|"][a-z0-9.\/_-]+[\'|"])(\))/i',
            function($match){
                $rep1 = '<?php $this->layout(';
    			$rep2 = ');?>';
    			return $rep1.$match[2].$rep2;
            }
        );

        // renderCompiler =======================
        $this->setCompiler(
            'renderCompiler',
            '/(@render\()([\'|"][a-z0-9.\/_-]+[\'|"][,\[\'"a-z0-9=>()\s\]$:_-]*)(\))/i',
            function($match){
                $rep1 = '<?php $this->render(';
    			$rep2 = ');?>';
    			return $rep1.$match[2].$rep2;
            }
        );

        // startSectionCompiler =======================
        $this->setCompiler(
            'startSectionCompiler',
            '/(@section\()([\'|"][a-z0-9.\/_-]+[\'|"])(\))/i',
            function($match){
                $rep1 = '<?php $this->section(';
    			$rep2 = ', function($ViewBag) { extract($this->data, EXTR_OVERWRITE|EXTR_REFS);?>';
    			return $rep1.$match[2].$rep2;
            }
        );

        // startLayoutSectionCompiler =======================
        $this->setCompiler(
            'startLayoutSectionCompiler',
            '/(@section\()([\'|"][a-z0-9.\/_-]+[\'|"])(\))/i',
            function($match){
                $rep1 = '<?php $this->section(';
    			$rep2 = ');?>';
    			return $rep1.$match[2].$rep2;
            }
        );

        // endSectionCompiler =======================
        $this->setCompiler(
            'endSectionCompiler',
            '/(@end)\b/i',
            function($match){
                return '<?php });?>';
            }
        );

        // echoCompiler =======================
        $this->setCompiler(
            'echoCompiler',
            '/(@)?\{[{|!|(][\r\n\s]*(.+?)[\r\n\s]*[}|!|)]\}/is',
            function($match) use ($view) {
                $return = str_replace( array_keys($view->echoCompiler), $view->echoCompiler, $match[0] );
                return $match[1] ? substr($match[0], 1) : $return;
            }
        );

        // phpTagCompiler =======================
        $this->setCompiler(
            'phpTagCompiler',
            '/(@\{)\s+(.*)\s+(\}@)/is',
            function($match){
                return '<?php ' . $match[2] . '?>';
            }
        );

    }
}#
