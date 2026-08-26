<?php

namespace Authxolote\Sdk\Clases;

class SetupMenu extends AuthxoloteBase
{
    protected array $menu;

    public function __construct(array $menu)
    {
        $this->menu = $menu;
        parent::__construct('/api/v1/menus/setup');
    }

    public function run(): bool
    {
        $response = $this->post($this->menu);
        logger($response->body());
        if ($response->created()) {
            return true;
        }

        return false;
    }
}
