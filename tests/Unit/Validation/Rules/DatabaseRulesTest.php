<?php

namespace Framework\Tests\Unit\Validation\Rules;

use Framework\Container;
use Framework\Database\Connection\Connection;
use Framework\Database\Connection\DatabaseManager;
use Framework\Tests\Support\Database\ModelTestWpdb;
use Framework\Tests\Unit\TestCase;
use Framework\Validation\Validator;

class DatabaseRulesTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['framework_test_users']);

        parent::tearDown();
    }

    protected function bootstrap_database(array $seed = []): ModelTestWpdb
    {
        global $wpdb;

        $wpdb = new ModelTestWpdb();

        foreach ($seed as $table => $rows) {
            $wpdb->seed($table, $rows);
        }

        $container = new Container();
        $container->instance('app', $container);
        $container->instance('db', new DatabaseManager(new Connection()));

        $this->set_container_instance($container);

        return $wpdb;
    }

    public function test_exists_passes_when_record_is_found(): void
    {
        $this->bootstrap_database(['products' => [['sku' => 'PRD-100']]]);

        $validator = Validator::make(['sku' => 'PRD-100'], ['sku' => 'exists:products']);

        $this->assertTrue($validator->passes());
    }

    public function test_exists_fails_when_record_is_missing(): void
    {
        $this->bootstrap_database(['products' => [['sku' => 'PRD-100']]]);

        $validator = Validator::make(['sku' => 'PRD-999'], ['sku' => 'exists:products']);

        $this->assertTrue($validator->fails());
        $this->assertSame(['The selected sku does not exist.'], $validator->errors()['sku']);
    }

    public function test_exists_uses_explicit_column_argument(): void
    {
        $this->bootstrap_database(['products' => [['code' => 'ABC']]]);

        $validator = Validator::make(['sku' => 'ABC'], ['sku' => 'exists:products,code']);

        $this->assertTrue($validator->passes());
    }

    public function test_exists_queries_the_prefixed_table(): void
    {
        $wpdb = $this->bootstrap_database(['products' => [['sku' => 'PRD-100']]]);

        Validator::make(['sku' => 'PRD-100'], ['sku' => 'exists:products'])->passes();

        $this->assertStringContainsString('FROM `wp_products`', end($wpdb->queries));
    }

    public function test_unique_passes_when_no_record_matches(): void
    {
        $this->bootstrap_database(['users' => [['email' => 'taken@example.com']]]);

        $validator = Validator::make(['email' => 'free@example.com'], ['email' => 'unique:users']);

        $this->assertTrue($validator->passes());
    }

    public function test_unique_fails_when_record_already_exists(): void
    {
        $this->bootstrap_database(['users' => [['email' => 'taken@example.com']]]);

        $validator = Validator::make(['email' => 'taken@example.com'], ['email' => 'unique:users']);

        $this->assertTrue($validator->fails());
        $this->assertSame(['The email has already been taken.'], $validator->errors()['email']);
    }

    public function test_unique_appends_ignore_id_clause(): void
    {
        $wpdb = $this->bootstrap_database(['users' => []]);

        Validator::make(
            ['email' => 'jane@example.com'],
            ['email' => 'unique:users,email,5']
        )->passes();

        $this->assertMatchesRegularExpression('/AND `id` != \'?5\'?/', end($wpdb->queries));
    }

    public function test_user_exists_passes_for_known_user_id(): void
    {
        $GLOBALS['framework_test_users'] = [['id' => 7, 'email' => 'jane@example.com']];

        $validator = Validator::make(['user_id' => 7], ['user_id' => 'user_exists']);

        $this->assertTrue($validator->passes());
    }

    public function test_user_exists_fails_for_unknown_user(): void
    {
        $GLOBALS['framework_test_users'] = [['id' => 7]];

        $validator = Validator::make(['user_id' => 99], ['user_id' => 'user_exists']);

        $this->assertTrue($validator->fails());
        $this->assertSame(['The selected user_id is not a valid user.'], $validator->errors()['user_id']);
    }

    public function test_user_exists_supports_email_lookup(): void
    {
        $GLOBALS['framework_test_users'] = [['id' => 7, 'email' => 'jane@example.com']];

        $validator = Validator::make(
            ['email' => 'jane@example.com'],
            ['email' => 'user_exists:email']
        );

        $this->assertTrue($validator->passes());
    }

    public function test_user_exists_falls_back_to_id_for_unsupported_field(): void
    {
        $GLOBALS['framework_test_users'] = [['id' => 7]];

        $validator = Validator::make(['user' => 7], ['user' => 'user_exists:unsupported']);

        $this->assertTrue($validator->passes());
    }
}
