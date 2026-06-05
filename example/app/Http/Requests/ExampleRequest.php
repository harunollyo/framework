<?php

namespace Example\App\Http\Requests;

use Framework\Sanitizer;
use Framework\Http\Request;

class ExampleRequest extends Request
{
    public function rules()
    {
        return [
            'data' => 'required|array',
            'name' => 'required|string',
        ];
    }

    public function filters()
    {
        return [
            'data' => Sanitizer::ARRAY,
            'name' => Sanitizer::TRIM,
        ];
    }
}
