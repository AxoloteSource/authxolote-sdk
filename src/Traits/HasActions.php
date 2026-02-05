<?php

namespace Authxolote\Sdk\Traits;

use Authxolote\Sdk\Authxolote;

trait HasActions
{
    public function belongsToAction(string $action): bool
    {
        return Authxolote::action($action)->isAllow();
    }
}
