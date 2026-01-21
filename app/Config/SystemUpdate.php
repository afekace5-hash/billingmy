<?php

namespace App\Config;

use CodeIgniter\Config\BaseConfig;

class SystemUpdate extends BaseConfig
{
    /**
     * Git repository URL
     */
    public string $gitRepository = "https://github.com/AFIK35/billingkimo.git";

    /**
     * Branch to update from
     */
    public string $gitBranch = "main";

    /**
     * Maximum number of backups to keep
     */
    public int $maxBackups = 10;

    /**
     * Allowed IP addresses for update operations
     * Leave empty to allow all authenticated users
     */
    public array $allowedIPs = [
        "127.0.0.1",
        "::1"
    ];

    /**
     * Backup retention days
     */
    public int $backupRetentionDays = 30;

    /**
     * Enable automatic backup before update
     */
    public bool $autoBackupBeforeUpdate = true;

    /**
     * Directories to exclude from backup
     */
    public array $backupExcludes = [
        "writable/cache",
        "writable/logs",
        "writable/session",
        "writable/backups",
        ".git",
        "vendor",
        "node_modules"
    ];

    /**
     * Commands to run after update
     */
    public array $postUpdateCommands = [
        "composer install --no-dev --optimize-autoloader",
        "php spark cache:clear"
    ];
}
