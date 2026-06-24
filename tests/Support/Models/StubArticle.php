<?php

namespace Framework\Tests\Support\Models;

use Framework\Database\Query\Model;

class StubArticle extends Model
{
  /**
   * The database table name.
   *
   * @var string
   */
    protected $table = 'test_articles';

  /**
   * The primary key column name.
   *
   * @var string
   */
    protected $primary_key = 'id';

  /**
   * Mass assignable attributes.
   *
   * @var array
   */
    protected $fillable = [
        'title',
    ];
}
