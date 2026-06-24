<?php

namespace Framework\Tests\Support\Models;

use Framework\Database\Query\Model;

class StubSlugPost extends Model
{
  /**
   * The database table name.
   *
   * @var string
   */
    protected $table = 'test_slug_posts';

  /**
   * The primary key column name.
   *
   * @var string
   */
    protected $primary_key = 'slug';

  /**
   * The type of the primary key.
   *
   * @var string
   */
    protected $key_type = 'string';

  /**
   * Mass assignable attributes.
   *
   * @var array
   */
    protected $fillable = [
        'title',
    ];
}
