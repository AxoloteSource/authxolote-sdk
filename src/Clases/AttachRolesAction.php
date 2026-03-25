<?php

namespace Authxolote\Sdk\Clases;

class AttachRolesAction extends AuthxoloteBase
{
    protected array $roles;

    public function __construct(array $roles)
    {
        $this->roles = $roles;
        parent::__construct('/api/v1/roles/attach/actions');
    }

    public function run(): bool
    {
        $response = $this->post($this->roles);
        logger($response->body());
        if ($response->created()) {
            return true;
        }

        return false;
    }
}
