<?php

namespace PhpConfig;

use PhpConfig\Exception\InvalidProviderNameException;

class Config
{
	protected ?ConfigStorage $baseStorage = null;

	/** @var ConfigStorage[] */
	protected array $storages = [];

	public function __construct(ConfigProvider $provider = null)
	{
		if(!is_null($provider))
			$this->baseStorage = $this->getConfigStorage($provider);
	}

	public function addProvider(string $name, ConfigProvider $provider): void
	{
		if(array_key_exists($name, $this->storages))
			throw new InvalidProviderNameException("Provider name [$name] is already exist!");

		$this->storages[$name] = $this->getConfigStorage($provider);
	}

	/**
	 * @param string $key
	 * @param callable|mixed $default
	 * @return mixed
	 */
	public function get(string $key, $default = null)
	{
		if(preg_match("#^([A-z0-9-]+):(.*)$#", $key) > 0)
		{
			$segments = explode(':', $key);

			if(!array_key_exists($segments[0], $this->storages))
				throw new InvalidProviderNameException("Provider name [$segments[0]] is not found!");

			return $this->storages[$segments[0]]->get(implode(':', array_slice($segments, 1)), $default);
		}
		return $this->baseStorage->get($key, $default);
	}

	protected function getConfigStorage(ConfigProvider $provider): ConfigStorage
	{
		return new ConfigStorage($provider);
	}
}