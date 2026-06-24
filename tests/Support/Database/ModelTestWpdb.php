<?php

namespace Framework\Tests\Support\Database;

class ModelTestWpdb extends TestWpdb
{
  /**
   * Number of rows affected by the last write query.
   *
   * @var int
   */
    public $rows_affected = 0;

  /**
   * Executed SQL statements.
   *
   * @var array
   */
    public $queries = [];

  /**
   * In-memory table rows keyed by fully qualified table name.
   *
   * @var array
   */
    public $table_data = [];

  /**
   * Seed rows for a logical table name (prefix is applied automatically).
   *
   * @param string $table The logical table name without prefix.
   * @param array $rows The rows to store.
   *
   * @return void
   */
    public function seed(string $table, array $rows): void
    {
        $this->table_data[$this->prefix . $table] = $rows;
    }

  /**
   * Clear stored data and query log.
   *
   * @return void
   */
    public function clear(): void
    {
        $this->table_data = [];
        $this->queries = [];
        $this->rows_affected = 0;
    }

  /**
   * Execute a write query against the in-memory store.
   *
   * @param string $sql The SQL statement.
   *
   * @return bool
   */
    public function query($sql)
    {
        $this->queries[] = $sql;

        if (stripos($sql, 'DELETE') !== false) {
            if (preg_match('/DELETE FROM `([^`]+)` WHERE `(\w+)` = (.+)/i', $sql, $matches)) {
                $this->delete_row($matches[1], $matches[2], $this->normalize_sql_value($matches[3]));
            }

            $this->rows_affected = 1;

            return true;
        }

        return true;
    }

  /**
   * Return rows that match a SELECT statement.
   *
   * @param string $sql The SQL statement.
   * @param string $output The output format constant.
   * @param bool $raw Whether to return raw values.
   *
   * @return array
   */
    public function get_results($sql, $output = \OBJECT, $raw = false)
    {
        $this->queries[] = $sql;

        $rows = $this->resolve_select($sql);

        if ($output === \ARRAY_A || $output === 'ARRAY_A') {
            return $rows;
        }

        return array_map(function (array $row) {
            return (object) $row;
        }, $rows);
    }

  /**
   * Resolve a SELECT statement against in-memory data.
   *
   * @param string $sql The SQL statement.
   *
   * @return array
   */
    protected function resolve_select(string $sql): array
    {
        $sql = preg_replace('/\s+limit\s+\d+/i', '', $sql);

        if (!preg_match('/FROM `([^`]+)`/i', $sql, $table_match)) {
            return [];
        }

        $table = $table_match[1];
        $data = $this->table_data[$table] ?? [];

        if (preg_match('/WHERE `(\w+)` IN \(([^)]+)\)/i', $sql, $matches)) {
            $column = $matches[1];
            $values = array_map(function (string $value) {
                return $this->normalize_sql_value($value);
            }, explode(',', $matches[2]));

            return array_values(array_filter($data, function (array $row) use ($column, $values) {
                return $this->value_in_list($row[$column] ?? null, $values);
            }));
        }

        if (preg_match('/WHERE `(\w+)` = ([^\s]+)/i', $sql, $matches)) {
            $column = $matches[1];
            $value = $this->normalize_sql_value($matches[2]);

            return array_values(array_filter($data, function (array $row) use ($column, $value) {
                return isset($row[$column]) && (string) $row[$column] === (string) $value;
            }));
        }

        return $data;
    }

  /**
   * Remove a row from an in-memory table.
   *
   * @param string $table The fully qualified table name.
   * @param string $column The column name.
   * @param string $value The value to match.
   *
   * @return void
   */
    protected function delete_row(string $table, string $column, string $value): void
    {
        if (!isset($this->table_data[$table])) {
            return;
        }

        $this->table_data[$table] = array_values(array_filter(
            $this->table_data[$table],
            function (array $row) use ($column, $value) {
                return !isset($row[$column]) || (string) $row[$column] !== (string) $value;
            }
        ));
    }

  /**
   * Normalize a SQL literal into a scalar string.
   *
   * @param string $value The SQL literal.
   *
   * @return string
   */
    protected function normalize_sql_value(string $value): string
    {
        $value = trim($value);

        if ($value === 'NULL') {
            return '';
        }

        return trim($value, "'\"");
    }

  /**
   * Determine whether a value is present in a list of SQL literals.
   *
   * @param mixed $value The value to check.
   * @param array $values The list of normalized values.
   *
   * @return bool
   */
    protected function value_in_list($value, array $values): bool
    {
        foreach ($values as $candidate) {
            if ((string) $value === (string) $candidate) {
                return true;
            }
        }

        return false;
    }
}
