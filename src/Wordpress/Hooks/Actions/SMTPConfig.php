<?php

namespace Framework\Wordpress\Hooks\Actions;

use Framework\Wordpress\Constants\HookNames;
use Framework\Wordpress\Constants\HookTypes;
use Kirki\App\Constants\OptionKeys;
use Framework\Wordpress\BaseHook;
use Framework\Supports\Facades\Settings;
use PHPMailer\PHPMailer\PHPMailer;

class SMTPConfig extends BaseHook
{
    public function get_name(): string
    {
        return HookNames::WP_PHP_MAILER_INIT;
    }

    public function get_type(): string
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        if (empty($args)) {
            error_log(
                sprintf(
                    '%s hook configured in a wrong way.',
                    $this->get_name()
                )
            );
            return;
        }

        $mailer = $args[0];

        if (!$mailer instanceof PHPMailer) {
            error_log('Mailer is not instance of PHPMailer.');
            return;
        }

        $config = Settings::get(OptionKeys::EMAIL_SETTINGS);
        $is_smtp = $config->get('mailer') === 'smtp';

        if ($is_smtp && empty($config->get('mail'))) {
            error_log('Mail settings are not configured');
            return;
        }

        if ($is_smtp) {
            $mailer->isSMTP();
            $mailer->Host = $config->get('mail.host');
            $mailer->SMTPAuth = $config->get('mail.enable_authentication');
            $mailer->Port = $config->get('mail.port');
            $mailer->Username = $config->get('mail.username');
            $mailer->Password = $config->get('mail.password');
            $mailer->SMTPSecure = $config->get('mail.encryption');
            $mailer->FromName = $config->get('mail.from_name');
            $mailer->From = $config->get('mail.from_email');
        }
    }
}
