<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AstroMoM</title>
</head>
<body>
    <h1>{{ $deepLink }} is not available for web.</h1>
    <p>Please download the app from:</p>
    <p>
        <a href="{{ $androidUrl }}"><img src="{{ asset('android_icon.png') }}" alt="Play Store"></a>
        or 
        <a href="{{ $iphoneUrl }}"><img src="{{ asset('iphone_icon.png') }}" alt="App Store"></a>
    </p>
</body>
</html>
