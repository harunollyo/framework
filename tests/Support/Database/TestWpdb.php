<?php

namespace Framework\Tests\Support\Database;

class TestWpdb
{
    public $prefix;

    public $charset;

    public $collate;

    public $last_error = '';

    public $queries = [];

    public $results = [];

    public function __construct(array $config = [])
    {
        $this->prefix = $config['prefix'] ?? 'wp_';
        $this->charset = $config['charset'] ?? 'utf8mb4';
        $this->collate = $config['collate'] ?? 'utf8mb4_unicode_ci';
        $this->results = $config['results'] ?? [];
        $this->last_error = $config['last_error'] ?? '';
    }

    public function query($query)
    {
        $this->queries[] = $query;

        return true;
    }

    public function get_results($query, $output = null)
    {
        $this->queries[] = $query;

        return $this->results;
    }

    public function prepare($query, ...$args)
    {
        if (count($args) === 1 && is_array($args[0])) {
            $args = $args[0];
        }

        if (empty($args)) {
            return $query;
        }

        $index = 0;

        return preg_replace_callback('/%[dfs]/', function () use (&$index, $args) {
            $value = $args[$index] ?? '';
            $index++;

            if (is_null($value)) {
                return 'NULL';
            }

            if (is_int($value) || is_float($value)) {
                return (string) $value;
            }

            return "'" . addslashes((string) $value) . "'";
        }, $query);
    }
}
