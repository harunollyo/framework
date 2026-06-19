<?php

namespace Example\App\Models;

use Framework\Database\Query\Model;

class Category extends Model
{
    protected $table = 'framework_categories';

    protected $primary_key = 'id';

    protected $casts = [
        'id' => 'integer',
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'created_at',
        'updated_at',
    ];

    public function blogs()
    {
        return $this->has_many(Blog::class);
    }
}
