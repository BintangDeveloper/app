<?php

use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

Route::post('/csp', function (Request $request) {
    $report = $request->all();
    
    // Format the message to be sent to Discord
    $message = [
        'content' => '**CSP Violation Report**',
        'embeds' => [
            [
                'title' => 'CSP Violation Detected',
                'description' => 'A violation of the Content Security Policy was reported.',
                'fields' => [
                    [
                        'name' => 'Document URI',
                        'value' => $report['csp-report']['document-uri'] ?? 'N/A',
                    ],
                    [
                        'name' => 'Violated Directive',
                        'value' => $report['csp-report']['violated-directive'] ?? 'N/A',
                    ],
                    [
                        'name' => 'Blocked URI',
                        'value' => $report['csp-report']['blocked-uri'] ?? 'N/A',
                    ],
                ],
                'color' => 16711680, // Red color in Discord
                'timestamp' => now()->toIso8601String(),
            ]
        ]
    ];
    
    // Send the violation report to Discord webhook
    Http::post('https://discord.com/api/webhooks/1236951203561340999/ytCSUHYOR4d8ZNEZkLx53YVn1RXAgCypivDQrNcFqrA_aF84IuBqIUkjnr5puHOF38_f', $message);

    return response()->json(['status' => 'received']);
});


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test', function (Request $request) {
  $data = 'Hello World!';
  
  return ResponseHelper::success($data, 200);
});

Route::get('/phpinfo', function (Request $request) {
  $data = phpinfo();
  
  return ResponseHelper::success($data, 200);
});

use App\Http\Controllers\api\BlogController;

Route::prefix('blog')->group(function () {
    Route::get('post', [BlogController::class, 'getPost']);
    Route::get('posts', [BlogController::class, 'getAllPosts']);
    Route::put('post', [BlogController::class, 'editPost']);
    Route::delete('post', [BlogController::class, 'deletePost']);
});
