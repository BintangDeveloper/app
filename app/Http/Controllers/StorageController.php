<?php

namespace App\Http\Controllers;

use App\AppwriteClient;
use App\Helpers\ResponseHelper;
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

    public function getFileInfo($BUCKET_ID, $FILE_ID): JsonResponse
    {
        try {
            $file = $this->storage->getFile(
                bucketId: $BUCKET_ID,
                fileId: $FILE_ID
            );

            return ResponseHelper::success($file);
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), [
              'BUCKET_ID' => $BUCKET_ID, 
              'FILE_ID' => $FILE_ID
            ]);
        }
    }
    
    public function getFileDownload($BUCKET_ID, $FILE_ID): JsonResponse
    {
        try {
            $result = $this->storage->getFileDownload(
                bucketId: $BUCKET_ID,
                fileId: $FILE_ID
            );

            return ResponseHelper::success($result);
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), [
              'BUCKET_ID' => $BUCKET_ID, 
              'FILE_ID' => $FILE_ID
            ]);
        }
    }
    
    public function getFilePreview($BUCKET_ID, $FILE_ID)
    {
      $result = $this->storage->getFilePreview(
        bucketId: $BUCKET_ID,
        fileId: $FILE_ID
      );
      return ResponseHelper::success($result);
    }
    
    public function getFileView($BUCKET_ID, $FILE_ID)
    {
      $result = $this->storage->getFileView(
        bucketId: $BUCKET_ID,
        fileId: $FILE_ID
      );
      return ResponseHelper::success($result);
    }

    public function uploadFile(Request $request, $BUCKET_ID): JsonResponse
    {
        // Ensure a file is provided
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,mp3,wav,flac|max:51200' // max size is 50MB (51200 KB)
        ]);


        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $fileContent = file_get_contents($file->getRealPath());

        try {
            // Generate a unique file ID using a hash
            $fileId = hash('sha1', uniqid('', true));

            $inputFile = [
                'file' => $fileContent,
                'filename' => $originalName
            ];

            $uploadedFile = $this->storage->createFile(
                bucketId: $BUCKET_ID,
                fileId: $fileId,
                file: $inputFile,
                permissions: ['read("any")']
            );

            return ResponseHelper::success($uploadedFile);
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage(), [
              'BUCKET_ID' => $BUCKET_ID,
              'REQUEST' => $request
            ]);
        }
    }
}
