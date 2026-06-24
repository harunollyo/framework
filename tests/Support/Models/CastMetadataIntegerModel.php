<?php

namespace Framework\Tests\Support\Models;

use Framework\Database\Query\Model;

class CastMetadataIntegerModel extends Model
{
  /**
   * The database table name.
   *
   * @var string
   */
    protected $table = 'test_cast_integer';

  /**
   * Attribute casts.
   *
   * @var array
   */
    protected $casts = [
        'metadata' => 'integer',
    ];
}
