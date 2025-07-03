<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Opening Venusnap...</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Attempt to open the app via deeplink --}}
    <script>
        const postId = "{{ $post }}";
        const mediaId = "{{ $media }}";
        const ref = "{{ request('ref') }}";

        const deepLink = `venusnap://post/${postId}/media/${mediaId}?ref=${ref}`;
        const fallbackUrl = "https://play.google.com/store/apps/details?id=com.venusnap.app"; // change if iOS

        // Attempt to open app
        window.location = deepLink;

        // If user doesn't have app, fallback after delay
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
