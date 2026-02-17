<?php

namespace PapaRascalDev\Sidekick\Console;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $signature = 'sidekick:install';

    protected $description = 'Install Sidekick configuration and assets';

    public function handle(): int
    {
        $this->components->info('Publishing Sidekick configuration...');
        $this->call('vendor:publish', ['--tag' => 'sidekick-config']);

        $this->components->info('Publishing Sidekick migrations...');
        $this->call('vendor:publish', ['--tag' => 'sidekick-migrations']);

        if ($this->confirm('Would you like to run migrations now?', true)) {
            $this->call('migrate');
        }

        if ($this->confirm('Would you like to publish the chat widget views?', false)) {
            $this->call('vendor:publish', ['--tag' => 'sidekick-views']);
        }

        $this->components->info('Add the following environment variables to your .env file:');
        $this->line('  SIDEKICK_OPENAI_TOKEN=your-openai-key');
        $this->line('  SIDEKICK_CLAUDE_TOKEN=your-anthropic-key');
        $this->line('  SIDEKICK_MISTRAL_TOKEN=your-mistral-key');
        $this->line('  SIDEKICK_COHERE_TOKEN=your-cohere-key');

        $this->newLine();
        $this->components->success('Sidekick installed successfully!');

        return self::SUCCESS;
    }
}
