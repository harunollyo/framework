<?php

namespace Example\App\Http\Requests;

use Framework\Sanitizer;
use Framework\Http\Request;

class ExampleRequest extends Request
{
    public function rules()
    {
        return [
            'name' => 'required|string',
            'data' => 'required|array',
            'data.name' => 'required|string|min:3',
            'data.age' => 'required|integer',
        ];
    }

    public function filters()
    {
        return [
            'name' => Sanitizer::TEXT,
            'age' => Sanitizer::INT,
            'options' => Sanitizer::ARRAY,
        ];
    }

    public function messages()
    {
        return [
            'data.name' => [
                'required' => 'You have to provide a value for the name field.',
                // 'string' => fn ($key) => sprintf('The "%s" field must be a string.', $key),
                'min' => fn ($key, $value) => sprintf('The "%s" field must be greater than or equal to %s characters.', $key, $value),
            ],
        ];
    }
}
