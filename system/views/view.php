<?php
namespace System\Views;
/**
 * View
 */
class view
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
            'render'    => 'templates/',
        ], $conf);
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
			include($file);
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
                include($file);
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
            require($file);
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
