<!DOCTYPE html>
<html>
<head>
    <title>Sidekick Chatroom</title>
    <style type="text/css">
        .chatWindow {
            border: 1px solid #fff;
            height: 600px;
            width: 500px;
            overflow-y: scroll;
            text-align: left;
            margin: auto;
            margin-bottom: 20px;
        }

        .chatWindow .user {
            padding: 10px;
            background: #000;
        }

        .chatWindow .assistant {
            padding: 10px;
            background: #222;
        }

        .chatWindow .assistant h5 {
            text-transform: capitalize;
        }

        .chatWindow .user h5{
            text-transform: capitalize;
        }
    </style>
</head>
<body style="padding: 40px; text-align: center; background: #000; color: #fff; font-family: arial, sans-serif;">
<a href="/sidekick/playground" style="border: 0;">
    <img src="https://substackcdn.com/image/fetch/w_1456,c_limit,f_webp,q_auto:good,fl_progressive:steep/https%3A%2F%2Fsubstack-post-media.s3.amazonaws.com%2Fpublic%2Fimages%2F0e3449a7-44de-4ce2-b384-cea763c0901e_500x500.heic" alt="Sidekick Robot" width="100" />
</a>
<h1>Talk with Sidekick</h1>
<h3>Conversation (id: {{$response['conversation_id']}})</h3>
    <div class="chatWindow" id="scrollableDiv">
        @foreach($response['messages'] as $message)
            <div class="{{ $message['role'] }}">
                <h5>{{ $message['role'] }}</h5>
                <p>{{ $message['content'] }}</p>
            </div>
        @endforeach
        <div style="padding: 10px; border-radius: 15px;">
            <form method="POST" action="/sidekick/playground/chat/update">
                @csrf
                <input type="hidden" name="conversation_id" value="{{$response['conversation_id']}}" />
                <textarea style="width: 100%; height: 60px; background: #888" placeholder="Reply..." name="message" required></textarea>
                <br>
                <input style="width: 100%; height: 20px;" type="submit" value="Send Response" />
            </form>
        </div>
    </div>

    <script>

        function scrollToBottom() {
            const container = document.getElementById('scrollableDiv');
            container.scrollTop = container.scrollHeight;
        }

        // Scroll to bottom when the page loads
        window.onload = scrollToBottom;
    </script>


</body>
</html>

