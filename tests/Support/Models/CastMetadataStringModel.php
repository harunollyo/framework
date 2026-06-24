<?php

namespace Framework\Tests\Support\Models;

use Framework\Database\Query\Model;

class CastMetadataStringModel extends Model
{
  /**
   * The database table name.
   *
   * @var string
   */
    protected $table = 'test_cast_string';

  /**
   * Attribute casts.
   *
   * @var array
   */
    protected $casts = [
        'metadata' => 'string',
    ];
}
