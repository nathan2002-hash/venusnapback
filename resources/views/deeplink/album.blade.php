<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $album->name ?? 'Venusnap Album' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Open Graph Meta --}}
   @php
        $ownerName = $album->user->name ?? 'Venusnap';
        $description = "Discover {$ownerName}'s latest snaps in the '{$album->name}' album on Venusnap.";
    @endphp


    <meta property="og:title" content="{{ $album->name ?? 'Venusnap Album' }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $thumbnailUrl ?? asset('default.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    <meta name="description" content="{{ $description }}">

    {{-- Deeplink logic --}}
    <script>
        const deepLink = `venusnap://album/{{ $album->id }}?ref={{ request('ref') }}`;
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
            Album shared by {{ $share->user->name }}
        @else
            Album "{{ $album->name ?? 'Untitled' }}"
        @endif
        <br><br>
        Redirecting you to the Venusnap app...
        <br><br>
        If nothing happens, <a href="https://play.google.com/store/apps/details?id=com.venusnap.app">tap here to download the app</a>.
    </p>
</body>
</html>
