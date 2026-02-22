<?php

use App\Models\User;
use App\Services\CachingService;
use Google\Client;
use Illuminate\Support\Facades\Http;

if (!function_exists('send_whatsapp_notification')) {

    function send_whatsapp_notification($numbers, string $message, ?string $fileUrl = null, array $extra = [])
    {
        $numbers = collect($numbers)
            ->flatten()
            ->map(fn ($n) => trim((string) $n))
            ->filter()
            ->unique()
            ->values();

        // dd($numbers);

        foreach ($numbers as $number) {

            if (is_array($number)) {
                $number = reset($number); // extract value
            }

            $number = trim((string) $number);

            $payload = array_merge([
                'number' => '91' . $number,
                // 'number'  => '917488699325',
                'caption' => $message,
            ], $extra);

            if (!empty($fileUrl) && filter_var($fileUrl, FILTER_VALIDATE_URL)) {
                $payload['file'] = $fileUrl;
            }

            try {
                // dd($payload);
                $response = Http::post('http://168.231.123.203:3000/send-media', $payload);

                $responseBody = $response->json();

                $status  = ($response->successful() && ($responseBody['success'] ?? false))
                            ? 'success'
                            : 'failed';

                $errorMessage = $responseBody['message'] ?? $response->body();

                DB::table('whatsapp_logs')->insert([
                    'number'     => $number,
                    'message'    => $message,
                    'file_url'   => $fileUrl,
                    'status'     => $status,
                    'response'   => $errorMessage,
                    'created_at'=> now(),
                ]);

            } catch (\Throwable $e) {

                DB::table('whatsapp_logs')->insert([
                    'number'   => $number,
                    'message'  => $message,
                    'file_url' => $fileUrl,
                    'status'   => 'failed',
                    'response' => $e->getMessage(),
                    'created_at' => now(),
                ]);

                \Log::error('WhatsApp send failed', [
                    'number' => $number,
                    'error'  => $e->getMessage()
                ]);
            }
        }
    }
}

// Http::post('http://168.231.123.203:3000/send-media', $payload);
function send_notification($user, $title, $body, $type, $customData = []) {
    $FcmTokens = User::where('fcm_id', '!=', '')->whereIn('id', $user)->get()->pluck('fcm_id');

    $cache = app(CachingService::class);


    $project_id = $cache->getSystemSettings('firebase_project_id');
    $url = 'https://fcm.googleapis.com/v1/projects/' . $project_id . '/messages:send';

    $access_token = getAccessToken();

    foreach ($FcmTokens as $FcmToken) {

        $data = [
            "message" => [
                "token" => $FcmToken,
                "notification" => [
                    "title" => $title,
                    "body" => $body
                ],
                "data" => [
                    "title" => $title,
                    "body" => $body,
                    "type" => $type,
                    ...$customData
                ],
                "android" => [
                    "notification"=> [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        "sound" => "default"  // This is for Android sound
                    ],
                    "priority" => "high"

                ],
                "apns" => [
                    "headers" => [
                        "apns-priority" => "10" // Set APNs priority to 10 (high) for immediate delivery
                    ],
                    "payload" => [
                        "aps" => [
                            "alert" => [
                                "title" => $title,
                                "body" => $body,
                            ],
                            "type" => $type,
                            ...$customData,
                            "sound" => "default",  // This is for iOS sound
                            "mutable-content" => 1,
                            "content-available" => 1
                        ]
                    ]
                ]
            ]
        ];

        $encodedData = json_encode($data);

        $headers = [
            'Authorization: Bearer ' . $access_token,
            'Content-Type: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);

        // Execute post
        $result = curl_exec($ch);
        // dd($result);
        if ($result == FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        // Close connection
        curl_close($ch);
    }
}

function getAccessToken()
{
    $cache = app(CachingService::class);

    $file_name = $cache->getSystemSettings('firebase_service_file');
    $data = explode("storage/", $file_name ?? '');
    $file_name = end($data);

    $file_path = base_path('public/storage/'. $file_name);

    $client = new Client();
    $client->setAuthConfig($file_path);
    $client->setScopes(['https://www.googleapis.com/auth/firebase.messaging']);
    $accessToken = $client->fetchAccessTokenWithAssertion()['access_token'];

    return $accessToken;
}
