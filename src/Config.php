<?php

namespace PhpConfig;

use PhpConfig\Exception\InvalidProviderNameException;

class Config
{
	protected ?ConfigStorage $baseStorage = null;

	/** @var ConfigStorage[] */
	protected array $storages = [];

	public function __construct(ConfigProvider $provider)
	{
		$this->baseStorage = $this->createConfigStorage($provider);
	}

	/**
	 * @param string $namespace
	 * @param ConfigProvider $provider
	 */
	public function addProvider(string $namespace, ConfigProvider $provider): void
	{
		if(array_key_exists($namespace, $this->storages))
			throw new InvalidProviderNameException("Provider name [$namespace] is already exist!");

		$this->storages[$namespace] = $this->createConfigStorage($provider);
	}

	/**
	 * @param string $key
	 * @param callable|mixed $default
	 * @return mixed
	 */
	public function get(string $key, $default = null)
	{
		return $this->getStorageFromKey($key)->get($this->getKeyWithoutProvider($key), $default);
	}

	/**
	 * @param string $key
	 * @param int|string|array|null $value
	 * @return mixed
	 */
	public function set(string $key, $value): ConfigStorage
	{
		return $this->getStorageFromKey($key)->set($this->getKeyWithoutProvider($key), $value);
	}

	public function save(?string $name = null, bool $beautify = true): bool
	{
		if(is_null($name))
			return $this->baseStorage->save(null, $beautify);

		return $this->getStorageFromKey($name)->save($this->getKeyWithoutProvider($name), $beautify);
	}

	/**
	 * @param string $key
	 * @return ConfigStorage
	 */
	protected function getStorageFromKey(string $key): ConfigStorage
	{
		if($this->hasProviderInKey($key))
		{
			$segments = explode('.', $key);
			$providerName = str_replace('@', '', $segments[0]);

			if(!array_key_exists($providerName, $this->storages))
				throw new InvalidProviderNameException("Provider name [$providerName] is not found!");

			return $this->storages[$providerName];
		}
		return $this->baseStorage;
	}

	/**
	 * @param string $key
	 * @return string
	 */
	protected function getKeyWithoutProvider(string $key): string
	{
		if($this->hasProviderInKey($key))
			return implode('.', array_slice(explode('.', $key), 1));
		return $key;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	protected function hasProviderInKey(string $key): bool
	{
		return stripos($key, '@') === 0;
	}

	/**
	 * @param ConfigProvider $provider
	 * @return ConfigStorage
	 */
	protected function createConfigStorage(ConfigProvider $provider): ConfigStorage
	{
		return new ConfigStorage($provider);
	}

}