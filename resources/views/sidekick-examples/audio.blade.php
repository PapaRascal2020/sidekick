<!DOCTYPE html>
<html>
<head>
    <title>Sidekick Audio Example</title>
</head>
<body style="padding: 40px; text-align: center; background: #000; color: #fff; font-family: arial, sans-serif;">
<a href="/sidekick/playground" style="border: 0;">
    <img src="https://substackcdn.com/image/fetch/w_1456,c_limit,f_webp,q_auto:good,fl_progressive:steep/https%3A%2F%2Fsubstack-post-media.s3.amazonaws.com%2Fpublic%2Fimages%2F0e3449a7-44de-4ce2-b384-cea763c0901e_500x500.heic" alt="Sidekick Robot" width="100" />
</a>
<h1>Audio Generation Example</h1>
<form method="POST" action="/sidekick/playground/audio">
    @csrf
    <textarea style="width: 300px; height: 100px;" placeholder="Type the text you want to convert to audio..." name="text_to_convert" required></textarea>
    <br>
    <input style="width: 300px; height: 50px;" type="submit" value="Convert to Audio" />
</form>

@if(isset($audio))
    <audio id="audioPlayer" controls style="padding-top:20px; width:300px;">
        <source src="data:audio/mpeg;base64,{!! $audio !!}" />
    </audio>
@endif
</body>
</html>
