<?php

namespace Socrate;

use Pimple\Container;
use InvalidArgumentException;

class Router
{

	public $urls;
	protected $metas;

	protected $pregSpecialChars = ['\\', '+', '*', '?', '[', '^', ']' , '$', '(', ')', '{', '}', '=', '!', '<', '>', '|' , ':', '-'];

	function __construct(Container $container)
	{
		$this->container = $container;
	}

	function getPath($name, $args = null) 
	{
		$meta = $this->getMeta($name);

		if (is_array($args)) 
		{
			$args = (object) $args;
		}

		foreach ($meta['varName'] as $varName) 
		{
			if (isset($args->{$varName}))
			{
				$replace[] = $args->{$varName};
			} 
			elseif (($method = 'get' . $this->camelize($varName)) && is_callable(array($args, $method))) 
			{
				$replace[] = $args->{'get' . $this->camelize($varName)}(); 
			}
			else 
			{
				$replace[] = '';
			}
		}

		$path = str_replace($meta['regex'], $replace, $meta['path']);
		$path = str_replace($this->pregSpecialChars, '', $path);
		
		return $path;

	}

	function getMeta($name) 
	{

		if (isset($this->metas[$name])) 
		{
			return $this->metas[$name];
		}

		if (isset($this->urls[$name])) 
		{
			preg_match_all('#(?<regex>\(\?<(?<varName>[^>]+)>[^\)]+\))#', $this->urls[$name][0], $match);
			$path = $this->urls[$name][0];
			$path = explode($path[0], $path);
			$this->metas[$name] = ['path' => $path[1], 'regex' => $match['regex'], 'varName' => $match['varName']];

			return $this->metas[$name];
		}

		throw new InvalidArgumentException($name . ' is not a valid URL identifier');

	}

	function camelize($var) 
	{
		return implode('', array_map('ucfirst', explode('_', $var)));
	}

	function dispatch($request) 
	{

		$container = $this->container;

		foreach ($this->urls as $rule) 
		{
		
			if (preg_match($rule[0], $request, $match)) 
			{

				$_GET = array_merge($_GET, $match);

				if (is_callable($rule[1])) 
				{
					return $rule[1]($container);
				}

				elseif (class_exists($rule[1]))
				{
					return new $rule[1]($container);
				}

				elseif (strpos($rule[1], '@'))
				{
					$subRule = explode('@', $rule[1]);
					
					$controller = new $subRule[0]($container);
					return $controller->{$subRule[1]}();
				}

				else 
				{
					return require $rule[1];
				}

			}

		}

		throw new Http404;

	}

}