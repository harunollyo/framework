<?php

namespace Example\App\Policies;

use Example\App\Models\Blog;
use Framework\Http\Request;
use Framework\Wordpress\User;


class BlogPolicy
{
    public function update(User $user, Blog $blog, Request $request)
    {
        return $user->get_id() === (int) $blog->user_id;
    }
}