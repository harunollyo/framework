<?php

namespace Framework\Tests\Support\Models;

use Framework\Database\Query\Model;

class TotallyGuardedModel extends Model
{
  /**
   * The database table name.
   *
   * @var string
   */
    protected $table = 'test_totally_guarded';

  /**
   * Guarded attributes.
   *
   * @var array
   */
    protected $guarded = ['*'];
}
