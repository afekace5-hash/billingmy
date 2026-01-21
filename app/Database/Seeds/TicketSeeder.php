<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run()
    {
        // Get some random customers
        $customerModel = new \App\Models\CustomerModel();
        $customers = $customerModel->findAll(5);

        if (empty($customers)) {
            // If no customers exist, create some basic sample data
            $customersData = [
                [
                    'nama_pelanggan' => 'John Doe',
                    'nomor_layanan' => 'ISP001',
                    'email' => 'john@example.com',
                    'telepphone' => '081234567890',
                    'address' => 'Jl. Merdeka No. 123'
                ],
                [
                    'nama_pelanggan' => 'Jane Smith',
                    'nomor_layanan' => 'ISP002',
                    'email' => 'jane@example.com',
                    'telepphone' => '081234567891',
                    'address' => 'Jl. Sudirman No. 456'
                ],
                [
                    'nama_pelanggan' => 'Ahmad Rahman',
                    'nomor_layanan' => 'ISP003',
                    'email' => 'ahmad@example.com',
                    'telepphone' => '081234567892',
                    'address' => 'Jl. Diponegoro No. 789'
                ]
            ];

            foreach ($customersData as $customer) {
                $customerModel->insert($customer);
            }

            $customers = $customerModel->findAll(3);
        }

        $ticketData = [
            [
                'ticket_number' => 'TKT-' . date('Ymd') . '-0001',
                'user_id' => 1,
                'customer_id' => $customers[0]['id_customers'] ?? 1,
                'subject' => 'Internet connection is slow',
                'description' => 'Internet connection has been very slow for the past 3 days. Speed test shows only 2 Mbps instead of the subscribed 20 Mbps. Please help investigate and fix this issue.',
                'status' => 'open',
                'priority' => 'high',
                'category' => 'teknis',
                'attachment' => null,
                'assigned_to' => null,
                'resolved_at' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
            ],
            [
                'ticket_number' => 'TKT-' . date('Ymd') . '-0002',
                'user_id' => 1,
                'customer_id' => $customers[1]['id_customers'] ?? 2,
                'subject' => 'Billing inquiry - overcharge',
                'description' => 'I received my monthly bill and noticed there is an extra charge of Rp 150,000 that I do not understand. Could you please explain what this charge is for?',
                'status' => 'in_progress',
                'priority' => 'medium',
                'category' => 'billing',
                'attachment' => null,
                'assigned_to' => 1,
                'resolved_at' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-6 hours'))
            ],
            [
                'ticket_number' => 'TKT-' . date('Ymd') . '-0003',
                'user_id' => 1,
                'customer_id' => $customers[2]['id_customers'] ?? 3,
                'subject' => 'Request new installation',
                'description' => 'I would like to request a new internet installation at my office. Address: Jl. Kemerdekaan No. 45, Jakarta. Please provide information about available packages and installation schedule.',
                'status' => 'open',
                'priority' => 'low',
                'category' => 'instalasi',
                'attachment' => null,
                'assigned_to' => null,
                'resolved_at' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-4 hours')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-4 hours'))
            ],
            [
                'ticket_number' => 'TKT-' . date('Ymd') . '-0004',
                'user_id' => 1,
                'customer_id' => $customers[0]['id_customers'] ?? 1,
                'subject' => 'Complete internet outage',
                'description' => 'Complete internet outage since this morning at 8 AM. No connection at all. This is urgent as we are running a business and need internet access immediately.',
                'status' => 'open',
                'priority' => 'urgent',
                'category' => 'gangguan',
                'attachment' => null,
                'assigned_to' => null,
                'resolved_at' => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
            ],
            [
                'ticket_number' => 'TKT-' . date('Ymd') . '-0005',
                'user_id' => 1,
                'customer_id' => $customers[1]['id_customers'] ?? 2,
                'subject' => 'WiFi router not working',
                'description' => 'The WiFi router provided by your company has stopped working. The power light is on but no WiFi signal is being broadcast. I have tried restarting it multiple times.',
                'status' => 'resolved',
                'priority' => 'medium',
                'category' => 'teknis',
                'attachment' => null,
                'assigned_to' => 1,
                'resolved_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'updated_at' => date('Y-m-d H:i:s', strtotime('-1 hour'))
            ]
        ];

        // Clear existing tickets first
        $this->db->table('tickets')->truncate();

        // Insert new sample tickets
        foreach ($ticketData as $ticket) {
            $this->db->table('tickets')->insert($ticket);
        }
    }
}
