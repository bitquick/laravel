<?php

namespace App\Console\Commands\Developer;

use Illuminate\Console\Command;

class GitOriginCommmand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:origin {--user=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $this->createRepo();
        $this->setOrigin();
    }

    private function createRepo() {
        $appName = config('app.name');
        $this->info("Creating GitHub repository");
        $command = "curl --netrc-file ~/.ssh/netrc https://api.github.com/user/repos? -d '{\"name\":\"{$appName}\", \"private\": true}'";
        $output = [];
        $status = null;
        exec($command, $output, $status);
    }

    private function setOrigin() {
        if (empty($user = $this->option('user'))) {
           $user = $this->ask('GitHub Username');
        }

        $appName = config('app.name');
        $this->info("Setting Git origin");
        $command = "git remote set-url origin git@github.com:{$user}/{$appName}";
        dd($command);
        $output = [];
        $status = null;
        exec($command, $output, $status);
    }


}
