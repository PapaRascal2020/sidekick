<?php

namespace PapaRascalDev\Sidekick\Console;

use Illuminate\Console\Command;
use PapaRascalDev\Sidekick\SidekickManager;

class IngestCommand extends Command
{
    protected $signature = 'sidekick:ingest
        {name : The name of the knowledge base}
        {--file=* : File path(s) to ingest}
        {--text= : Inline text to ingest}
        {--dir= : Directory of files to ingest (.txt, .md, .html, .csv)}
        {--purge : Purge existing chunks before ingesting}
        {--source= : Source label for the ingested content}';

    protected $description = 'Ingest content into a Sidekick knowledge base';

    public function handle(SidekickManager $manager): int
    {
        $name = $this->argument('name');
        $builder = $manager->knowledge($name);

        if ($this->option('purge')) {
            $builder->purge();
            $this->components->info("Purged existing chunks from knowledge base [{$name}].");
        }

        $ingested = false;

        // Ingest inline text
        if ($text = $this->option('text')) {
            $source = $this->option('source') ?? 'inline';
            $builder->ingest($text, $source);
            $this->components->info('Ingested inline text.');
            $ingested = true;
        }

        // Ingest individual files
        foreach ($this->option('file') as $filePath) {
            if (! file_exists($filePath)) {
                $this->components->error("File not found: {$filePath}");

                continue;
            }

            $builder->ingestFile($filePath);
            $this->components->info("Ingested file: {$filePath}");
            $ingested = true;
        }

        // Ingest directory
        if ($dir = $this->option('dir')) {
            if (! is_dir($dir)) {
                $this->components->error("Directory not found: {$dir}");

                return self::FAILURE;
            }

            $extensions = ['txt', 'md', 'html', 'csv'];
            $files = [];

            foreach ($extensions as $ext) {
                $files = array_merge($files, glob(rtrim($dir, '/')."/*.{$ext}") ?: []);
            }

            if (empty($files)) {
                $this->components->warn("No .txt, .md, .html, or .csv files found in: {$dir}");

                return self::SUCCESS;
            }

            foreach ($files as $filePath) {
                $builder->ingestFile($filePath);
                $this->components->info("Ingested file: {$filePath}");
            }

            $ingested = true;
        }

        if (! $ingested) {
            $this->components->warn('No content provided. Use --file, --text, or --dir to specify content.');

            return self::FAILURE;
        }

        $this->components->success(
            "Knowledge base [{$name}] now has {$builder->chunkCount()} chunk(s)."
        );

        return self::SUCCESS;
    }
}
