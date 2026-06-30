<?php

namespace Hatchyu\PipelineEngine\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallPipelineCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pipeline:install
                            {--force : Overwrite existing workflow file}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Install and configure a customized GitHub Actions CI workflow';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->components->info('Hatchyu Pipeline Engine: Interactive CI Scaffolder');

        $workflowPath = base_path('.github/workflows/ci.yml');

        if (File::exists($workflowPath) && ! $this->option('force')) {
            if (! $this->components->confirm('A GitHub Actions CI workflow already exists. Do you want to overwrite it?', false)) {
                $this->components->warn('Installation aborted.');
                return self::SUCCESS;
            }
        }

        // 1. Ask for PHP Version
        $defaultPhp = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
        $phpVersion = $this->components->ask('Which PHP version should run in CI?', $defaultPhp);

        // 2. Ask for Node.js setup
        $hasPackageJson = File::exists(base_path('package.json'));
        $setupNode = $this->components->confirm('Does this project require building Node/Frontend assets?', $hasPackageJson);

        // 3. Ask for Lint step
        $runLint = $this->components->confirm('Include code quality checks (ci-lint: Pint, Larastan)?', true);

        // 4. Ask for Security step
        $runSecurity = $this->components->confirm('Include dependency security checks (ci-security: Composer Audit)?', true);

        // 5. Ask for Test step
        $runTests = $this->components->confirm('Include test execution (ci-test)?', true);

        // Load stub
        $stubPath = __DIR__ . '/../../stubs/ci.yml.stub';
        if (! File::exists($stubPath)) {
            $this->components->error('Workflow stub file not found at: ' . $stubPath);
            return self::FAILURE;
        }

        $stub = File::get($stubPath);

        // Replace placeholders
        $stub = str_replace('{{PHP_VERSION}}', $phpVersion, $stub);

        // Node.js steps stub
        $nodeSteps = '';
        if ($setupNode) {
            $nodeSteps = "\n    - name: Cache NPM Dependencies\n" .
                         "      uses: actions/cache@v4\n" .
                         "      with:\n" .
                         "        path: ~/.npm\n" .
                         "        key: dependencies-laravel-npm-\${{ hashFiles('**/package-lock.json') }}\n" .
                         "        restore-keys: dependencies-laravel-npm-\n" .
                         "\n" .
                         "    - name: Install & Build Frontend Assets\n" .
                         "      run: |\n" .
                         "        if [ -f package.json ]; then\n" .
                         "          npm ci\n" .
                         "          npm run build --if-present\n" .
                         "        fi";
        }
        $stub = str_replace('{{NODE_STEPS}}', $nodeSteps, $stub);

        // Lint step
        $lintStep = $runLint ? "\n    - name: Run Quality Checks\n      run: ./vendor/bin/ci-lint" : '';
        $stub = str_replace('{{LINT_STEP}}', $lintStep, $stub);

        // Security step
        $securityStep = $runSecurity ? "\n    - name: Run Security Audits\n      run: ./vendor/bin/ci-security" : '';
        $stub = str_replace('{{SECURITY_STEP}}', $securityStep, $stub);

        // Test step
        $testStep = $runTests ? "\n    - name: Run Test Suite\n      run: ./vendor/bin/ci-test" : '';
        $stub = str_replace('{{TEST_STEP}}', $testStep, $stub);

        // Ensure directories exist
        File::ensureDirectoryExists(dirname($workflowPath));

        // Write the custom workflow
        File::put($workflowPath, $stub);

        $this->components->info('✔ GitHub Actions workflow successfully created at: ' . $workflowPath);
        
        $this->line('');
        $this->info("🚀 Installation complete! To get started:");
        $this->info("1. Commit and push '.github/workflows/ci.yml' to your repository.");
        $this->info("2. When configuring a new tool (like Pint/Larastan), the engine will automatically detect and run them.");
        $this->info("3. To customize pipeline behaviors, simply adjust the workflow file or set custom environment variables.");
        $this->line('');

        return self::SUCCESS;
    }
}
