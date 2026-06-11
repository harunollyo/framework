<?php

namespace Framework\Polyfill;

if (!class_exists('Framework\Polyfill\ArgumentCountError', false)) {
    if (class_exists('ArgumentCountError', false)) {
        /**
         * Namespaced wrapper for the native ArgumentCountError class.
         *
         * @since 1.0.0
         */
        class ArgumentCountError extends \ArgumentCountError
        {
        }
    } else {
        /**
         * Polyfill for the ArgumentCountError class introduced in PHP 7.1.
         *
         * @since 1.0.0
         */
        class ArgumentCountError extends \Error
        {
        }
    }
}
