<?php

namespace Framework\Tests\Support\Models;

use Framework\Database\Query\Model;

class StubComment extends Model
{
  /**
   * The database table name.
   *
   * @var string
   */
    protected $table = 'test_comments';

  /**
   * The primary key column name.
   *
   * @var string
   */
    protected $primary_key = 'id';
}
