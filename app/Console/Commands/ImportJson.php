<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class ImportJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assessments:import-json {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import JSON dump from old system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $file = $this->argument('file');
        $json = file_get_contents($file);
        $data = json_decode($json, true);

        $this->info('Importing staff users...' . count($data['users']['staff']));

        $passwords  = [];
        foreach(range(1, 5) as $i) {
            $passwords[] = \Illuminate\Support\Facades\Hash::make(Str::random(32), ['rounds' => 12]);
        }
        foreach ($data['users']['staff'] as $user) {
            $this->info('Importing user: ' . $user['email']);
            $user = User::firstOrCreate([
                'email' => $user['email'],
            ], [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'surname' => $user['surname'],
                'forenames' => $user['forenames'],
                'is_admin' => $user['is_admin'],
                'is_staff' => true,
                'school' => $user['is_admin'] ? 'ENG' : null,
                'password' => Arr::random($passwords)
            ]);
        }

        $this->info('Importing student users...' . count($data['users']['students']));

        $bar = $this->output->createProgressBar(count($data['users']['students']));

        $bar->start();
        $count = 0;
        foreach ($data['users']['students'] as $user) {
            $user = User::firstOrCreate([
                'email' => $user['email'],
            ], [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'surname' => $user['surname'],
                'forenames' => $user['forenames'],
                'is_admin' => false,
                'is_staff' => false,
                'school' => null,
                'password' => Arr::random($passwords)
            ]);
            $count++;
            if ($count % 100 === 0) {
                $bar->advance();
            }
        }
        $bar->finish();
    }
}
