<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

Route::get('/', function () {
    return view('user.welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::get('/test-backblaze-connection', 'Api\PostController@testConnection');




Route::get('/test-backblaze-public-upload', function () {
    $localPath = public_path('assets/image6.jpg'); // Ensure this file exists
    $filename = 'uploads/posts/' . uniqid() . '.jpg';

    if (!File::exists($localPath)) {
        return response()->json([
            'success' => false,
            'message' => "File not found at: $localPath"
        ]);
    }

    $fileContents = File::get($localPath);

    try {
        $success = Storage::disk('backblaze')->put($filename, $fileContents);

        if (!$success) {
            Log::error("Backblaze Upload Failed: No error message returned.");
        }

        return response()->json([
            'success' => $success,
            'message' => $success ? "File uploaded successfully!" : "Upload failed",
            'file_path' => $filename
        ]);
    } catch (\Exception $e) {
        Log::error("Backblaze Upload Error", ['error' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'message' => "Exception: " . $e->getMessage()
        ]);
    }
});


Route::get('/test-connection', function () {
    try {
        $pdo = new PDO('mysql:host=mysql-364d7a01-nathanjemima06-2394.i.aivencloud.com;port=14363;dbname=defaultdb', 'avnadmin', 'AVNS_9oOKAUv65tF0yPi04eI', [
            PDO::MYSQL_ATTR_SSL_CA => '-----BEGIN CERTIFICATE-----
MIIETTCCArWgAwIBAgIUODPWNsamg3j6j/Drr74lS65CZUowDQYJKoZIhvcNAQEM
BQAwQDE+MDwGA1UEAww1ZmJmZWY4OTctMGFmMC00YzllLTliNGYtOTg4ZmMzOWY5
MmJlIEdFTiAxIFByb2plY3QgQ0EwHhcNMjUwMjIwMTA0MjMyWhcNMzUwMjE4MTA0
MjMyWjBAMT4wPAYDVQQDDDVmYmZlZjg5Ny0wYWYwLTRjOWUtOWI0Zi05ODhmYzM5
ZjkyYmUgR0VOIDEgUHJvamVjdCBDQTCCAaIwDQYJKoZIhvcNAQEBBQADggGPADCC
AYoCggGBAMMg56A5sUZvm8+ORiOC1RfUzifVXQQhMewG8tu2ua63Kpo8CnCr8KIm
MQuUzbjYs09h6DpOWGCIEIxJf9pvUf4PPpieLSe3qBdSPB5cC5btE6bgGk2Q5g3K
XX2Rv8RwrmnPUbVpK/kq8XlBLTOlKsRu04zgkFrn4BJQAU3Rkn5NWwZ9efSB+e41
5flNqEgEicxRPgWlrep2AsrXXMI2TU4pdX0Ilas+nPjD3BW7AgJ+gCY9SpMM/is7
LnnqvGs9OODAHUYdlfsfRvYxRnglGDiWpN0ae161guxel8PJ07gLLQbT6vyt6ldt
9hWZl7L6i8Z57zJIKbb2SXo0KCRoT/fpeebUV2YALo7l+WuekTQJFdBuS7GKxgu2
B7KyG3eHw9AWzBOR7Nm+wLwSNSUdSytGErkvFFTRzE3vhlQMzwMYCz4joliebtV3
b/Y7WaD8JN6a6fo/whHwVgc4W4TOH4Wm3ifj43lpaYGWqzSpdPkfbqk63tGoxm+c
/eVBoKih1QIDAQABoz8wPTAdBgNVHQ4EFgQU3cnM/eL8IhCGLNorXY2RlQ19v2Yw
DwYDVR0TBAgwBgEB/wIBADALBgNVHQ8EBAMCAQYwDQYJKoZIhvcNAQEMBQADggGB
ACt0gUtIazXuepkkC06t3LABOdSvG9wht2kgTAhgW1CULJgO/cyyBdMzj254qbCd
xk6jZk+Xl7V0hf3oLB1O6mLdyQkFPYdMZ/xXuJ2j5EeaPNiKo01wLiHKMi1HNwQX
4IGAiEiF/Xx7d7NzbvRGfVzNfpviPQ+OZwXLXKJ+bkIFlQOlsVyLqCVCfbh8SbiM
w5skM/jwLwHUt99A9+vKsj0WmR6o+rn8oHFR8zwH74nWF4rocNXtOpJHXnK8cER5
hxn6kDl00YLN1rH6nY2MLlGXnkNu5TUsbQsrv+sSUNzPeMHRg0zwO0GsIXMcTL5o
Nm4xu6CHYWOI8MOs7Wd9ptiP5GlBPCdK6/N/5rncDqL5+LPrj/VlxMBTsVDKRzSQ
zNY59KixpxL0u+QrzKfzKUWDeu8Kt4BP5vAYcHFl0l9vSaN1YlhBVARLozHN4PGM
3T5J+gjYv+vEWJxwu3ROPFArc/l0FaS5hmfOJJAIOcMI5m4q8TYsMJecvHrw+Hev
qQ==
-----END CERTIFICATE-----',
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        ]);
        return 'Connection successful';
    } catch (PDOException $e) {
        return 'Connection failed: ' . $e->getMessage();
    }
});
