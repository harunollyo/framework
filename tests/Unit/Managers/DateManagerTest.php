<?php

namespace Framework\Tests\Unit\Managers;

use DateTime;
use DateTimeZone;
use Framework\Managers\DateManager;
use Framework\Tests\Unit\TestCase;

class DateManagerTest extends TestCase
{
    /**
     * Date manager under test.
     *
     * @var \Framework\Managers\DateManager
     */
    protected $date_manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->date_manager = new DateManager();
    }

    public function test_now_returns_current_datetime(): void
    {
        $before = time();
        $now = $this->date_manager->now();
        $after = time();

        $this->assertInstanceOf(DateTime::class, $now);
        $this->assertGreaterThanOrEqual($before, $now->getTimestamp());
        $this->assertLessThanOrEqual($after, $now->getTimestamp());
    }

    public function test_parse_accepts_datetime_string(): void
    {
        $date = $this->date_manager->parse('2024-05-01 14:30:00', new DateTimeZone('UTC'));

        $this->assertSame('2024-05-01 14:30:00', $date->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', $date->getTimezone()->getName());
    }

    public function test_create_from_format_returns_null_for_invalid_input(): void
    {
        $date = $this->date_manager->create_from_format('Y-m-d', 'not-a-date');

        $this->assertNull($date);
    }

    public function test_create_from_format_parses_valid_input(): void
    {
        $date = $this->date_manager->create_from_format('Y-m-d', '2024-05-01', new DateTimeZone('UTC'));

        $this->assertSame('2024-05-01', $date->format('Y-m-d'));
    }

    public function test_is_valid_date_accepts_datetime_interface(): void
    {
        $this->assertTrue($this->date_manager->is_valid_date(new DateTime()));
    }

    public function test_is_valid_date_rejects_invalid_string(): void
    {
        $this->assertFalse($this->date_manager->is_valid_date('not-a-date'));
    }

    public function test_start_of_day_resets_time(): void
    {
        $date = new DateTime('2024-05-01 14:30:45', new DateTimeZone('UTC'));
        $start_of_day = $this->date_manager->start_of_day($date);

        $this->assertSame('2024-05-01 00:00:00', $start_of_day->format('Y-m-d H:i:s'));
        $this->assertSame('2024-05-01 14:30:45', $date->format('Y-m-d H:i:s'));
    }

    public function test_is_after_compares_dates(): void
    {
        $earlier = new DateTime('2024-01-01');
        $later = new DateTime('2024-06-01');

        $this->assertTrue($this->date_manager->is_after($later, $earlier));
        $this->assertFalse($this->date_manager->is_after($earlier, $later));
    }

    public function test_to_sql_datetime_string_formats_date(): void
    {
        $date = new DateTime('2024-05-01 14:30:00', new DateTimeZone('UTC'));

        $this->assertSame(
            '2024-05-01 14:30:00',
            $this->date_manager->to_sql_datetime_string($date)
        );
    }

    public function test_create_from_timestamp_builds_datetime(): void
    {
        $timestamp = (new DateTime('2024-05-01 14:30:00', new DateTimeZone('UTC')))->getTimestamp();
        $date = $this->date_manager->create_from_timestamp($timestamp, new DateTimeZone('UTC'));

        $this->assertSame('2024-05-01 14:30:00', $date->format('Y-m-d H:i:s'));
    }
}
