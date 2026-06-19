<?php

use Example\Database\Migrations\CreateBlogSeoTable;
use Example\Database\Migrations\CreateBlogsTable;
use Example\Database\Migrations\CreateBlogTagTable;
use Example\Database\Migrations\CreateCategoriesTable;
use Example\Database\Migrations\CreateCommentsTable;
use Example\Database\Migrations\CreateEventSpeakerTable;
use Example\Database\Migrations\CreateEventsTable;
use Example\Database\Migrations\CreateSpeakersTable;
use Example\Database\Migrations\CreateTagsTable;
use Example\Database\Migrations\CreateReplyTable;

return [
    CreateCategoriesTable::class,
    CreateTagsTable::class,
    CreateBlogsTable::class,
    CreateBlogSeoTable::class,
    CreateCommentsTable::class,
    CreateBlogTagTable::class,
    CreateEventsTable::class,
    CreateSpeakersTable::class,
    CreateEventSpeakerTable::class,
    CreateReplyTable::class,
];
