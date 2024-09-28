<?php

namespace PapaRascalDev\Sidekick\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'sidekick:remove', description: 'Uninstalls Sidekick Playground')]
class RemoveCommand extends Command
{
    /**
    * The name and signature of the console command.
    *
    * @var string
    */
    protected $signature = 'sidekick:remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Uninstalls Sidekick Playground';

    /**
     * Execute the console command.
     *
     * @return int|null
     */
    public function handle()
    {
        $fileSystem = new Filesystem();

        //Remove specific resources
        $viewsPath = resource_path('views');
        $stubViewsPath = __DIR__.'/../../stubs/default/resources/views';

        if ($fileSystem->exists($viewsPath)) {
            $this->removeSpecificFiles($fileSystem, $stubViewsPath, $viewsPath);
        }

        // Remove existing routes
        $this->components->info('Removing web.sidekick.php');
        $routesPath = base_path('routes/web.sidekick.php');
        if ($fileSystem->exists($routesPath)) {
            $fileSystem->delete($routesPath);
        }

        // Remove Tests
        $this->components->info('Checking for tests and removing');
        $packageTestDirectory = __DIR__.'/../../tests';

        $fileSystem->ensureDirectoryExists(base_path('tests/Unit'));
        $fileSystem->ensureDirectoryExists(base_path('tests/Feature'));

        $testFiles = $fileSystem->allFiles($packageTestDirectory);

        foreach ($testFiles as $testFile) {
            $path = $testFile->getRelativePath();
            $file = $testFile->getRelativePathName();
            $this->components->info('Removing '. $file . ' from ' . base_path('tests/' . $path));
            $fileSystem->delete(base_path('tests/' . $file));
        }

        // Unlink routes in web.php
        $this->components->info('Unlinking Routes');
        $webRoutesPath = base_path('routes/web.php');
        $routeLink = "\nrequire base_path('routes/web.sidekick.php');\n";
        $fileSystem->replaceInFile($routeLink, "", $webRoutesPath);

        $this->components->warn('As we can\'t be sure to remove any user files, the directories remain in resources, please delete if not needed.' );

        $this->components->success("Successfully removed Sidekick Playground");
    }

    /**
     * Remove specific files and directories that were added by the application.
     *
     * @param Filesystem $fileSystem
     * @param string $stubViewsPath
     * @param string $viewsPath
     * @return void
     */
    protected function removeSpecificFiles(Filesystem $fileSystem, string $stubViewsPath, string $viewsPath)
    {
        $stubFiles = $fileSystem->allFiles($stubViewsPath);

        foreach ($stubFiles as $stubFile) {
            $relativePath = $stubFile->getRelativePathname();
            $targetFile = $viewsPath . DIRECTORY_SEPARATOR . $relativePath;
            if ($fileSystem->exists($targetFile)) {
                $this->components->info('Removing ' . $relativePath);
                $fileSystem->delete($targetFile);
            }
        }
    }
}
