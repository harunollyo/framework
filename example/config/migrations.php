<?php

use Example\Database\Migrations\CreateEventSpeakerTable;
use Example\Database\Migrations\CreateEventsTable;
use Example\Database\Migrations\CreateSpeakersTable;

return [
    CreateEventsTable::class,
    CreateSpeakersTable::class,
    CreateEventSpeakerTable::class,
];