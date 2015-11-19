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

				$defaults = isset($rule[2]) && is_array($rule[2])? $rule[2]: [];

				$_GET = array_merge($_GET, $defaults, $match);
				
				if (is_callable($rule[1])) 
				{
					$return = $rule[1]($container);
				}

				elseif (class_exists($rule[1]))
				{
					$return = new $rule[1]($container);
				}

				elseif (strpos($rule[1], '@'))
				{
					$subRule = explode('@', $rule[1]);
					
					$controller = new $subRule[0]($container);
					$return = $controller->{$subRule[1]}();
				}

				else 
				{
					$return = require $rule[1];
				}

				if ($return !== static::NEXT) {
					return $return;
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

			if ($replace) {
				$replace = $meta['prefix'][$i].$replace.$meta['suffix'][$i];
			}

			$replaces[] = $replace;

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

			// Split the regex into characters 
			$chars = str_split($path);

			$level = 0;
			$subpattern = '';
			$name = '';
			$nameRecording = false;
			$prefix = '';
			$suffix = '';
			$nameLevel = 0;
			$subpatterns = [];
			$names = [];
			$prefixes = [];
			$suffixes = [];

			// Loop through the regex
			for($i = 0, $j = count($chars); $i < $j; $i++) {

				$prev = $i > 0? $chars[$i-1]: null;
				$char = $chars[$i];

				// Subpattern recording

				// Sub pattern starts
				if ($char === '(' && $prev !== '\\') {
					$level++;

				// Subpattern stops
				} elseif ($char === ')' && $prev !== '\\') {
					$level--;
				}

				// Record the subpattern at the first level
				if ($level > 0) {
					$subpattern .= $char;

				// First level sub pattern ended: update the list of subpatterns
				} elseif ($level === 0 && $subpattern) {
					$subpattern .= $char;
					if ($name) {
						$names[] = $name;
						$subpatterns[] = $subpattern;
						$prefixes[] = $prefix;
						$suffixes[] = $suffix;
					}
					$subpattern = '';
					$name = '';
					$prefix = '';
					$suffix = '';
					$level = 0;
				}

				// Subpattern name recording

				// Name starts
				if (!$name && $char === '<' && $prev !== '\\') {
					$nameRecording = true;
					$prefix = $subpattern;
					$nameLevel = $level;

				// Name stops
				} elseif ($nameRecording && $char === '>' && $prev !== '\\') {
					$nameRecording = false;

				// Name recording
				} elseif ($nameRecording) {
					$name .= $char;
				}

				if (!$nameRecording && $prefix && $level < $nameLevel) {
					$suffix .= $char;
				}

			}

			$this->metas[$name] = [
				'path' => $path, 
				'regex' => $subpatterns,
				'varName' => $names,
				'prefix' => $prefixes,
				'suffix' => $suffixes,
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