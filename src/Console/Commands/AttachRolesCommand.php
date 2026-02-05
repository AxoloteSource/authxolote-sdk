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
    protected $description = 'Asocia los roles definidos en el archivo de configuración mediante Authxolote::attachRolesAction';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $roles = config('actions', []);

        if (empty($roles)) {
            $this->error('No se encontraron roles en el archivo de configuración.');

            return 1;
        }
        $result = Authxolote::attachRolesAction($roles);

        if (! $result) {
            $this->error('Ocurrió un error al asociar los roles.');

            return 1;
        }

        $this->info('Roles asociados correctamente.');

        return 0;

    }
}
