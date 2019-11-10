<?php

namespace App\Console\Commands\Developer;

use Illuminate\Console\Command;
use App\EnvWriter;

class LaradockSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laradock:configure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs and configures laradock';

    private const DEFAULT_SETTINGS = [
        'DATA_PATH_HOST' => '~/.laradock/data',
        'COMPOSE_PROJECT_NAME' => 'laradock',
        'PHP_VERSION' => '7.3',
        'WORKSPACE_INSTALL_XDEBUG' => 'true',
        'PHP_FPM_INSTALL_XDEBUG' => 'true'
    ];

    private const XDEBUG_SETTINGS = [
        'xdebug.remote_host' => 'dockerhost',
        'xdebug.remote_connect_back' => '0',
        'xdebug.remote_autostart' => '1',
        'xdebug.remote_enable' => '1',
        'xdebug.cli_color' => '1'
    ];

    private const XDEBUG_INI_PATHS = [
        'php-fpm',
        'workspace'
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line("Configuring Laradock...");

        $this->copyEnvFile();

        $this->writeEnvVars();

        $this->info('Laradock configured.');
        return;
    }

    private function copyEnvFile() {
        $this->info("Creating laradock .env file");
        $command = 'cp laradock/env-example laradock/.env';
        $output = [];
        $status = null;
        exec($command, $output, $status);
    }

    private function writeEnvVars() {
        $vars = self::DEFAULT_SETTINGS;
        $appName = config('app.name');
        $vars['DATA_PATH_HOST'] = "~/.laradock/{$appName}/data";
        $vars['COMPOSE_PROJECT_NAME'] = $appName;

        $file = $this->environmentFilePath();

        $env = new EnvWriter($file);
        foreach($vars as $key => $value) {
            $env->write(strtoupper($key), $value);
        }
    }

    private function basePath() {
        return $this->laravel->basePath() . DIRECTORY_SEPARATOR . 'laradock';
    }

    /**
     * Returns the environment file path for laradock
     * @return string
     */
    private function environmentFilePath() {
        return $this->basePath() . DIRECTORY_SEPARATOR . '.env';
    }

    /**
     * Returns an array of XDEBUG ini files and their full path.
     * @return array
     */
    private function xdebugIniFilePaths() {
        $file = 'xdebug.ini';
        $files = [];
        foreach(self::XDEBUG_INI_PATHS as $path_part) {
            $files[] = $this->basePath() . DIRECTORY_SEPARATOR . $path_part . DIRECTORY_SEPARATOR . $file;
        }
        return $files;
    }

    /**
     * Enables XDEBUG
     */
    private function enableXDEBUG() {
        $this->info("Enabling xdebug");
        foreach($this->xdebugIniFilePaths() as $ini) {
            $this->writeToXdebugIni($ini);
        }
    }

    /**
     * Loop through each key/value pair in XDEBUG_SETTINGS
     * and write those to given ini file.
     * @param $file
     */
    private function writeToXdebugIni($file) {
        // $this->line("Writing settings to {$file}");
        $ini = new EnvWriter($file, 'ini');

        $ini->uncomment('xdebug.remote_host');

        foreach(self::XDEBUG_SETTINGS as $key => $value) {
            $ini->write($key, $value);
        }
    }
}
