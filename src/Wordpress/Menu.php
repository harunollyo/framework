<?php

namespace Framework\Wordpress;

use Framework\Wordpress\Constants\MenuTypes;
use Framework\Supports\Arr;
use Exception;

use function Framework\collection;

class Menu
{
    /**
     * The page menu type i.e. main menu or submenu
     * @var MenuTypes
     */
    protected $menu_type = MenuTypes::MAIN_MENU;

    /**
     * The page title
     * @var string
     */
    protected $page_title = null;

    /**
     * The menu title
     * @var string
     */
    protected $menu_title = null;

    /**
     * The capabilities
     * @var string
     */
    protected $capabilities = null;

    /**
     * The menu slug
     * @var string
     */
    protected $menu_slug = null;

    /**
     * The callback
     * @var string
     */
    protected $callback = '__return_false';

    /**
     * The icon url
     * @var string
     */
    protected $icon_url = null;

    /**
     * The position
     * @var int
     */
    protected $position = null;

    /**
     * The parent slug
     * @var string
     */
    protected $parent_slug = null;


    public function __construct()
    {
        if (!$this->check_required_properties()) {
            throw new Exception('Missing required properties for making a menu item');
        }
    }

    public function is_displayable()
    {
        return true;
    }

    protected function check_required_properties()
    {
        $properties = [
            $this->page_title,
            $this->menu_title,
            $this->capabilities,
            $this->menu_slug,
        ];

        if (MenuTypes::SUB_MENU === $this->menu_type) {
            $properties[] = $this->parent_slug;
        }

        return collection($properties)->every(function ($property) {
            return !empty($property);
        });
    }

    /**
     * Render the menu
     * @return void
     */
    public function render()
    {
        if ($this->menu_type === MenuTypes::MAIN_MENU) {
            add_menu_page(
                $this->page_title,
                $this->menu_title,
                $this->capabilities,
                $this->menu_slug,
                $this->callback,
                $this->icon_url,
                $this->position
            );
        } else {
            add_submenu_page(
                $this->parent_slug,
                $this->page_title,
                $this->menu_title,
                $this->capabilities,
                $this->menu_slug,
                $this->callback,
                $this->position
            );
        }
    }
}
