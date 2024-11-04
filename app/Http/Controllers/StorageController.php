<?php

namespace App\Http\Controllers;

use App\AppwriteClient;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Exception;

class StorageController extends Controller
{
    protected $storage;

    public function __construct()
    {
        $this->storage = AppwriteClient::getService('Storage');
    }

    public function getInfo($BUCKET_ID, $FILE_ID)
    {
        try {
            $file = $this->storage->getFile(
                bucketId: $BUCKET_ID,
                fileId: $FILE_ID
            );

            return ResponseHelper::success($file);
        } catch (Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }

    public function upload(Request $request, $BUCKET_ID)
    {
        // Ensure a file is provided
        if (!$request->hasFile('file')) {
            return ResponseHelper::error('Require input "file" as file buffer.');
        }

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $fileContent = file_get_contents($file->getRealPath());

        try {
            // Generate a unique file ID using a hash
            $fileId = hash('sha128', uniqid('', true));

            // Manually create a format compatible with Appwrite's file upload
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
            return ResponseHelper::error($e->getMessage());
        }
    }
}
