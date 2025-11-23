<?php
// FILE: /app/helpers/FileUpload.php

/**
 * File upload helper class
 * Handles file uploads with validation and security
 */
class FileUpload
{
    private $allowedExtensions;
    private $maxSize;
    private $uploadPath;
    private $errors = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $config = require __DIR__ . '/../../config/app.php';

        $this->allowedExtensions = $config['upload']['allowed_extensions'];
        $this->maxSize = $config['upload']['max_size'];
        $this->uploadPath = $config['upload']['path'];

        // Ensure upload directory exists
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    /**
     * Upload a file
     *
     * @param array $file File from $_FILES
     * @param string $subfolder Optional subfolder
     * @return string|false Filename on success, false on failure
     */
    public function upload($file, $subfolder = '')
    {
        $this->errors = [];

        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $this->errors[] = 'No file was uploaded';
            return false;
        }

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadError($file['error']);
            return false;
        }

        // Validate file size
        if ($file['size'] > $this->maxSize) {
            $this->errors[] = 'File size exceeds maximum allowed size of ' . $this->formatBytes($this->maxSize);
            return false;
        }

        // Validate file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            $this->errors[] = 'File type not allowed. Allowed types: ' . implode(', ', $this->allowedExtensions);
            return false;
        }

        // Validate file type by mime
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'application/pdf'
        ];

        if (!in_array($mimeType, $allowedMimes)) {
            $this->errors[] = 'Invalid file type';
            return false;
        }

        // Generate unique filename
        $filename = $this->generateUniqueFilename($extension);

        // Create subfolder if specified
        $targetPath = $this->uploadPath;
        if ($subfolder) {
            $targetPath .= $subfolder . '/';
            if (!is_dir($targetPath)) {
                mkdir($targetPath, 0755, true);
            }
        }

        // Move uploaded file
        $targetFile = $targetPath . $filename;
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return ($subfolder ? $subfolder . '/' : '') . $filename;
        }

        $this->errors[] = 'Failed to move uploaded file';
        return false;
    }

    /**
     * Delete a file
     *
     * @param string $filename Filename to delete
     * @return bool
     */
    public function delete($filename)
    {
        $filepath = $this->uploadPath . $filename;

        // Prevent directory traversal
        $realpath = realpath($filepath);
        if ($realpath === false || strpos($realpath, realpath($this->uploadPath)) !== 0) {
            return false;
        }

        if (file_exists($filepath)) {
            return unlink($filepath);
        }

        return false;
    }

    /**
     * Get upload errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Generate unique filename
     *
     * @param string $extension File extension
     * @return string
     */
    private function generateUniqueFilename($extension)
    {
        return uniqid('upload_', true) . '_' . time() . '.' . $extension;
    }

    /**
     * Get upload error message
     *
     * @param int $code Error code
     * @return string
     */
    private function getUploadError($code)
    {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension',
        ];

        return $errors[$code] ?? 'Unknown upload error';
    }

    /**
     * Format bytes to human readable
     *
     * @param int $bytes Bytes
     * @return string
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
