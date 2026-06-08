<?php

namespace Example\App\Policies;

use Example\App\Models\Event;
use Framework\Wordpress\User;

class EventPolicy
{
    public function view(User $user, Event $event)
    {
        return false;
    }
}