<?php

namespace App\Http\Controllers;

use App\AppwriteClient;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Exception;

class StorageController extends Controller
{
    public function get($BUCKET_ID, $FILE_ID)
    {
        try {
            $storage = AppwriteClient::getService('Storage');
            
            $file = $storage->getFile(
                bucketId: $BUCKET_ID,
                fileId: $FILE_ID
            );

            return ResponseHelper::success($file);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            
            return ResponseHelper::error($errorMessage);
        }
    }
}
