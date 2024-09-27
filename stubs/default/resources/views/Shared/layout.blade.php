
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-white">

<!-- Main Container -->
<div class="flex h-screen">

    <!-- Sidebar Section -->
    <aside class="w-64 p-6 border-r border-gray-700">
        <div class="flex items-center px-2 h-20 pb-10">
            <img src="https://substackcdn.com/image/fetch/w_1456,c_limit,f_webp,q_auto:good,fl_progressive:steep/https%3A%2F%2Fsubstack-post-media.s3.amazonaws.com%2Fpublic%2Fimages%2F0e3449a7-44de-4ce2-b384-cea763c0901e_500x500.heic" class="h-14 w-14" alt="Sidekick Robot" />
            <div class="ml-1">
                <p class="ml-1 text-2xl font-medium tracking-wide truncate text-gray-100 font-sans">Sidekick!</p>
                <div class="badge">
                    <span class="px-2 py-0.5 ml-auto text-xs font-medium tracking-wide text-blue-800 bg-blue-100 rounded-full">Playground</span>
                </div>
            </div>
        </div>
        <nav class="space-y-4">
            <a href="/sidekick/chat" class="block text-white hover:bg-gray-600 px-4 py-2 rounded">Chats</a>
            <a href="/sidekick/completion" class="block text-white hover:bg-gray-600 px-4 py-2 rounded">Completion</a>
            <a href="/sidekick/image" class="block text-white hover:bg-gray-600 px-4 py-2 rounded">Image Generation</a>
            <a href="/sidekick/audio" class="block text-white hover:bg-gray-600 px-4 py-2 rounded">Audio Generation</a>
            <a href="/sidekick/moderate" class="block text-white hover:bg-gray-600 px-4 py-2 rounded">Moderation</a>
            <a href="/sidekick/transcribe" class="block text-white hover:bg-gray-600 px-4 py-2 rounded">Transcription</a>
            <a href="/sidekick/embedding" class="block text-white hover:bg-gray-600 px-4 py-2 rounded">Embedding</a>
        </nav>
    </aside>

    <!-- Chat Area Section -->
    <main class="flex-1 flex flex-col">
        <!-- Header -->
        @yield('content')
    </main>
</div>

@stack('page-scripts')

</body>
</html>

