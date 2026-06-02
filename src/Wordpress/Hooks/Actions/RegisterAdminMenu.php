<?php

namespace Framework\Wordpress\Hooks\Actions;

use Framework\Wordpress\Constants\HookTypes;
use Framework\Wordpress\BaseHook;
use Framework\Wordpress\Constants\HookNames;
use Framework\Wordpress\Menu;
use Exception;

use function Framework\config;

class RegisterAdminMenu extends BaseHook
{
    public function get_name()
    {
        return HookNames::ADMIN_MENU;
    }

    public function get_type()
    {
        return HookTypes::ACTION;
    }

    public function handle(...$args)
    {
        $menus = config('menu', []);

        if (empty($menus)) {
            return;
        }

        foreach ($menus as $menu) {
            if (!class_exists($menu) || !is_subclass_of($menu, Menu::class)) {
                throw new Exception(sprintf('Menu class %s does not exist.', $menu));
            }

            $menu_instance = new $menu();

            if ($menu_instance->is_displayable()) {
                $menu_instance->render();
            };
        }
    }
}
