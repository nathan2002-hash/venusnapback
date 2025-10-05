<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $ad->adboard->name ?? 'Venusnap Ad' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Open Graph Meta --}}
    @php
        $sharedBy = isset($share) ? $share->user->name : null;
        $description = $sharedBy
            ? "Check out \"{$ad->adboard->name}\" on Venusnap"
            : "Check out this ad on Venusnap.";
    @endphp

    <meta property="og:title" content="{{ $ad->adboard->name ?? 'Venusnap Ad' }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $thumbnailUrl ?? asset('default.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="article">
     <link href="{{ asset('assets1/img/logo1.png') }}" rel="icon">
    <link href="{{ asset('assets1/img/logo1.png') }}" rel="apple-touch-icon">

    <meta name="description" content="{{ $description }}">

    {{-- Deeplink logic --}}
    <script>
        const deepLink = `venusnap://sponsored/{{ $share->short_code }}`;
        const fallbackUrl = "https://play.google.com/store/apps/details?id=com.venusnap.app";

        window.location = deepLink;

        setTimeout(() => {
            window.location.href = fallbackUrl;
        }, 2000);
    </script>
</head>
<body>
    <p style="text-align:center;margin-top:40px;font-family:sans-serif;">
        @if(isset($share))
            Ad shared
        @else
            Ad from {{ $ad->adboard->album->name ?? 'an album' }}
        @endif
        <br><br>
        Redirecting you to the Venusnap app...
        <br><br>
        If nothing happens, <a href="https://play.google.com/store/apps/details?id=com.venusnap.app">tap here to download the app</a>.
    </p>
</body>
</html>
