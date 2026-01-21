<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RestoreRouterConfig extends BaseCommand
{
    protected $group = 'Database';
    protected $name = 'router:restore-config';
    protected $description = 'Restore correct router configuration';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        CLI::write('🔧 Restoring Correct Router Configuration...', 'yellow');
        CLI::write('============================================', 'white');

        // Check current configuration
        $query = $db->query("SELECT * FROM lokasi_server WHERE id_lokasi = 12");
        $router = $query->getRowArray();

        if ($router) {
            CLI::write('Current Configuration:', 'cyan');
            CLI::write('  IP Router: ' . $router['ip_router'], 'white');
            CLI::write('  Port API: ' . $router['port_api'], 'white');
            CLI::write('');

            // Restore correct configuration
            CLI::write('Restoring correct configuration...', 'yellow');
            $db->query("UPDATE lokasi_server SET port_api = 45211 WHERE id_lokasi = 12");

            CLI::write('✅ Configuration restored!', 'green');
            CLI::write('  Host (tunnel): us-1.hostddns.us:31014 (tidak berubah)', 'green');
            CLI::write('  Port API: 45211 (dikembalikan)', 'green');
            CLI::write('');

            // Verify the fix
            $query = $db->query("SELECT * FROM lokasi_server WHERE id_lokasi = 12");
            $updatedRouter = $query->getRowArray();

            CLI::write('Updated Configuration:', 'cyan');
            CLI::write('  IP Router: ' . $updatedRouter['ip_router'], 'white');
            CLI::write('  Port API: ' . $updatedRouter['port_api'], 'white');
        } else {
            CLI::write('❌ Router with ID 12 not found!', 'red');
        }

        CLI::write('Configuration restore completed.', 'green');
    }
}
