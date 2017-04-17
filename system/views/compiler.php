<?php
namespace System\Views;
/**
 *
 */
class Compiler
{
	public $content = '';
	protected $compilers = [];

    public function setCompiler($name, $regex, $replace) {
		$this->compilers[$name] = [
			'regex' => $regex,
			'replace' => $replace
		];
		return $this;
    }


    public function getCompiler($name) {
		return $this->content = preg_replace_callback(
			$this->compilers[$name]['regex'],
			$this->compilers[$name]['replace'],
			$this->content
		);
    }

	public function runCompiler(array $list, $except = false)
	{
		if ($except === true){
			foreach ($this->compilers as $key => $value) {
				if(!in_array($key, $list)){
					$this->getCompiler($key);
				}
			}
		} else {
			foreach ($list as $name) {
				$this->getCompiler($name);
			}
		}
		return $this->content;
	}

	public function runCompilerAll()
	{
		foreach ($this->compilers as $key => $value) {
			$this->getCompiler($key);
		}
		return $this->content;
	}

}
