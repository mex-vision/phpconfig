<?php

namespace PhpConfig;

use PhpConfig\Exception\NotWritableException;
use PhpConfig\Exception\PathNotFoundException;

class ConfigProvider
{
	protected string    $configDir;
	protected string    $suffix;

	protected array     $configPaths = [];

	public function __construct(string $configDir, string $suffix = '')
	{
		$this->configDir = trim($configDir, '\/') . '/';
		$this->suffix = $suffix;

		if(!is_dir($this->configDir))
			throw new PathNotFoundException('Directory [' . $this->configDir . '] is not found!');

		if(!is_writable($this->configDir))
			throw new NotWritableException('Directory [' . $this->configDir . '] is not writable!');

		foreach (scandir($this->configDir) as $file)
		{
			if(strpos($file, $this->suffix . '.php') === false)
				continue;
			$name = str_replace($this->suffix . '.php', '', $file);
			$this->configPaths[$name] = $this->configDir . $file;
		}
	}

	public function getPaths(): array
	{
		return $this->configPaths;
	}

	public function getPath(string $name): ?string
	{
		if(array_key_exists($name, $this->configPaths))
			return $this->configPaths[$name];
		return null;
	}

	public function compilePath(string $name): string
	{
		return $this->configDir . $name . $this->suffix . '.php';
	}

	public function getConfigDir(): string
	{
		return $this->configDir;
	}

	public function getSuffix(): string
	{
		return $this->suffix;
	}


}