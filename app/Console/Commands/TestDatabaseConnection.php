<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestDatabaseConnection extends Command
{
    protected $signature = 'test:db-connection';
    protected $description = 'Test the database connection';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            DB::connection()->getPdo();
            $this->info('Connexion à la base de données réussie!');
        } catch (\Exception $e) {
            $this->error('Échec de la connexion à la base de données: ' . $e->getMessage());
        }
    }
}

