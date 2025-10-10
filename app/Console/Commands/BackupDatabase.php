<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database {--filename=} {--connection=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the database to storage/backups directory';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $connection = $this->option('connection') ?: config('database.default');
        $config = config("database.connections.{$connection}");

        if (!$config) {
            $this->error("Database connection {$connection} not found.");
            return 1;
        }

        // Create backups directory if it doesn't exist
        $backupDir = storage_path('backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // Generate filename
        $fileName = $this->option('filename') 
            ?? "backup_{$connection}_" . now()->format('Y-m-d_H-i-s') . '.sql';
        
        $filePath = $backupDir . '/' . $fileName;

        try {
            // Determine the database driver
            switch ($config['driver']) {
                case 'mysql':
                    $result = $this->backupMysql($config, $filePath);
                    break;
                
                case 'pgsql':
                    $result = $this->backupPostgres($config, $filePath);
                    break;
                
                case 'sqlite':
                    $result = $this->backupSqlite($config, $filePath);
                    break;
                
                default:
                    $this->error("Unsupported database driver: {$config['driver']}");
                    return 1;
            }

            if ($result) {
                $this->info("Database backup created successfully: {$filePath}");
                $this->info("File size: " . $this->formatBytes(filesize($filePath)));
                return 0;
            } else {
                $this->error("Failed to create database backup.");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Error during backup: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Backup MySQL database
     */
    private function backupMysql($config, $filePath)
    {
        $host = $config['host'];
        $port = $config['port'];
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        $command = [
            'mysqldump',
            "--host={$host}",
            "--port={$port}",
            "--user={$username}",
            "--password={$password}",
            '--single-transaction',
            '--routines',
            '--triggers',
            $database
        ];

        $process = new Process($command);
        $process->setTimeout(300); // 5 minutes timeout
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        file_put_contents($filePath, $process->getOutput());
        return true;
    }

    /**
     * Backup PostgreSQL database
     */
    private function backupPostgres($config, $filePath)
    {
        $host = $config['host'];
        $port = $config['port'];
        $database = $config['database'];
        $username = $config['username'];
        $password = $config['password'];

        // Set password in environment for pg_dump
        $env = [
            'PGPASSWORD' => $password
        ];

        $command = [
            'pg_dump',
            "--host={$host}",
            "--port={$port}",
            "--username={$username}",
            "--dbname={$database}",
            '--verbose',
            '--clean',
            '--no-owner',
            '--no-acl'
        ];

        $process = new Process($command, null, $env);
        $process->setTimeout(300); // 5 minutes timeout
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        file_put_contents($filePath, $process->getOutput());
        return true;
    }

    /**
     * Backup SQLite database
     */
    private function backupSqlite($config, $filePath)
    {
        $databasePath = $config['database'];

        if (!file_exists($databasePath)) {
            throw new \Exception("SQLite database file does not exist: {$databasePath}");
        }

        // Simply copy the SQLite file
        return copy($databasePath, $filePath);
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, $precision) . ' ' . $units[$i];
    }
}