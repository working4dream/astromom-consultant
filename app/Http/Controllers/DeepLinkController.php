<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeepLinkController extends Controller
{
    public function handleDeepLink(Request $request)
    {
        $userAgent = $request->header('User-Agent');
        $deepLink = $request->query('number');
        $deviceMappings = [
            'Android' => 'https://play.google.com/store/apps/details?id=in.subastro.app',
            'iPhone'  => 'https://google.com',
        ];

        $defaultRedirectUrl = url('/pmp');
        $deviceType = 'Other';

        foreach ($deviceMappings as $type => $url) {
            if (stripos($userAgent, $type) !== false) {
                $deviceType = $type;
                break;
            }
        }

        $redirectUrl = $deviceMappings[$deviceType] ?? $defaultRedirectUrl;
        if ($deviceType === 'Other') {
            return response()->view('deep-link-message', [
                'androidUrl' => $deviceMappings['Android'], 
                'iphoneUrl' => $deviceMappings['iPhone'],
                'deepLink' => $deepLink,
            ]);
        }

        return redirect()->to($redirectUrl);
    }

}
