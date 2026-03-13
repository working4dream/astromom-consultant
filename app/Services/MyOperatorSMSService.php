<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class MyOperatorSMSService
{
    protected $apiKey;
    protected $senderId;
    protected $url;

    public function __construct()
    {
        $this->apiKey = env('MYOPERATOR_API_KEY');
        $this->senderId = env('MYOPERATOR_SENDER_ID');
        $this->url = env('MYOPERATOR_URL');
        $this->templateId = env('MYOPERATOR_TEMPLATE_ID');
    }

    public function sendSMS($phoneNumber, $message)
    {
        $response = Http::get($this->url, [
            'apikey' => $this->apiKey,
            'senderid' => $this->senderId,
            'number' => $phoneNumber,
            'message' => $message,
            'format' => 'json'
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return ['error' => 'Failed to send SMS'];
    }
}
