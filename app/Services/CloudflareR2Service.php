<?php

namespace App\Services;

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\Credentials\Credentials;
use Aws\Signature\SignatureV4;
use GuzzleHttp\Psr7\Request;

class CloudflareR2Service
{
    private $s3Client;
    private $bucketName;
    private $accountId;
    private $accessKeyId;
    private $secretAccessKey;

    public function __construct()
    {
        $this->accountId = env('CLOUDFLARE_ACCOUNT_ID');
        $this->accessKeyId = env('CLOUDFLARE_ACCESS_KEY_ID');
        $this->secretAccessKey = env('CLOUDFLARE_SECRET_ACCESS_KEY');
        $this->bucketName = env('CLOUDFLARE_BUCKET_NAME');

        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region' => 'auto',
            'endpoint' => "https://{$this->accountId}.r2.cloudflarestorage.com",
            'credentials' => [
                'key' => $this->accessKeyId,
                'secret' => $this->secretAccessKey,
            ],
            'use_path_style_endpoint' => true,
        ]);
    }

    public function getFileUrl($key)
    {
        try {
            $expires = 3600; // URL expiration time in seconds
            $time = time();
            $requestTime = gmdate('Ymd\THis\Z', $time);
            $timestamp = gmdate('Ymd', $time);

            $cloudflareUrl = "https://{$this->bucketName}.{$this->accountId}.r2.cloudflarestorage.com/{$key}";

            $credentialScope = "{$timestamp}/auto/s3/aws4_request";

            $query = http_build_query([
                'X-Amz-Algorithm' => 'AWS4-HMAC-SHA256',
                'X-Amz-Credential' => "{$this->accessKeyId}/{$credentialScope}",
                'X-Amz-Date' => $requestTime,
                'X-Amz-Expires' => $expires,
                'X-Amz-SignedHeaders' => 'host',
            ]);

            $canonicalRequest = "GET\n/{$key}\n{$query}\nhost:{$this->bucketName}.{$this->accountId}.r2.cloudflarestorage.com\n\nhost\nUNSIGNED-PAYLOAD";

            $stringToSign = "AWS4-HMAC-SHA256\n{$requestTime}\n{$credentialScope}\n" . hash('sha256', $canonicalRequest);

            $signingKey = hash_hmac('sha256', 'aws4_request', 
                hash_hmac('sha256', 's3', 
                    hash_hmac('sha256', 'auto', 
                        hash_hmac('sha256', $timestamp, "AWS4{$this->secretAccessKey}", true),
                    true),
                true),
            true);

            $signature = hash_hmac('sha256', $stringToSign, $signingKey);

            $signedUrl = "{$cloudflareUrl}?{$query}&X-Amz-Signature={$signature}";

            return $signedUrl;
        } catch (AwsException $e) {
            \Log::error('Error generating signed URL: ' . $e->getMessage());
            return null;
        }
    }

    public function uploadFileToBucket($imageData, $uuid)
    {
        $base64Data = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $imageData));
        $mimeType = explode(':', explode(';', $imageData)[0])[1];

        try {
            $result = $this->s3Client->putObject([
                'Bucket' => $this->bucketName,
                'Key' => $uuid,
                'Body' => $base64Data,
                'ContentType' => $mimeType,
                'ACL' => 'public-read',
            ]);

            return $this->getFileUrl($uuid);
        } catch (AwsException $e) {
            \Log::error('File upload error: ' . $e->getMessage());
            return null;
        }
    }

    public function deleteFile($key)
    {
        try {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucketName,
                'Key' => $key,
            ]);
            return true;
        } catch (AwsException $e) {
            \Log::error('File deletion error: ' . $e->getMessage());
            return false;
        }
    }
}