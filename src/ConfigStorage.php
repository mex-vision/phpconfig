<?php

namespace PhpConfig;

class ConfigStorage
{
	protected array $data = [];

	public function __construct(array $data)
	{
		$this->data = $data;
	}

	/**
	 * @param string $key
	 * @param callable|mixed $default
	 * @return mixed
	 */
	public function get(string $key, $default = null)
	{
		return $this->find($this->data, explode(".", $key, $default));
	}

	/**
	 * @param mixed $array
	 * @param array $segments
	 * @param callable|mixed $default
	 * @return mixed
	 */
	protected function find(&$array, array $segments, $default = null)
	{
		if(is_array($array) and array_key_exists($segments[0], $array))
		{
			if(count($segments) > 1)
				return $this->find($array[$segments[0]], array_slice($segments, 1), $default);
			else
				return $array[$segments[0]];
		}
		return is_callable($default) ? $default() : $default;
	}
}