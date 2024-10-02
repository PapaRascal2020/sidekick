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
    public function handle(): ?int
    {
        $fileSystem = new Filesystem();

        // Install resources
        $this->components->info('Installing Views');
        $fileSystem->ensureDirectoryExists(resource_path('views'));
        $fileSystem->copyDirectory(__DIR__.'/../../stubs/default/resources/views', resource_path('views'));

        // Install Routes
        $this->components->info('Installing Routes');
        $fileSystem->ensureDirectoryExists(base_path('routes'));
        $fileSystem->copyDirectory(__DIR__.'/../../stubs/default/routes', base_path('routes'));

        // Install Tests
        if($this->confirm('Install Sidekick tests?')) {
            $this->components->info('Installing Tests');
            $packageTestDirectory = __DIR__.'/../../tests';

            $fileSystem->ensureDirectoryExists(base_path('tests/Unit'));
            $fileSystem->ensureDirectoryExists(base_path('tests/Feature'));

            $testFiles = $fileSystem->allFiles($packageTestDirectory);

            foreach ($testFiles as $testFile) {
                $path = $testFile->getRelativePath();
                $file = $testFile->getRelativePathName();
                $this->components->info('Installing '. $file . ' to ' . base_path('tests/' . $path));
                $fileSystem->copy($testFile, base_path('tests/' . $file));
            }
        }

        $fileSystem->copyDirectory(__DIR__.'/../../stubs/default/routes', base_path('routes'));

        // Make link to routes in web.php
        $this->components->info('Linking Routes');
        $webRoutesPath = base_path('routes/web.php');
        $routeLink = "\nrequire base_path('routes/web.sidekick.php');\n";
        $fileSystem->append($webRoutesPath, $routeLink);

        $this->components->success("Successfully installed Sidekick Playground");

        return 1;
    }
}
