<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $album->name ?? 'Venusnap Album' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Open Graph Meta --}}
    @php
        $albumName = $album->name ?? 'Untitled Album';
        $type = $album->type ?? 'personal';
        $snapCount = $album->posts->sum(fn($post) => $post->postMedias->count());
        $roundedSnapCount = ($snapCount < 10) ? 'a few' : (floor($snapCount / 10) * 10) . '+';

        // Check if album name already contains the word "album"
        $includesAlbum = str_contains(strtolower($albumName), 'album');

        $ownerName = ($type === 'personal') ? ($album->user->name ?? 'Someone') : null;

        switch ($type) {
            case 'creator':
                $description = $includesAlbum
                    ? "Explore '{$albumName}' — {$roundedSnapCount} creative snaps and inspiration on Venusnap."
                    : "Explore the '{$albumName}' album — {$roundedSnapCount} creative snaps and inspiration on Venusnap.";
                break;

            case 'business':
                $description = $includesAlbum
                    ? "Discover '{$albumName}' — {$roundedSnapCount} product snaps and updates on Venusnap."
                    : "Discover '{$albumName}' album — {$roundedSnapCount} product snaps and updates on Venusnap.";
                break;

            case 'personal':
            default:
                $description = $includesAlbum
                    ? "Take a glimpse into {$ownerName}’s '{$albumName}' — {$roundedSnapCount} memories and meaningful snaps on Venusnap."
                    : "Take a glimpse into {$ownerName}’s album '{$albumName}' — {$roundedSnapCount} memories and meaningful snaps on Venusnap.";
                break;
        }
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
