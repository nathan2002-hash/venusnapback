<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
   @foreach ($artworks as $artwork)
    <div>
        <img src="{{ config('filesystems.disks.public.url') }}/{{ $artwork->file_path }}" width="150" height="150" alt="">
    </div><br>
   @endforeach
<h1>create</h1>
<form action="/api/artwork/store" method="POST" enctype="multipart/form-data">
    @csrf
    <textarea name="content" id="" cols="23" rows="5" placeholder="content"></textarea><br><br>
    <select name="color_text" id="">
        <option selected value="red">red</option>
        <option value="white">white</option>
        <option value="black">black</option>
        <option value="blue">blue</option>
    </select><br><br>
    <select name="color_background" id="">
        <option selected value="red">yellow</option>
        <option value="blue">blue</option>
        <option value="green">green</option>
        <option value="red">red</option>
    </select><br><br>
    <input type="file" name="file_path"><br><br>
    <button type="submit">post it</button>
</form>
</body>
</html>
