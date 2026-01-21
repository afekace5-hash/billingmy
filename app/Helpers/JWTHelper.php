<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHelper
{
    private static $secretKey;
    private static $algorithm = 'HS256';
    private static $tokenExpiry = 2592000; // 30 days in seconds

    public function __construct()
    {
        // Get secret key from environment or generate one
        self::$secretKey = getenv('JWT_SECRET_KEY') ?: 'your-secret-key-change-this-in-production-12345678';
    }

    /**
     * Generate JWT token for customer
     * 
     * @param array $customerData Customer data
     * @return string JWT token
     */
    public static function generateToken($customerData)
    {
        $issuedAt = time();
        $expire = $issuedAt + self::$tokenExpiry;

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'iss' => base_url(),
            'data' => [
                'customer_id' => $customerData['id_customers'],
                'nomor_layanan' => $customerData['nomor_layanan'],
                'email' => $customerData['email'],
                'nama' => $customerData['nama_pelanggan']
            ]
        ];

        self::$secretKey = getenv('JWT_SECRET_KEY') ?: 'your-secret-key-change-this-in-production-12345678';

        return JWT::encode($payload, self::$secretKey, self::$algorithm);
    }

    /**
     * Validate and decode JWT token
     * 
     * @param string $token JWT token
     * @return object|null Decoded token data or null if invalid
     */
    public static function validateToken($token)
    {
        try {
            self::$secretKey = getenv('JWT_SECRET_KEY') ?: 'your-secret-key-change-this-in-production-12345678';

            $decoded = JWT::decode($token, new Key(self::$secretKey, self::$algorithm));
            return $decoded;
        } catch (\Exception $e) {
            log_message('error', 'JWT validation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get customer ID from token
     * 
     * @param string $token JWT token
     * @return int|null Customer ID or null if invalid
     */
    public static function getCustomerIdFromToken($token)
    {
        $decoded = self::validateToken($token);

        if ($decoded && isset($decoded->data->customer_id)) {
            return $decoded->data->customer_id;
        }

        return null;
    }

    /**
     * Generate random token for password reset/activation
     * 
     * @return string Random token
     */
    public static function generateRandomToken()
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Generate 6-digit OTP
     * 
     * @return string 6-digit OTP
     */
    public static function generateOTP()
    {
        return sprintf('%06d', mt_rand(0, 999999));
    }
}
