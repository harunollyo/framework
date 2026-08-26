<?php

namespace Framework\Tests\Unit\Database\Migrations;

use Framework\Constants\OptionKeys;
use Framework\Container;
use Framework\Database\Migrations\Migrator;
use Framework\Tests\Support\Migrations\FirstMigration;
use Framework\Tests\Support\Migrations\RecordingMigration;
use Framework\Tests\Support\Migrations\RecordingOptionStore;
use Framework\Tests\Support\Migrations\RecordingSchema;
use Framework\Tests\Support\Migrations\SecondMigration;
use Framework\Tests\Support\Migrations\TestMigrationRepository;
use Framework\Tests\Support\Migrations\ThirdMigration;
use Framework\Tests\Unit\TestCase;
use RuntimeException;

class MigratorTest extends TestCase
{
    /** @var RecordingOptionStore */
    protected $options;

    /** @var RecordingSchema */
    protected $schema;

    /** @var TestMigrationRepository */
    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();

        RecordingMigration::reset_order();

        $this->options = new RecordingOptionStore();
        $this->schema = new RecordingSchema();

        $container = new Container();
        $container->instance('app', $container);
        $container->instance('option', $this->options);
        $container->instance('schema', $this->schema);

        $this->set_container_instance($container);

        $this->repository = new TestMigrationRepository();
    }

    protected function make_migrator(array $migrations): Migrator
    {
        $this->repository->set_registered($migrations);

        return new Migrator($this->repository);
    }

    protected function history(): array
    {
        return $this->options->get(OptionKeys::MIGRATIONS, []);
    }

    public function test_first_run_records_every_migration_in_one_batch(): void
    {
        $first = new FirstMigration();
        $second = new SecondMigration();

        $this->make_migrator([$first, $second])->run();

        $this->assertSame(1, $first->up_calls);
        $this->assertSame(1, $second->up_calls);

        $history = $this->history();

        $this->assertArrayHasKey(FirstMigration::class, $history);
        $this->assertArrayHasKey(SecondMigration::class, $history);
        $this->assertSame(1, $history[FirstMigration::class]['batch']);
        $this->assertSame(1, $history[SecondMigration::class]['batch']);
        $this->assertNotNull($history[FirstMigration::class]['ran_at']);
    }

    public function test_second_run_executes_nothing(): void
    {
        $first = new FirstMigration();
        $second = new SecondMigration();

        $migrator = $this->make_migrator([$first, $second]);
        $migrator->run();
        $migrator->run();

        $this->assertSame(1, $first->up_calls);
        $this->assertSame(1, $second->up_calls);
    }

    public function test_migration_added_later_records_in_a_higher_batch(): void
    {
        $first = new FirstMigration();

        $this->make_migrator([$first])->run();

        $second = new SecondMigration();

        $this->make_migrator([$first, $second])->run();

        $history = $this->history();

        $this->assertSame(1, $history[FirstMigration::class]['batch']);
        $this->assertSame(2, $history[SecondMigration::class]['batch']);
        $this->assertSame(1, $first->up_calls);
    }

    public function test_failure_keeps_earlier_migrations_recorded(): void
    {
        $first = new FirstMigration();
        $second = new SecondMigration();
        $third = new ThirdMigration();
        $third->throw_on_up = true;

        $migrator = $this->make_migrator([$first, $second, $third]);

        try {
            $migrator->run();
            $this->fail('Expected the third migration to throw.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('up failed', $exception->getMessage());
        }

        $history = $this->history();

        $this->assertArrayHasKey(FirstMigration::class, $history);
        $this->assertArrayHasKey(SecondMigration::class, $history);
        $this->assertArrayNotHasKey(ThirdMigration::class, $history);
    }

    public function test_rerun_after_failure_resumes_at_the_failed_migration(): void
    {
        $first = new FirstMigration();
        $second = new SecondMigration();
        $third = new ThirdMigration();
        $third->throw_on_up = true;

        $migrator = $this->make_migrator([$first, $second, $third]);

        try {
            $migrator->run();
        } catch (RuntimeException $exception) {
            unset($exception);
        }

        $third->throw_on_up = false;

        $migrator->run();

        $this->assertSame(1, $first->up_calls);
        $this->assertSame(1, $second->up_calls);
        $this->assertSame(1, $third->up_calls);
        $this->assertArrayHasKey(ThirdMigration::class, $this->history());
    }

    public function test_rollback_undoes_only_the_highest_batch_in_reverse_order(): void
    {
        $first = new FirstMigration();
        $second = new SecondMigration();

        $this->make_migrator([$first])->run();

        $migrator = $this->make_migrator([$first, $second]);
        $migrator->run();

        RecordingMigration::reset_order();

        $rolled_back = $migrator->rollback();

        $this->assertSame([SecondMigration::class], $rolled_back);
        $this->assertSame(1, $second->down_calls);
        $this->assertSame(0, $first->down_calls);
        $this->assertArrayHasKey(FirstMigration::class, $this->history());
        $this->assertArrayNotHasKey(SecondMigration::class, $this->history());
    }

    public function test_rollback_undoes_a_batch_in_reverse_registration_order(): void
    {
        $first = new FirstMigration();
        $second = new SecondMigration();
        $third = new ThirdMigration();

        $migrator = $this->make_migrator([$first, $second, $third]);
        $migrator->run();

        RecordingMigration::reset_order();

        $migrator->rollback();

        $this->assertSame(
            ['down:ThirdMigration', 'down:SecondMigration', 'down:FirstMigration'],
            RecordingMigration::$order
        );
    }

    public function test_rollback_with_step_spans_multiple_batches(): void
    {
        $first = new FirstMigration();
        $second = new SecondMigration();

        $this->make_migrator([$first])->run();

        $migrator = $this->make_migrator([$first, $second]);
        $migrator->run();

        $rolled_back = $migrator->rollback(2);

        $this->assertSame([SecondMigration::class, FirstMigration::class], $rolled_back);
        $this->assertSame([], $this->history());
    }

    public function test_rollback_with_empty_history_is_a_noop(): void
    {
        $first = new FirstMigration();

        $rolled_back = $this->make_migrator([$first])->rollback();

        $this->assertSame([], $rolled_back);
        $this->assertSame(0, $first->down_calls);
    }

    public function test_rollback_toggles_foreign_key_checks(): void
    {
        $first = new FirstMigration();

        $migrator = $this->make_migrator([$first]);
        $migrator->run();
        $migrator->rollback();

        $this->assertSame(['disable', 'enable'], $this->schema->calls);
    }

    public function test_rollback_restores_foreign_key_checks_when_a_step_fails(): void
    {
        $first = new FirstMigration();
        $first->throw_on_down = true;

        $migrator = $this->make_migrator([$first]);
        $migrator->run();

        try {
            $migrator->rollback();
            $this->fail('Expected the rollback to throw.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('down failed', $exception->getMessage());
        }

        $this->assertSame(['disable', 'enable'], $this->schema->calls);
    }

    public function test_fresh_clears_the_migration_history(): void
    {
        $first = new FirstMigration();
        $second = new SecondMigration();

        $migrator = $this->make_migrator([$first, $second]);
        $migrator->run();

        $this->assertNotSame([], $this->history());

        $migrator->fresh();

        $this->assertSame([], $this->history());
        $this->assertSame(1, $first->down_calls);
        $this->assertSame(1, $second->down_calls);
    }

    public function test_fresh_skips_migrations_that_were_never_applied(): void
    {
        $first = new FirstMigration();
        $second = new SecondMigration();

        $this->make_migrator([$first])->run();

        $migrator = $this->make_migrator([$first, $second]);
        $migrator->fresh();

        $this->assertSame(1, $first->down_calls);
        $this->assertSame(0, $second->down_calls);
        $this->assertSame([], $this->history());
    }

    public function test_fresh_undoes_migrations_in_reverse_registration_order(): void
    {
        $migrator = $this->make_migrator([
            new FirstMigration(),
            new SecondMigration(),
            new ThirdMigration(),
        ]);
        $migrator->run();

        RecordingMigration::reset_order();

        $migrator->fresh();

        $this->assertSame(
            ['down:ThirdMigration', 'down:SecondMigration', 'down:FirstMigration'],
            RecordingMigration::$order
        );
    }

    public function test_legacy_history_reads_as_batch_one(): void
    {
        $this->options->set(
            OptionKeys::MIGRATIONS,
            [
                FirstMigration::class  => true,
                SecondMigration::class => true,
            ]
        );

        $previous = $this->repository->get_previous_migrations();

        $this->assertSame(1, $previous[FirstMigration::class]['batch']);
        $this->assertNull($previous[FirstMigration::class]['ran_at']);
        $this->assertSame(1, $this->repository->get_last_batch_number());
        $this->assertSame(2, $this->repository->get_next_batch_number());
    }

    public function test_legacy_history_is_not_re_executed(): void
    {
        $first = new FirstMigration();

        $this->options->set(OptionKeys::MIGRATIONS, [FirstMigration::class => true]);

        $this->make_migrator([$first])->run();

        $this->assertSame(0, $first->up_calls);
    }

    public function test_status_reports_applied_and_pending_migrations(): void
    {
        $first = new FirstMigration();
        $second = new SecondMigration();

        $this->make_migrator([$first])->run();

        $status = $this->make_migrator([$first, $second])->status();

        $this->assertSame(
            [
                ['migration' => 'FirstMigration', 'ran' => true, 'batch' => 1],
                ['migration' => 'SecondMigration', 'ran' => false, 'batch' => null],
            ],
            $status
        );
    }

    public function test_status_reports_everything_pending_before_any_run(): void
    {
        $status = $this->make_migrator([new FirstMigration(), new SecondMigration()])->status();

        $this->assertSame(
            [
                ['migration' => 'FirstMigration', 'ran' => false, 'batch' => null],
                ['migration' => 'SecondMigration', 'ran' => false, 'batch' => null],
            ],
            $status
        );
    }
}
