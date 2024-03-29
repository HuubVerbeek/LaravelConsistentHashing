<?php

namespace HuubVerbeek\ConsistentHashing\Implementations;

use HuubVerbeek\ConsistentHashing\Contracts\SetterContract;
use HuubVerbeek\ConsistentHashing\Exceptions\ReservedCacheKeyException;
use HuubVerbeek\ConsistentHashing\Rules\ReservedCacheKeyRule;
use HuubVerbeek\ConsistentHashing\Traits\Validator;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;

class CacheSetter extends StoreImplementation implements SetterContract
{
    use Validator;

    private const RESERVED_CACHE_KEYS = ['all_cached_keys'];

    /**
     * @throws ReservedCacheKeyException
     * @throws \Throwable
     */
    public function set(string $key, mixed $value): void
    {
        $this->validate(
            new ReservedCacheKeyRule($key, self::RESERVED_CACHE_KEYS),
            new ReservedCacheKeyException()
        );

        Cache::store($this->node->identifier)->put($key, $value);

        $this->addKeyToKeysList($key);
    }

    public function forget(string $key): void
    {
        Cache::store($this->node->identifier)->forget($key);

        $this->removeKeyFromKeysList($key);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function addKeyToKeysList(string $key): void
    {
        $keys = Cache::store($this->node->identifier)->get('all_cached_keys') ?? [];

        Cache::store($this->node->identifier)->put('all_cached_keys', array_merge($keys, [$key]));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function removeKeyFromKeysList(string $key): void
    {
        $keys = Cache::store($this->node->identifier)->get('all_cached_keys') ?? [];

        if (($key = array_search($key, $keys)) !== false) {
            unset($keys[$key]);
        }

        Cache::store($this->node->identifier)->put('all_cached_keys', $keys);
    }
}
