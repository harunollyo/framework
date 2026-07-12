<?php

namespace Example\App\Http\Requests;

use Framework\Sanitizer;
use Framework\Http\Request;

class ExampleRequest extends Request
{
    public function rules()
    {
        return [
            'name' => 'string|max:3',
            'email' => 'string|email',
        ];
    }

    public function filters()
    {
        return [
            'name' => Sanitizer::TEXT,
            'email' => Sanitizer::EMAIL,
        ];
    }

    public function messages()
    {
        return [
            'name' => [
                'string' => 'The `name` must be a string.',
                'max' => 'The `name` field must be at most 3 characters long.',
            ],
            'email' => [
                'string' => 'The `email` must be a string.',
                'email' => fn ($key, $value) => sprintf('The `%s` field must be a valid email address. Found: %s', $key, $value),
            ],
        ];
    }
}
