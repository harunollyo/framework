<?php

namespace Framework\Validation\Constants;

use Framework\Validation\Rules\AfterRule;
use Framework\Validation\Rules\ArrayRule;
use Framework\Validation\Rules\BooleanRule;
use Framework\Validation\Rules\DateFormatRule;
use Framework\Validation\Rules\DateRule;
use Framework\Validation\Rules\DateTimeRule;
use Framework\Validation\Rules\EmailRule;
use Framework\Validation\Rules\EmailUniqueRule;
use Framework\Validation\Rules\ExistsRule;
use Framework\Validation\Rules\FloatRule;
use Framework\Validation\Rules\GreaterThanEqualRule;
use Framework\Validation\Rules\GreaterThanRule;
use Framework\Validation\Rules\InRule;
use Framework\Validation\Rules\IntegerRule;
use Framework\Validation\Rules\IsValidImageIdRule;
use Framework\Validation\Rules\LessThanEqualRule;
use Framework\Validation\Rules\LessThanRule;
use Framework\Validation\Rules\MaxRule;
use Framework\Validation\Rules\MinRule;
use Framework\Validation\Rules\NotInRule;
use Framework\Validation\Rules\NullableRule;
use Framework\Validation\Rules\NumberRule;
use Framework\Validation\Rules\ObjectRule;
use Framework\Validation\Rules\ProhibitedIfRule;
use Framework\Validation\Rules\RegexRule;
use Framework\Validation\Rules\RequiredIfExists;
use Framework\Validation\Rules\RequiredIfRule;
use Framework\Validation\Rules\RequiredIfSiblingRule;
use Framework\Validation\Rules\RequiredRule;
use Framework\Validation\Rules\SameAsRule;
use Framework\Validation\Rules\Sanitizer;
use Framework\Validation\Rules\StringRule;
use Framework\Validation\Rules\UniqueRule;
use Framework\Validation\Rules\UrlRule;
use Framework\Validation\Rules\UserExists;

class Validation
{
    /**
     * The rules to validator method map
     * 
     * @var array
     * 
     * @since 3.3.0
     */
    const RULE_MAP = [
        'required' => RequiredRule::class,
        'string' => StringRule::class,
        'array' => ArrayRule::class,
        'object' => ObjectRule::class,
        'boolean' => BooleanRule::class,
        'integer' => IntegerRule::class,
        'number' => NumberRule::class,
        'float' => FloatRule::class,
        'email' => EmailRule::class,
        'email_unique' => EmailUniqueRule::class,
        'unique' => UniqueRule::class,
        'url' => UrlRule::class,
        'exists' => ExistsRule::class,
        'min' => MinRule::class,
        'max' => MaxRule::class,
        'in' => InRule::class,
        'not_in' => NotInRule::class,
        'regex' => RegexRule::class,
        'sanitize' => Sanitizer::class,
        'same_as' => SameAsRule::class,
        'nullable' => NullableRule::class,
        'date' => DateRule::class,
        'datetime' => DateTimeRule::class,
        'date_format' => DateFormatRule::class,
        'is_valid_image_id' => IsValidImageIdRule::class,
        'required_if' => RequiredIfRule::class,
        'required_if_sibling' => RequiredIfSiblingRule::class,
        'prohibited_if' => ProhibitedIfRule::class,
        'required_if_exists' => RequiredIfExists::class,
        'user_exists' => UserExists::class,
        'after' => AfterRule::class,
        'gt' => GreaterThanRule::class,
        'gte' => GreaterThanEqualRule::class,
        'lt' => LessThanRule::class,
        'lte' => LessThanEqualRule::class,
    ];
}
