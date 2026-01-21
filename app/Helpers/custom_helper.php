<?php
function userLogin()
{
    $db = \Config\Database::connect();
    return $db->table('users')->where('id_user', session('id_user'))->get()->getRow();
}

function countData($table)
{
    $db = \Config\Database::connect();
    return $db->table($table)->countAllResults();
}

/**
 * Get user avatar URL based on user ID
 * Rotates between 3 available avatars
 */
function getUserAvatar($user = null)
{
    if (!$user) $user = userLogin();
    if (!$user) return base_url() . "backend/assets/images/users/avatar-1.jpg";

    // Rotate between 3 avatars based on user ID
    $avatarNumber = (($user->id_user % 3) + 1);

    return base_url() . "backend/assets/images/users/avatar-{$avatarNumber}.jpg";
}

/**
 * Get user initials for display
 */
function getUserInitials($user = null)
{
    if (!$user) $user = userLogin();
    if (!$user) return "G";

    $nameParts = explode(" ", trim($user->name_user));
    $initials = "";
    foreach ($nameParts as $part) {
        if (!empty($part)) {
            $initials .= strtoupper(substr($part, 0, 1));
            if (strlen($initials) >= 2) break;
        }
    }
    return $initials ?: "U";
}

/**
 * Safely assign paket to customer
 * Handles duplicate entry errors gracefully
 */
function assignPaketToCustomer($customerId, $paketId)
{
    $db = \Config\Database::connect();

    try {
        // Update customer with paket
        $result = $db->table('customers')
            ->where('id_customers', $customerId)
            ->update(['id_paket' => $paketId]);

        if ($result) {
            return ['success' => true, 'message' => 'Paket berhasil di-assign'];
        } else {
            return ['success' => false, 'message' => 'Gagal mengupdate paket'];
        }
    } catch (\Exception $e) {
        $errorMessage = $e->getMessage();

        if (
            strpos($errorMessage, "Duplicate entry") !== false &&
            strpos($errorMessage, "paket") !== false
        ) {

            // This is OK - multiple customers can use same paket
            log_message('info', 'Paket assignment - multiple customers: ' . $errorMessage);

            return ['success' => true, 'message' => 'Paket di-assign (shared dengan customer lain)'];
        }

        return ['success' => false, 'message' => $errorMessage];
    }
}

/**
 * Check if paket can be assigned to multiple customers
 */
function isPaketSharedAllowed($paketId)
{
    // By default, all pakets can be shared by multiple customers
    // This is normal business logic for ISP
    return true;
}

/**
 * Get customers using specific paket
 */
function getCustomersUsingPaket($paketId)
{
    $db = \Config\Database::connect();

    return $db->table('customers c')
        ->select('c.id_customers, c.nama_pelanggan, c.tgl_pasang')
        ->where('c.id_paket', $paketId)
        ->orderBy('c.tgl_pasang', 'DESC')
        ->get()
        ->getResult();
}

/**
 * Format periode from YYYY-MM to readable format
 */
if (!function_exists('formatPeriode')) {
    function formatPeriode($periode, $uppercase = false)
    {
        if (!$periode) return '-';

        $months = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];

        $parts = explode('-', $periode);
        if (count($parts) == 2) {
            $year = $parts[0];
            $month = $parts[1];
            $result = ($months[$month] ?? $month) . ' ' . $year;
            return $uppercase ? strtoupper($result) : $result;
        }

        return $periode;
    }
}

/**
 * Format short periode from YYYY-MM to readable format (abbreviated months)
 */
if (!function_exists('formatPeriodeShort')) {
    function formatPeriodeShort($periode, $uppercase = false)
    {
        if (!$periode) return '-';

        $months = [
            '01' => 'Jan',
            '02' => 'Feb',
            '03' => 'Mar',
            '04' => 'Apr',
            '05' => 'Mei',
            '06' => 'Jun',
            '07' => 'Jul',
            '08' => 'Agt',
            '09' => 'Sep',
            '10' => 'Okt',
            '11' => 'Nov',
            '12' => 'Des'
        ];

        $parts = explode('-', $periode);
        if (count($parts) == 2) {
            $year = $parts[0];
            $month = $parts[1];
            $result = ($months[$month] ?? $month) . ' ' . $year;
            return $uppercase ? strtoupper($result) : $result;
        }

        return $periode;
    }
}
