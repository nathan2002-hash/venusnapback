<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
   @foreach ($saveds as $saved)
    <div>
        @foreach ($saved->post->postmedias as $postmedia)
        <img src="{{ config('filesystems.disks.public.url') }}/{{ $postmedia->file_path }}" width="150" height="150" alt="">
        @endforeach
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
