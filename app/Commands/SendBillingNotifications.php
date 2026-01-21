<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Controllers\WhatsappBillingNotification;

class SendBillingNotifications extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'WhatsApp';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'whatsapp:billing:send-all';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Kirim semua notifikasi tagihan WhatsApp (H-7, H-3, H-1, H, Payment, Isolir)';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'whatsapp:billing:send-all [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--type' => 'Tipe notifikasi: all, due-date, h-1, h-3, h-7, payment, isolir',
        '--test' => 'Mode test (tidak benar-benar kirim)',
    ];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        CLI::write('╔═══════════════════════════════════════════════════════╗', 'green');
        CLI::write('║   WhatsApp Billing Notification System               ║', 'green');
        CLI::write('╚═══════════════════════════════════════════════════════╝', 'green');
        CLI::newLine();

        $type = CLI::getOption('type') ?? 'all';
        $isTest = CLI::getOption('test') !== null;

        if ($isTest) {
            CLI::write('[TEST MODE] Tidak ada pesan yang akan dikirim', 'yellow');
            CLI::newLine();
        }

        CLI::write('⏰ Waktu: ' . date('Y-m-d H:i:s'), 'cyan');
        CLI::write('📱 Mode: ' . ($isTest ? 'TEST' : 'PRODUCTION'), 'cyan');
        CLI::write('🎯 Type: ' . strtoupper($type), 'cyan');
        CLI::newLine();

        // Initialize controller
        $controller = new WhatsappBillingNotification();

        try {
            $results = [];

            switch ($type) {
                case 'due-date':
                    CLI::write('📅 Mengirim notifikasi jatuh tempo hari ini...', 'yellow');
                    $results['due_date'] = $controller->sendDueDateNotifications();
                    break;

                case 'h-1':
                    CLI::write('📅 Mengirim notifikasi H-1...', 'yellow');
                    $results['h_minus_1'] = $controller->sendPreDueDateNotifications(1);
                    break;

                case 'h-3':
                    CLI::write('📅 Mengirim notifikasi H-3...', 'yellow');
                    $results['h_minus_3'] = $controller->sendPreDueDateNotifications(3);
                    break;

                case 'h-7':
                    CLI::write('📅 Mengirim notifikasi H-7...', 'yellow');
                    $results['h_minus_7'] = $controller->sendPreDueDateNotifications(7);
                    break;

                case 'payment':
                    CLI::write('💰 Mengirim konfirmasi pembayaran...', 'yellow');
                    $results['payment'] = $controller->sendPaymentConfirmations();
                    break;

                case 'isolir':
                    CLI::write('⚠️  Mengirim notifikasi isolir...', 'yellow');
                    $results['isolir'] = $controller->sendIsolirNotifications();
                    break;

                case 'all':
                default:
                    CLI::write('🔄 Mengirim semua jenis notifikasi...', 'yellow');
                    CLI::newLine();

                    // H-7
                    CLI::write('  📅 H-7: Pengingat 7 hari sebelum jatuh tempo...', 'white');
                    $results['h_minus_7'] = $controller->sendPreDueDateNotifications(7);

                    // H-3
                    CLI::write('  📅 H-3: Pengingat 3 hari sebelum jatuh tempo...', 'white');
                    $results['h_minus_3'] = $controller->sendPreDueDateNotifications(3);

                    // H-1
                    CLI::write('  📅 H-1: Pengingat 1 hari sebelum jatuh tempo...', 'white');
                    $results['h_minus_1'] = $controller->sendPreDueDateNotifications(1);

                    // H-0 (Due Date)
                    CLI::write('  📅 H-0: Notifikasi jatuh tempo hari ini...', 'white');
                    $results['due_date'] = $controller->sendDueDateNotifications();

                    // Payment Confirmations
                    CLI::write('  💰 Konfirmasi pembayaran...', 'white');
                    $results['payment'] = $controller->sendPaymentConfirmations();

                    // Isolir Notifications
                    CLI::write('  ⚠️  Notifikasi isolir...', 'white');
                    $results['isolir'] = $controller->sendIsolirNotifications();

                    break;
            }

            CLI::newLine();
            CLI::write('═══════════════════════════════════════════════════════', 'green');
            CLI::write('                    📊 HASIL PENGIRIMAN                ', 'green');
            CLI::write('═══════════════════════════════════════════════════════', 'green');
            CLI::newLine();

            $totalSent = 0;
            $totalFailed = 0;

            foreach ($results as $notifType => $result) {
                if (is_array($result)) {
                    $sent = $result['sent'] ?? 0;
                    $failed = $result['failed'] ?? 0;
                    $skipped = $result['skipped'] ?? 0;

                    $totalSent += $sent;
                    $totalFailed += $failed;

                    $typeLabel = str_replace('_', ' ', strtoupper($notifType));

                    CLI::write("  {$typeLabel}:", 'cyan');
                    CLI::write("    ✅ Terkirim  : {$sent}", 'green');

                    if ($failed > 0) {
                        CLI::write("    ❌ Gagal     : {$failed}", 'red');
                    }

                    if ($skipped > 0) {
                        CLI::write("    ⏭️  Dilewati  : {$skipped}", 'yellow');
                    }

                    CLI::newLine();
                }
            }

            CLI::write('═══════════════════════════════════════════════════════', 'green');
            CLI::write("  🎯 TOTAL TERKIRIM : {$totalSent}", 'green');

            if ($totalFailed > 0) {
                CLI::write("  ⚠️  TOTAL GAGAL   : {$totalFailed}", 'red');
            }

            CLI::write('═══════════════════════════════════════════════════════', 'green');
            CLI::newLine();

            if ($totalSent > 0) {
                CLI::write('✨ Notifikasi berhasil dikirim!', 'green');
            } elseif ($totalFailed > 0) {
                CLI::write('⚠️  Beberapa notifikasi gagal dikirim. Cek log untuk detail.', 'yellow');
            } else {
                CLI::write('ℹ️  Tidak ada notifikasi yang perlu dikirim saat ini.', 'blue');
            }

            CLI::newLine();
            CLI::write('💡 Tip: Lihat log detail di writable/logs/', 'light_gray');
            CLI::newLine();
        } catch (\Exception $e) {
            CLI::newLine();
            CLI::error('❌ ERROR: ' . $e->getMessage());
            CLI::newLine();
            CLI::write('Stack trace:', 'red');
            CLI::write($e->getTraceAsString(), 'light_gray');
            CLI::newLine();

            return EXIT_ERROR;
        }

        return EXIT_SUCCESS;
    }
}
