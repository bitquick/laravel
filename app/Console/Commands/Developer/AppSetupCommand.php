<?php

namespace App\Console\Commands\Developer;

use Exception;
use Illuminate\Console\Command;
use Dotenv\Dotenv;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;

class AppSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup {--app-name=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup the application\'s environment variables.';

    private const ENVIRONMENT_SETTINGS = [
        'default' => [
            'APP_NAME' => 'laravel',
            'DB_HOST' => '127.0.0.1',
            'DB_DATABASE' => 'laravel',
            'QUEUE_CONNECTION' => 'sync',
            'REDIS_HOST' => '127.0.0.1'
        ],
        'laradock' => [
            'APP_NAME' => 'laravel',
            'DB_HOST' => 'mysql',
            'DB_DATABASE' => 'default',
            'QUEUE_CONNECTION' => 'redis',
            'REDIS_HOST' => 'redis'
        ]
    ];

    private const ENVIRONMENT_SETTINGS_DEFAULT = 'laradock';

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
        try {
            $this->appSetup();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        } finally {
            return;
        }
    }

    /**
     * Retrieve the default environment variables for a given environment or the default environment if left blank.
     * @param null $environment
     * @return array|mixed
     */
    private function getDefaultEnvVar($environment = null) {
        if(!empty($environment)) {
            return self::ENVIRONMENT_SETTINGS[$environment] ?? [];
        }
        return self::ENVIRONMENT_SETTINGS[self::ENVIRONMENT_SETTINGS_DEFAULT] ?? [];
    }

    private function appSetup() {
        $this->configLaravelEnvironment();
    }

    private function configLaravelEnvironment() {
        $this->check_if_env_file_exists();
        $default_env = $this->getDefaultEnvVar();
        $env = $this->setUserEnvVars($default_env);
        $this->writeEnvFile($env);
        $this->call('key:generate');
    }

    private function check_if_env_file_exists() {
        if(file_exists($this->laravel->environmentFilePath())) {
            throw new \RuntimeException('This application is already set up');
        }
    }

    private function setUserEnvVars($defaultEnv) {
        if ( $appName = $this->option('app-name')) {
            $defaultEnv['APP_NAME'] = $appName;
        } else {
            foreach($defaultEnv as $key => &$value) {
                $tmp = $this->ask($key . " [{$value}]");
                if (!empty($tmp)) {
                    $value = $tmp;
                }
            }
        }
        return $defaultEnv;
    }

    private function writeEnvFile($envVars, $in=null, $out=null) {
        $file_env = $out ?? $this->laravel->environmentFilePath();
        $file_env_example = $in ?? $this->laravel->environmentFilePath().'.example';

        $contents = file_get_contents($file_env_example);
        if (!empty($envVars)) {
            $contents = $this->overwriteDefaults($contents, $envVars);
        }
        file_put_contents($file_env, $contents);
    }

    private function overwriteDefaults($contents, $envVars) {
        foreach($envVars as $key => $value) {
            $contents = preg_replace(
                $this->replacementPattern($key),
                "{$key}=".$value,
                $contents);
        }
        return $contents;
    }
    private function replacementPattern($field) {
        return "/^{$field}=.*/m";
    }
}
