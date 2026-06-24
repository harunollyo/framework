<?php

namespace Framework\Tests\Support\Models;

use Framework\Database\Query\Model;

class StrictFillableModel extends Model
{
  /**
   * The database table name.
   *
   * @var string
   */
    protected $table = 'test_strict_fillable';

  /**
   * Mass assignable attributes.
   *
   * @var array
   */
    protected $fillable = [
        'title',
    ];
}
