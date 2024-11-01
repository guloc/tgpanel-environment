<?php

/**
 * Class Cache_model
 */

class Cache_model extends CI_Model {
    private $adapter;
    private $enable_for_cli = true;
    /**
     * Cache_model constructor.
     */
    function __construct()
    {
        parent::__construct();
        $driver = $this->config->item('cache_driver');
        $this->load->driver('cache', array('adapter' => $driver));
        if ($driver == 'memcached') {
            $this->adapter = $this->cache->memcached;
        } else {
            $this->adapter = $this->cache;
        }
    }
    
    public function get($key)
    {
        return ( ! is_cli() or $this->enable_for_cli)
               ? $this->adapter->get($key)
               : false;
    }

    public function save($key, $data, $ttl=60)
    {
        return ( ! is_cli() or $this->enable_for_cli)
               ? $this->adapter->save($key, $data, $ttl)
               : false;
    }

    public function delete($key)
    {
        return $this->adapter->delete($key);
    }

    public function increment($key, $ttl=30, $offset=1)
    {
        $value = $this->get($key);
        $value = is_int($value)
               ? $value + $offset
               : $offset;
        $this->save($key, $value, $ttl);
        return $value;
    }

    public function decrement($key, $ttl=30, $offset=1)
    {
        $value = $this->get($key);
        $value = is_int($value)
               ? $value - $offset
               : - $offset;
        $this->save($key, $value, $ttl);
        return $value;
        
    }

    public function clean()
    {
        return $this->adapter->clean();
    }
}