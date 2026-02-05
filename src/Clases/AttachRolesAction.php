<?php

namespace Authxolote\Sdk\Clases;

class AttachRolesAction extends AuthxoloteBase
{
    public function __construct(protected array $roles)
    {
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
