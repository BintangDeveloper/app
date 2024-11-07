<?php

namespace App\Http\Controllers;

use App\AppwriteClient;
use App\Helpers\ResponseHelper;
use App\Helpers\MediaResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class StorageController extends Controller
{
    protected $storage;

    public function __construct()
    {
        $this->storage = AppwriteClient::getService('Storage');
    }
    
    public function listFiles(string $bucketId): JsonResponse
    {
        try {
            $result = $this->storage->listFiles(bucketId: $bucketId);
            return ResponseHelper::success($result);
        } catch (Exception $e) {
            return ResponseHelper::error("Failed to list files.", ['bucketId' => $bucketId, 'error' => $e->getMessage()]);
        }
    }

    public function getFileInfo(string $bucketId, string $fileId): JsonResponse
    {
        try {
            $file = $this->storage->getFile(bucketId: $bucketId, fileId: $fileId);
            return ResponseHelper::success($file);
        } catch (Exception $e) {
            return ResponseHelper::error("File not found.", ['bucketId' => $bucketId, 'fileId' => $fileId, 'error' => $e->getMessage()]);
        }
    }
    
    public function getFileDownload(string $bucketId, string $fileId): mixed
    {
        try {
            $result = $this->storage->getFileDownload(bucketId: $bucketId, fileId: $fileId);
            
            return MediaResponseHelper::download ($result);
        } catch (Exception $e) {
            return ResponseHelper::error("Failed to download file.", ['bucketId' => $bucketId, 'fileId' => $fileId, 'error' => $e->getMessage()]);
        }
    }
    
    public function getFilePreview(string $bucketId, string $fileId): mixed
    {
        try {
            $result = $this->storage->getFilePreview(bucketId: $bucketId, fileId: $fileId);
            return MediaResponseHelper::media($result);
        } catch (Exception $e) {
            return ResponseHelper::error("Failed to preview file.", ['bucketId' => $bucketId, 'fileId' => $fileId, 'error' => $e->getMessage()]);
        }
    }
    
    public function getFileView(string $bucketId, string $fileId): mixed
    {
        try {
            $result = $this->storage->getFileView(bucketId: $bucketId, fileId: $fileId);
            
            return MediaResponseHelper::media($result);
            
        } catch (Exception $e) {
            return ResponseHelper::error("Failed to view file.", ['bucketId' => $bucketId, 'fileId' => $fileId, 'error' => $e->getMessage()]);
        }
    }

    public function uploadFile(Request $request, string $bucketId): JsonResponse
    {
        $this->validateFile($request);

        try {
            $file = $request->file('file');
            $fileId = $this->generateFileId();
            $fileContent = file_get_contents($file->getRealPath());
            $fileName = $file->getClientOriginalName();

            $inputFile = ['file' => $fileContent, 'filename' => $fileName];

            $uploadedFile = $this->storage->createFile(
                bucketId: $bucketId,
                fileId: $fileId,
                file: $inputFile,
                permissions: ['read("any")']
            );

            return ResponseHelper::success($uploadedFile);
        } catch (Exception $e) {
            return ResponseHelper::error("File upload failed.", ['bucketId' => $bucketId, 'error' => $e->getMessage()]);
        }
    }

    protected function validateFile(Request $request): void
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mp3,wav,flac|max:51200'
        ], [
            'file.required' => 'Please provide a file to upload.',
            'file.file' => 'The uploaded item must be a file.',
            'file.mimes' => 'Invalid file format.',
            'file.max' => 'File size cannot exceed 50MB.'
        ]);
    }

    protected function generateFileId(): string
    {
        return hash('sha1', uniqid('', true));
    }
}
