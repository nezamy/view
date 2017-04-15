<?php
namespace System\Views;
/**
 *
 */
class Compiler
{

    /**
	 * echoCompiler
	 *
	 * @param   string $cont
	 * @return  string
	 */
	private function echoCompiler($cont)
	{
		$regex = '/(@)?\{[{|!|(][\r\n\s]*(.+?)[\r\n\s]*[}|!|)]\}/is';
		$callback = function ($m) {
			$return = str_replace( array_keys($this->ReplaceEcho), $this->ReplaceEcho, $m[0] );
			return $m[1] ? substr($m[0], 1) : $return;
		};

		return preg_replace_callback($regex, $callback, $cont);
	}

	private function phpTagCompiler($cont)
    {
		$regex = '/(@\{)\s+(.*)\s+(\}@)/is';

		$callback = function ($m) {
			return '<?php ' . $m[2] . '?>';
		};

		return preg_replace_callback($regex, $callback, $cont);
	}


	/**
	 * Compile Blade statements that start with "@".
	 *
	 * @param  string  $value
	 * @return mixed
	 */
   /* protected function compileStatements($value)
	{
		$callback = function ($match) {
			if (method_exists($this, $method = 'compile'.ucfirst($match[1]))) {
				$match[0] = $this->$method( isset($match[3]) ? $match : null );
			} elseif (isset($this->customDirectives[$match[1]])) {
				$match[0] = call_user_func($this->customDirectives[$match[1]], isset($match[3]) ? $match : null);
			}

			return isset($match[3]) ? $match[0] : $match[0].$match[2];
		};

		return preg_replace_callback('/\B@(\w+)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', $callback, $value);
	}*/


	/**
	 * @param $cont
	 * @param bool|false $layout
	 * @return mixed
	 */
	private function startSectionCompiler($cont, $layout=false) {
		$reg = '/(@section\()([\'|"][a-z0-9.\/_-]+[\'|"])(\))/i';
		return preg_replace_callback($reg, function($s) use($layout) {
			$rep1 = '<?php $this->section(';
			if($layout === true) {
				$rep2 = ');?>';
			}else{
				$rep2 = ', function($ViewBag) { extract($this->data->export(), EXTR_OVERWRITE|EXTR_REFS);?>';
			}
			return $rep1.$s[2].$rep2;
		}, $cont);
	}

	/**
	 * @param $cont
	 * @return mixed
	 */
	private function extendsCompiler($cont) {
		$reg = '/(@extends\()([\'|"][a-z0-9.\/_-]+[\'|"])(\))/i';
		return preg_replace_callback($reg, function($s) {
			$rep1 = '<?php $this->layout(';
			$rep2 = ');?>';
			return $rep1.$s[2].$rep2;
		}, $cont);
	}

	/**
	 * @param $cont
	 * @return mixed
	 */
	private function includeCompiler($cont) {
		$reg = '/(@include\()([\'|"][a-z0-9.\/_-]+[\'|"][,\[\'"a-z0-9=>()\s\]$:_-]*)(\))/i';
		return preg_replace_callback($reg, function($s) {
			$rep1 = '<?php $this->_include(';
			$rep2 = ');?>';
			return $rep1.$s[2].$rep2;
		}, $cont);
	}
}
