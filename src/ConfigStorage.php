<?php

namespace PhpConfig;

class ConfigStorage
{
	protected ConfigProvider $provider;
	protected array $data = [];

	public function __construct(ConfigProvider $provider)
	{
		$this->provider = $provider;
		foreach ($provider->getPaths() as $name => $path)
		{
			$this->data[$name] = include $path;
		}
	}

	/**
	 * @param string $key
	 * @param callable|mixed $default
	 * @return mixed
	 */
	public function get(string $key, $default = null)
	{
		return $this->find($this->data, explode(".", $key), $default);
	}

	public function getProvider(): ConfigProvider
	{
		return $this->provider;
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
		return is_callable($default) ? $default($this->data) : $default;
	}
}