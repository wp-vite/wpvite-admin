<?php

namespace App\Services\Storage;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class S3StorageService
{
    protected $disk;

    public function __construct()
    {
        $this->disk = Storage::disk('s3_admin'); // Assumes 's3' is defined in config/filesystems.php
    }

    /**
     * Upload a file from local path to S3.
     *
     * @param string $localPath
     * @param string $s3Path
     * @return bool
     */
    public function upload(string $localPath, string $s3Path): bool
    {
        try {
            return $this->disk->put($s3Path, file_get_contents($localPath));
        } catch (\Exception $e) {
            Log::error("S3 upload failed: " . $e->getMessage());dd($e->getMessage());
            return false;
        }
    }

    /**
     * Download a file from S3 to local path.
     *
     * @param string $s3Path
     * @param string $localPath
     * @return bool
     */
    public function download(string $s3Path, string $localPath): bool
    {
        try {
            $contents = $this->disk->get($s3Path);
            file_put_contents($localPath, $contents);
            return true;
        } catch (\Exception $e) {
            Log::error("S3 download failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a file exists on S3.
     *
     * @param string $s3Path
     * @return bool
     */
    public function exists(string $s3Path): bool
    {
        return $this->disk->exists($s3Path);
    }

    /**
     * Delete a file from S3.
     *
     * @param string $s3Path
     * @return bool
     */
    public function delete(string $s3Path): bool
    {
        return $this->disk->delete($s3Path);
    }
}
