<?php

namespace Authxolote\Sdk\Console\Commands;

use Authxolote\Sdk\Authxolote;
use Illuminate\Console\Command;

class AttachRolesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authxolote:actions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Associate roles defined in the configuration file using Authxolote::attachRolesAction';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $roles = config('actions', []);

        if (empty($roles)) {
            $this->error('No roles found in the configuration file.');

            return 1;
        }
        $result = Authxolote::attachRolesAction($roles);

        if (! $result) {
            $this->error('An error occurred while associating the roles.');

            return 1;
        }

        $this->info('Roles associated successfully.');

        return 0;

    }
}
