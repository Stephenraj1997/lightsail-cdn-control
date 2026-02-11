<?php
/**
 * AWS Lightsail API handler
 *
 * Handles all communication with AWS Lightsail API for cache invalidation.
 * Uses AWS Signature Version 4 for authentication.
 *
 * @package Lightsail_CDN_Control
 * @since 1.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AWS Lightsail handler class
 *
 * @since 1.0
 */
class Lightsail_CDN_AWS {
    
    /**
     * AWS Access Key ID
     *
     * @var string
     */
    private $access_key;
    
    /**
     * AWS Secret Access Key
     *
     * @var string
     */
    private $secret_key;
    
    /**
     * AWS Region
     *
     * @var string
     */
    private $region;
    
    /**
     * Lightsail Distribution Name
     *
     * @var string
     */
    private $distribution_name;
    
    /**
     * Constructor - Load AWS credentials
     *
     * @since 1.0
     */
    public function __construct() {
        // Load credentials from wp-config.php constants
        $this->access_key = defined('AWS_ACCESS_KEY_ID') ? AWS_ACCESS_KEY_ID : '';
        $this->secret_key = defined('AWS_SECRET_ACCESS_KEY') ? AWS_SECRET_ACCESS_KEY : '';
        $this->region = defined('AWS_DEFAULT_REGION') ? AWS_DEFAULT_REGION : 'us-east-1';
        $this->distribution_name = defined('LIGHTSAIL_DISTRIBUTION_NAME') ? LIGHTSAIL_DISTRIBUTION_NAME : '';
    }
    
    /**
     * Get AWS credentials status
     *
     * @since 1.0
     * @return array Credentials status
     */
    public function get_credentials_status() {
        return array(
            'access_key' => !empty($this->access_key),
            'secret_key' => !empty($this->secret_key),
            'region' => $this->region,
            'distribution_name' => $this->distribution_name,
            'configured' => !empty($this->access_key) && !empty($this->secret_key) && !empty($this->distribution_name)
        );
    }
    
    /**
     * Validate that all required credentials are set
     *
     * @since 1.0
     * @return bool True if credentials are valid
     */
    public function validate_credentials() {
        return !empty($this->access_key) && 
               !empty($this->secret_key) && 
               !empty($this->distribution_name);
    }
    
    /**
     * Create cache invalidation request
     *
     * This method creates and sends a cache invalidation request to AWS Lightsail
     * using AWS Signature Version 4 authentication.
     *
     * @since 1.0
     * @return array Result array with 'success' boolean and 'message' string
     */
    public function create_cache_invalidation() {
        // Validate credentials first
        if (!$this->validate_credentials()) {
            return array(
                'success' => false,
                'message' => __('Missing AWS credentials or distribution name. Please configure in wp-config.php', 'lightsail-cdn-control')
            );
        }
        
        // AWS service details
        $service = 'lightsail';
        $host = "lightsail.{$this->region}.amazonaws.com";
        $endpoint = "https://{$host}/";
        
        // Request payload
        $payload = json_encode(array(
            'distributionName' => $this->distribution_name
        ));
        
        // Create timestamp for request
        $amz_date = gmdate('Ymd\THis\Z');
        $date_stamp = gmdate('Ymd');
        
        // Step 1: Create canonical headers
        $canonical_headers = 
            "host:{$host}\n" .
            "x-amz-date:{$amz_date}\n" .
            "x-amz-target:Lightsail_20161128.ResetDistributionCache\n";
        
        $signed_headers = "host;x-amz-date;x-amz-target";
        
        // Step 2: Create payload hash
        $payload_hash = hash('sha256', $payload);
        
        // Step 3: Create canonical request
        $canonical_request =
            "POST\n/\n\n" .
            $canonical_headers . "\n" .
            $signed_headers . "\n" .
            $payload_hash;
        
        // Step 4: Create string to sign
        $credential_scope = "{$date_stamp}/{$this->region}/{$service}/aws4_request";
        
        $string_to_sign =
            "AWS4-HMAC-SHA256\n" .
            "{$amz_date}\n" .
            "{$credential_scope}\n" .
            hash('sha256', $canonical_request);
        
        // Step 5: Calculate signing key
        $k_date = hash_hmac('sha256', $date_stamp, "AWS4{$this->secret_key}", true);
        $k_region = hash_hmac('sha256', $this->region, $k_date, true);
        $k_service = hash_hmac('sha256', $service, $k_region, true);
        $k_signing = hash_hmac('sha256', 'aws4_request', $k_service, true);
        
        // Step 6: Calculate signature
        $signature = hash_hmac('sha256', $string_to_sign, $k_signing);
        
        // Step 7: Create authorization header
        $authorization =
            "AWS4-HMAC-SHA256 Credential={$this->access_key}/{$credential_scope}, " .
            "SignedHeaders={$signed_headers}, Signature={$signature}";
        
        // Step 8: Send request to AWS
        $response = wp_remote_post($endpoint, array(
            'headers' => array(
                'Content-Type' => 'application/x-amz-json-1.1',
                'X-Amz-Date' => $amz_date,
                'X-Amz-Target' => 'Lightsail_20161128.ResetDistributionCache',
                'Authorization' => $authorization,
            ),
            'body' => $payload,
            'timeout' => 15,
        ));
        
        // Handle response errors
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }
        
        // Check response status
        $status = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        if ($status === 200) {
            return array(
                'success' => true,
                'message' => __('Lightsail CDN cache cleared successfully', 'lightsail-cdn-control')
            );
        } else {
            return array(
                'success' => false,
                'message' => sprintf(
                    __('AWS API Error (%d): %s', 'lightsail-cdn-control'),
                    $status,
                    $body
                )
            );
        }
    }
    
    /**
     * Get region
     *
     * @since 1.0
     * @return string AWS region
     */
    public function get_region() {
        return $this->region;
    }
    
    /**
     * Get distribution name
     *
     * @since 1.0
     * @return string Distribution name
     */
    public function get_distribution_name() {
        return $this->distribution_name;
    }
}
