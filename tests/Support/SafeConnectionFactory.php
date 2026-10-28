<?php

namespace Tests\Support;

use Illuminate\Database\Connectors\ConnectionFactory;
use LogicException;

class SafeConnectionFactory extends ConnectionFactory
{
    public function make(array $config, $name = null)
    {
        if (($config['driver'] ?? null) !== 'sqlite'
            || ($config['database'] ?? null) !== ':memory:'
            || ! empty($config['url']) || isset($config['read']) || isset($config['write'])) {
            throw new LogicException('Tests may connect only to SQLite :memory:. Persistent database access is blocked.');
        }

        return parent::make($config, $name);
    }
}
