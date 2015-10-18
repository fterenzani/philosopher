<?php

namespace Socrate;

use Pimple\Container;
use InvalidArgumentException;

class Router
{

	const NOT_FOUND = 'NOT_FOUND';
	const NEXT = 'NEXT';

	public $urls;
	protected $metas;

	protected $pregSpecialChars = ['\\', '+', '*', '?', '[', '^', ']' , '$', '(', ')', 
										'{', '}', '=', '!', '<', '>', '|' , ':'];

	function __construct(Container $container)
	{
		$this->container = $container;
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

		return static::NOT_FOUND;

	}

	function getPath($name, $args = null) 
	{
		$meta = $this->getMeta($name);

		if (is_array($args)) 
		{
			$args = (object) $args;
		}

		$replaces = [];

		for ($i = 0, $j = count($meta['varName']); $i < $j; $i++)
		{
			
			$varName = $meta['varName'][$i];
			$replace = '';

			if (isset($args->{$varName}))
			{
				$replace = $args->{$varName};
			} 
			elseif (($method = 'get' . $this->camelize($varName)) && is_callable(array($args, $method))) 
			{
				$replace = $args->{$method}(); 
			}

			if ($replace)
			{
				$replaces[] = $meta['prefix'][$i].$replace.$meta['suffix'][$i];
			}
			else
			{
				$replaces[] = $replace;
			}
		}

		$path = str_replace($meta['regex'], $replaces, $meta['path']);
		$path = str_replace($this->pregSpecialChars, '', $path);
		
		return $path;

	}

	protected function getMeta($name) 
	{

		if (isset($this->metas[$name])) 
		{
			return $this->metas[$name];
		}

		if (isset($this->urls[$name])) 
		{

			$path = $this->urls[$name][0];
			$path = explode($path[0], $path);
			$path = $path[1];

			preg_match_all('#(?<regex>(?:\((?<prefix>[^\(]*))?\(\?<(?<varName>[^>]+)>[^\)]+\)(?:(?<suffix>[^)]*)\)\?)?)#', $path, $match);

			$this->metas[$name] = [
				'path' => $path, 
				'regex' => $match['regex'], 
				'varName' => $match['varName'], 
				'prefix' => $match['prefix'], 
				'suffix' => $match['suffix']
				];

			return $this->metas[$name];
		}

		throw new InvalidArgumentException($name . ' is not a valid URL identifier');

	}

	protected function camelize($var) 
	{
		return implode('', array_map('ucfirst', explode('_', $var)));
	}


}