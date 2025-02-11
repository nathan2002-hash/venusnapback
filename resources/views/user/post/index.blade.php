<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
   @foreach ($posts as $post)
    <div style="border: 2px solid red;">
        @foreach ($post->postmedias as $postmedia)
        <div style="border: 2px solid blue;">
            <img src="{{ config('filesystems.disks.public.url') }}/{{ $postmedia->file_path }}" width="150" height="150" alt="">
            <form action="/api/post/admire/{{ $postmedia->id }}" method="POST">
                @csrf
                <button type="submit">Admire</button>
            </form><br>
            <form action="/api/post/comment/{{ $postmedia->id }}" method="POST">
                @csrf
                <textarea name="comment" id="" cols="30" rows="2"></textarea>
                <button type="submit">comment</button>
            </form>
            @foreach ($postmedia->comments as $comment)
                <li>
                    {{ $comment->comment }}
                    <form action="/api/post/comment/reply/{{ $comment->id }}" method="POST">
                        @csrf
                        <button type="submit">reply</button>
                    </form><br>
                </li>
            @endforeach
        </div>
        @endforeach<br>
        <form action="/api/post/save/{{ $post->id }}" method="POST">
            @csrf
            <button type="submit">save</button>
        </form><br>

        <form action="/api/support/{{ $post->user->id }}" method="POST">
            @csrf
            <button type="submit">support</button>
        </form><br>
        <form action="/api/post/report/{{ $post->id }}" method="POST">
            @csrf
            <textarea name="report" id="" cols="30" rows="2"></textarea>
            <button type="submit">Report</button>
        </form>
    </div><br>
   @endforeach

<h1>create</h1>
<form action="/api/post/store" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="text" name="description" placeholder="description"><br><br>
    <select name="visibility" id="">
        <option selected value="on">on</option>
        <option value="off">off</option>
    </select><br><br>
    <select name="type" id="">
        <option selected value="single">single</option>
        <option value="movie">movie</option>
    </select><br><br>
    <input type="file" name="file_path"><br><br>
    <button type="submit">post it</button>
</form>
</body>
</html>
