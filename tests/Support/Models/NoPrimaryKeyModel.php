<?php

namespace Framework\Tests\Support\Models;

use Framework\Database\Query\Model;

class NoPrimaryKeyModel extends Model
{
  /**
   * The database table name.
   *
   * @var string
   */
    protected $table = 'test_no_primary_key';

  /**
   * Return null to simulate a model without a primary key.
   *
   * @return null
   */
    public function get_primary_key()
    {
        return null;
    }
}
