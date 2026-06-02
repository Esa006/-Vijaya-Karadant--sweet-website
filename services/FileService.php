<?php
/**
 * Sweets Website
 * =============================================================
 * File: FileService.php
 * Description: Secure filesystem operations and upload handling
 * Author: Antigravity - Principal Security Architect
 * Version: 1.0.0
 * =============================================================
 */

class FileService {

    private string $subDir;
    private string $uploadDir;
    private array $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
    private int $maxSize = 10 * 1024 * 1024; // 10MB

    public function __construct(string $subDir = 'products') {
        $this->subDir = $subDir;
        $this->uploadDir = ROOT_PATH . '/assets/images/' . $subDir . '/';
        $this->ensureDirectoryExists();
    }

    /**
     * Handle Secure File Upload
     */
    public function upload(array $file): ?string {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return null;
        }

        // 1. Basic Validation
        if ($file['error'] !== UPLOAD_ERR_OK) {
            error_log('[FileService] Upload error code: ' . $file['error']);
            return null;
        }

        if ($file['size'] > $this->maxSize) {
            error_log('[FileService] File too large');
            return null;
        }

        // 2. MIME Type Verification (Finest Grade)
        $mimeType = '';
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
        } elseif (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($file['tmp_name']);
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $map = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'];
            $mimeType = $map[$ext] ?? 'application/octet-stream';
        }

        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            error_log('[FileService] Invalid MIME type: ' . $mimeType);
            return null;
        }

        // 3. Unique Filename Generation
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('sw_', true) . '.' . $extension;
        $targetPath = $this->uploadDir . $filename;

        // 4. Move File Securely
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // 5. Compress Image (GD)
            $this->compressImage($targetPath, $mimeType);
            
            // Return relative path for DB
            return 'assets/images/' . $this->subDir . '/' . $filename;
        }

        return null;
    }

    /**
     * Compress and resize image using PHP GD
     */
    private function compressImage(string $filePath, string $mimeType): void {
        if (!extension_loaded('gd')) {
            return; // GD not available, skip compression
        }

        $maxWidth = 1200;
        $quality = 80; // For JPEG

        list($width, $height) = getimagesize($filePath);
        if (!$width || !$height) return;

        // Calculate new dimensions if image is larger than maxWidth
        if ($width > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int)($height * ($maxWidth / $width));
        } else {
            // Keep original dimensions, just compress
            $newWidth = $width;
            $newHeight = $height;
        }

        $image = null;
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($filePath);
                // Convert transparent PNG to white background for JPEG
                if ($image) {
                    $bg = imagecreatetruecolor($newWidth, $newHeight);
                    $white = imagecolorallocate($bg, 255, 255, 255);
                    imagefill($bg, 0, 0, $white);
                    imagecopyresampled($bg, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    imagejpeg($bg, $filePath, $quality);
                    imagedestroy($bg);
                    imagedestroy($image);
                    return; // Done
                }
                break;
            case 'image/webp':
                // For webp, we could just leave it or compress it further, but usually it's fine.
                // Leaving webp as is for now.
                return;
        }

        if ($image) {
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            // Save as JPEG back to the same path (overwriting)
            imagejpeg($newImage, $filePath, $quality);
            imagedestroy($newImage);
            imagedestroy($image);
        }
    }

    /**
     * Delete file from disk
     */
    public function delete(string $relativePath): bool {
        $path = ROOT_PATH . '/' . $relativePath;
        if (file_exists($path) && is_file($path)) {
            return unlink($path);
        }
        return false;
    }

    private function ensureDirectoryExists(): void {
        if (!is_dir($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true)) {
                error_log('[FileService] Failed to create directory: ' . $this->uploadDir);
            }
        }
    }
}

/**
 * Helper since in_array is standard but let's be explicit
 */
function in_row($needle, $haystack) {
    return in_array($needle, $haystack);
}
