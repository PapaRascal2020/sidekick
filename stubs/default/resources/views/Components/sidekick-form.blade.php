<footer class="bg-slate-900 p-4">
    <form method="POST" id="sidekick-form" action="{{ $url }}">
        <div class="flex gap-4">
            @csrf
            {{ $slot }}
            <input type="text"
                   id="prompt"
                   name="prompt"
                   class="flex-1 text-black border border-gray-300 rounded-md p-2 focus:outline-none focus:border-blue-600"
                   placeholder="Type your message..."
                   value="{{ $value  ?? ''}}"
                   required>
            <button class="bg-blue-600 text-white px-4 py-2 ml-2 rounded-md hover:bg-blue-700">&#x23CE;</button>
        </div>
    </form>
</footer>
