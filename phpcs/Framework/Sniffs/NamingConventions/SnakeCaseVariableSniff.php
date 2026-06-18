<?php

require_once dirname(__DIR__) . '/Support/SniffHelper.php';

/**
 * Enforces snake_case variable names for properties and local variables.
 *
 * @since 1.0.0
 */
class Framework_Sniffs_NamingConventions_SnakeCaseVariableSniff implements PHP_CodeSniffer\Sniffs\Sniff
{
    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * @return array
     * @since 1.0.0
     */
    public function register()
    {
        return [T_VARIABLE];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer\Files\File $phpcs_file The file being scanned.
     * @param int                        $stack_ptr  The position of the current token.
     *
     * @return void
     * @since 1.0.0
     */
    public function process(PHP_CodeSniffer\Files\File $phpcs_file, $stack_ptr)
    {
        $tokens = $phpcs_file->getTokens();
        $variable_name = ltrim($tokens[$stack_ptr]['content'], '$');

        if ($variable_name === 'this') {
            return;
        }

        if (Framework_SniffHelper::is_snake_case($variable_name)) {
            return;
        }

        $phpcs_file->addError(
            'Variable "$%s" must use snake_case naming.',
            $stack_ptr,
            'NotSnakeCase',
            [$variable_name]
        );
    }
}
