<?php

namespace Authxolote\Sdk\Console\Commands;

use Authxolote\Sdk\Authxolote;
use Illuminate\Console\Command;

class SetupMenuCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authxolote:menu';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup menu defined in the configuration file using Authxolote::setupMenu';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $menu = config('authxolote.menu', []);

        if (empty($menu)) {
            $this->error('No menu found in the configuration file.');

            return 1;
        }
        $result = Authxolote::setupMenu($menu);

        if (! $result) {
            $this->error('An error occurred while setting up the menu.');

            return 1;
        }

        $this->info('Menu setup successfully.');

        return 0;

    }
}
