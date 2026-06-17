<?php
/**
 * Central registry of canonical date, datetime, and ISO-8601 interval format strings.
 * Separates database storage formats from human-readable display formats and predefined DateInterval steps.
 * Keeps date formatting consistent across models, queries, and UI layers.
 *
 * @package    Framework
 * @subpackage Constants
 * @since      1.0.0
 */
namespace Framework\Constants;

defined('ABSPATH') || exit;

class DateTimeFormats
{
    const DB_DATE = 'Y-m-d';

    const DB_MONTH_OF_YEAR = 'Y-m';

    const DB_DATETIME = 'Y-m-d H:i:s';

    const HUMAN_READABLE_DATE = 'M j, Y';

    const HUMAN_READABLE_DAY_OF_MONTH = 'M j';

    const HUMAN_READABLE_MONTH_OF_YEAR = 'M Y';

    const PER_DAY_STEP = 'P1D';

    const THREE_DAY_STEP = 'P3D';

    const PER_MONTH_STEP = 'P1M';

    const THREE_MONTH_STEP = 'P3M';

    const PER_WEEK_STEP = 'P7D';

    const THREE_WEEK_STEP = 'P21D';

    const FIRST_DAY_OF_CURRENT_MONTH = 'Y-m-01';

    const LAST_DAY_OF_CURRENT_MONTH = 'Y-m-t';
}
