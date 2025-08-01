<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $post->album->name ?? 'Venusnap Post' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Open Graph Meta --}}
    @php
        $sharedBy = isset($share) ? $share->user->name : null;
        $description = $sharedBy
            ? "Check out this post shared by $sharedBy on Venusnap."
            : "Check out this post from the '".($post->album->name ?? 'album')."' on Venusnap.";
    @endphp

    <meta property="og:title" content="{{ $post->album->name ?? 'Venusnap Post' }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $media->full_url ?? asset('default.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="article">

    <meta name="description" content="{{ $description }}">

    {{-- Deeplink logic --}}
    <script>
        const deepLink = `venusnap://post/{{ $post->id }}/media/{{ $media->id }}?ref={{ request('ref') }}`;
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
            Post shared by {{ $share->user->name }}
        @else
            Post from {{ $post->album->name ?? 'an album' }}
        @endif
        <br><br>
        Redirecting you to the Venusnap app...
        <br><br>
        If nothing happens, <a href="https://play.google.com/store/apps/details?id=com.venusnap.app">tap here to download the app</a>.
    </p>
</body>
</html>
