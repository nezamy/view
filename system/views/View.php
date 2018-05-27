<?php
namespace System\Views;
use System\Cache;
use System\Views\ViewCompiler;
/**
 * View
 */
class View
{
    private $config     = [];
    private $sections   = [];
    private $viewBag    = [];


    public function __construct(array $conf = [])
    {
        $this->config = array_merge([
            'path'      => 'views/',
            'theme'     => 'default/',
            'layout'    => 'layout/default.php',
            'render'    => 'default/templates/',
            'cache'     => 'storage/cache',
            'compiler'  => false,
            //===========  echo Compiler
            //escape only
            'contentTags'        => ['{{ ', ' }}'],
            // escape and strip html tags
            'escapedContentTags' => ['{( ', ' )}'],
            //without escape
            'rawTags'            => ['{! ', ' !}']
        ], $conf);

        $this->echoCompiler = [
            $this->config['contentTags'][0]         => '<?= $this->escape(',
            $this->config['contentTags'][1]         => ');?>',
            $this->config['escapedContentTags'][0]  => '<?= $this->escape(',
            $this->config['escapedContentTags'][1]  => ', TRUE);?>',
            $this->config['rawTags'][0]             => '<?= ',
            $this->config['rawTags'][1]             => ';?>',
        ];
    }

    public function getPath($file = null){
        return $this->config['path'] . $this->config['theme'] . $file;
    }

    public function getLayoutPath(){
        return $this->getPath($this->config['layout']);
    }

    public function getRenderPath($tmp){
        return $this->config['path'] . $this->config['render'] . $tmp . '.php';
    }

    /**
	 * Set the default layout
	 *
	 * @param string|bool  $layout 	default is "layout/default.php" or false if u want disable layout
	 *
	 * @return $this
	 */
	public function layout($layout)
    {
		$this->config['layout'] = $layout;
		return $this;
	}

    /**
	 * section in layout is calling section in view if is definded
	 *
	 * @param string   $file   view file path name without .php
	 * @param array    $vars   array of the vars "key => value"
	 *
	 * @return this
	 */
	public function view($file, array $vars = [])
    {
		if ( is_file($file = $this->getPath($file . '.php')) ) {
            $this->data = $vars;
			extract($vars, EXTR_OVERWRITE|EXTR_REFS);
			include($this->cache($file));
            $this->end();
		} else {
            throw new \Exception("Views: File not found $file");
        }

		return $this;
	}

    public function render($parts, array $vars = [])
    {
		foreach ( (array)$parts as $part ) {
			if ( is_file($file = $this->getRenderPath($part)) ) {
                extract($vars, EXTR_OVERWRITE|EXTR_REFS);
                include($this->cache($file));
			} else {
                throw new \Exception("Views: Render File not found $file");
            }
		}
		return $this;
	}

    /**
	 * section with callback
	 * Usage @section('name') , @section('name') some code @end
	 *
	 * @param string   $name
     *
	 * @return string
	 */
	public function section($name, callable $callback = null)
	{
		if($callback){
			$this->sections[$name] = $callback;
		}else{
			return array_key_exists($name, $this->sections) ? $this->sections[$name]($this->data) : null;
		}
	}

    /**
	 * end and include layout
	 */
	public function end()
	{
		if ($this->config['layout'] !== false && is_file($file = $this->getLayoutPath()) ) {
            require($this->cache($file, true));
		}
	}

	/**
	 * share data to all views files
	 *
	 * @param string   $k
	 * @param mixed    $v
	 *
	 * @return mixed
	 */
	public function ViewBag($k=null, $v=null)
    {
		if($v !== null){
			$this->viewBag->$k = $v;
		}elseif($k !== null){
			return $this->viewBag->$k;
		}else{
			return $this->viewBag;
		}
		return $this;
	}

    /**
	 * Cache compiled file
	 *
	 * @param   string $file
	 * @param   bool $layout
	 * @return  string
	 */
	private function cache($file, $layout=false)
	{
        if(!$this->config['compiler']){
            return $file;
        }

		return (new Cache([
            'path' => $this->config['cache']
        ]))->compiled($file, $file, $this->setCacheCompiler($file, $layout));
	}

    /**
	 * Set Cache Compiler
	 *
	 * @param   string $f
	 * @param   bool $layout
	 * @return  string
	 */
	private function setCacheCompiler($f, $layout=false)
	{
        $viewCompiler = new viewCompiler($this);
        $viewCompiler->content = file_get_contents($f);

		if($layout === true) {
			return $viewCompiler->runCompiler([
                'renderCompiler',
                'startLayoutSectionCompiler',
                'echoCompiler'
            ]);
		} else {
			return $viewCompiler->runCompiler(['startLayoutSectionCompiler'], true);
		}
	}

    public function escape($text, $strip=false){
        if($strip){
            return strip_tags($text);
        }
        return htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
    }

    /**
     * Magic call.
     *
     * @param string   $method
     * @param array    $args
     *
     * @return mixed
     */
    public function __call($method, $args)
	{
        return  isset($this->{$method}) && is_callable($this->{$method})
                ? call_user_func_array($this->{$method}, $args) : null;
	}

    /**
     * Set new variables and functions to this class.
     *
     * @param string      $k
     * @param mixed    $v
     */
	public function __set($k, $v)
	{
		$this->{$k} = $v instanceof \Closure ? $v->bindTo($this) : $v;
	}
}
