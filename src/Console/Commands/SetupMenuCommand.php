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
    protected $signature = 'authxolote:menu
                            {--force : Elimina el menú existente y lo vuelve a crear desde la configuración}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup menu defined in the configuration file using Authxolote::setupMenuAction';

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

        $force = (bool) $this->option('force');

        if ($force && ! $this->confirmForce($menu)) {
            $this->info('Operación cancelada. El menú no fue modificado.');

            return 1;
        }

        $result = Authxolote::setupMenuAction($menu, $force)->run();

        if (! $result) {
            $this->error('An error occurred while setting up the menu.');

            return 1;
        }

        $this->info('Menu setup successfully.');

        return 0;

    }

    /**
     * Advierte que el menú se eliminará y se volverá a crear antes de continuar.
     */
    private function confirmForce(array $menu): bool
    {
        $name = $menu['name'] ?? 'actual';

        $this->newLine();
        $this->warn("Esto eliminará el menú \"{$name}\" y todos sus ítems, y lo volverá a crear desde la configuración.");
        $this->warn('Se perderán los roles asignados, el orden y cualquier modificación manual hecha desde el panel.');
        $this->newLine();

        return $this->confirm('¿Deseas continuar?', false);
    }
}
