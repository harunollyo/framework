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
}
