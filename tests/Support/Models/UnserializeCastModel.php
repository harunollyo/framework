<?php

namespace Framework\Tests\Support\Models;

use Framework\Database\Query\Model;

class UnserializeCastModel extends Model
{
  /**
   * The database table name.
   *
   * @var string
   */
    protected $table = 'test_unserialize_cast';

  /**
   * Attribute casts.
   *
   * @var array
   */
    protected $casts = [
        'payload' => 'unserialize',
    ];
}
