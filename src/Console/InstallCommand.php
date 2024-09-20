<?php

namespace PapaRascalDev\Sidekick\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'sidekick:install', description: 'Install Sidekick Playground')]
class InstallCommand extends Command
{
    /**
    * The name and signature of the console command.
    *
    * @var string
    */
    protected $signature = 'sidekick:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Sidekick Playground';

    /**
     * Execute the console command.
     *
     * @return int|null
     */
    public function handle()
    {
        $fileSystem = new Filesystem();
        // Install resources
        $fileSystem->ensureDirectoryExists(resource_path('views'));
        $fileSystem->copyDirectory(__DIR__.'/../../stubs/default/resources/views', resource_path('views'));

        // Install Routes
        $fileSystem->ensureDirectoryExists(base_path('routes'));
        $fileSystem->copyDirectory(__DIR__.'/../../stubs/default/routes', base_path('routes'));

        // Make link to routes in web.php
        $webRoutesPath = base_path('routes/web.php');
        $routeLink = "\nrequire base_path('routes/web.sidekick.php');\n";
        $fileSystem->append($webRoutesPath, $routeLink);

        $this->components->success("Successfully installed Sidekick Playground");
    }
}
