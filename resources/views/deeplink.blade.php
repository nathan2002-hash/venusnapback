<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $post->album->name ?? 'Venusnap Post' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Open Graph Meta --}}
    <meta property="og:title" content="{{ $post->album->name ?? 'Venusnap Post' }}">
    <meta property="og:description" content="Check out this post from the '{{ $post->album->name ?? 'album' }}' album by {{ $post->user->name ?? 'a user' }} on Venusnap.">
    <meta property="og:image" content="{{ $post->media->full_url ?? asset('default.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="article">

    <meta name="description" content="Discover more from the album '{{ $post->album->name ?? 'Unknown Album' }}' on Venusnap.">

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
        Redirecting you to the Venusnap app...
        <br><br>
        If nothing happens, <a href="https://play.google.com/store/apps/details?id=com.venusnap.app">tap here to download the app</a>.
    </p>
</body>
</html>
