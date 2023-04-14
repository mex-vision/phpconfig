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

	/**
	 * @param string $key
	 * @param int|string|array|null $value
	 * @return mixed
	 */
	public function set(string $key, $value): ConfigStorage
	{
		$segments = array_reverse(explode('.', $key));

		if(count($segments) == 1)
			$this->data = $this->merge($this->data, [$segments[0] => [$value]]);

		$result = [];
		foreach ($segments as $segment)
		{
			if(empty($result))
				$result[$segment] = $value;
			else
			{
				$result[$segment] = $result;
				unset($result[array_key_first($result)]);
			}
		}

		$this->data = $this->merge($this->data, $result);
		return $this;
	}

	/**
	 * @param string|null $name
	 * @param bool $beautify
	 * @return bool
	 */
	public function save(?string $name = null, bool $beautify = true): bool
	{
		if(is_null($name) or $name == '')
		{
			foreach ($this->data as $filename => $content)
				file_put_contents(
					$this->provider->compilePath($filename),
					"<?php\n\n" . $this->getCommentForSave() . "return " . $this->arrayExport($content, $beautify) . ";"
				);
			return true;
		}
		if(array_key_exists($name, $this->data))
		{
			file_put_contents(
				$this->provider->compilePath($name),
				"<?php\n\n" . $this->getCommentForSave() . "return " . $this->arrayExport($this->data[$name], $beautify) . ";"
			);
			return true;
		}
		return false;
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

	/**
	 * @param array $to
	 * @param array $from
	 * @return array
	 */
	protected function merge(array $to, array $from): array
	{
		foreach ($from as $key => $value)
		{
			if(!isset($to[$key]) or !is_array($to[$key]))
			{
				$to[$key] = $value;
				continue;
			}
			if(is_array($value))
				$to[$key] = $this->merge($to[$key], $value);
			else
				$to[$key] = $value;
		}
		return $to;
	}

	/**
	 * @param array $array
	 * @param bool $beautify
	 * @param int $iteration
	 * @return string
	 */
	protected function arrayExport(array $array, bool $beautify, int $iteration = 0): string
	{
		if(empty($array))
			return "[]";

		$tab = $beautify ? str_repeat("\t", $iteration) : "";
		$result = $beautify ? "[\n" : "[";
		$count = count($array);

		foreach ($array as $key => $val)
		{
			$count--;
			$result .= $beautify ? ($tab . "\t") : "";
			$result .= $this->toArrayElement($key);
			$result .= $beautify ? " => " : "=>";

			if(is_array($val))
				$result .= $this->arrayExport($val, $beautify, $iteration + 1);
			else
				$result .= $this->toArrayElement($val);

			$result .= ($count > 0) ? "," : "";
			$result .= $beautify ? "\n" : "";
		}
		return $beautify ? $result . $tab . "]" : $result . "]";
	}

	/**
	 * @param $value
	 * @return string
	 */
	protected function toArrayElement($value) : string
	{
		if(is_int($value) or is_float($value))
			return $value;
		if(is_bool($value))
			return var_export($value, true);
		if(is_null($value))
			return 'null';
		if(is_object($value))
			return get_class($value) . "::class";
		return '"' . ((string) $value) . '"';
	}

	/**
	 * @return string
	 */
	protected function getCommentForSave(): string
	{
		$result  = "// ===============================================================================\n";
		$result .= "// Last change time: ".date("d.m.Y в H:i:s")."\n";
		$result .= "// ===============================================================================\n\n";
		return $result;
	}

}