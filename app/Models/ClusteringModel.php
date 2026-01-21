<?php

namespace App\Models;

use CodeIgniter\Model;

class ClusteringModel extends Model
{
    protected $table = 'clustering';
    protected $primaryKey = 'id_clustering';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $allowedFields = [
        'name',
        'number_of_ports',
        'type_option',
        'lokasi_server_id',
        'coordinate',
        'latitude',
        'longitude',
        'address'
    ];
    protected $useTimestamps = false;

    public function getDatatables()
    {
        $request = service('request');
        $draw = $request->getGet('draw');
        $start = $request->getGet('start');
        $length = $request->getGet('length');

        $total = $this->countAllResults(false);
        $data = $this->findAll($length, $start);

        $result = [];
        $i = $start + 1;
        $customerModel = model('CustomerModel');
        foreach ($data as $row) {
            // Hitung jumlah pelanggan yang memakai port pada cluster ini
            $used_ports = $customerModel->where('customer_clustering_id', $row['id_clustering'])->countAllResults();
            $remaining_ports = isset($row['number_of_ports']) ? ($row['number_of_ports'] - $used_ports) : '-';
            $result[] = [
                'DT_RowIndex' => $i++,
                'name' => $row['name'],
                'type_option' => $row['type_option'],
                'server_location_id' => $row['server_location_id'],
                'number_of_ports' => $row['number_of_ports'],
                'remaining_ports' => $remaining_ports,
                'coordinate' => $row['coordinate'],
                'address' => $row['address'],
                'action' => '<button class="btn btn-sm btn-primary">Edit</button>'
            ];
        }

        return [
            'draw' => intval($draw),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $result
        ];
    }
}
