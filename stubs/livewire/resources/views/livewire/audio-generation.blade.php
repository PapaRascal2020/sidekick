<div class="bg-slate-700 flex-1 flex flex-col">
    <div class="bg-slate-700 flex-1 p-6 overflow-y-auto">
        <div class="space-y-4">
            <div class="flex items-start justify-center">
                <div class="text-gray-300 w-3/4 text-center">
                    <p class="font-bold text-3xl mb-20">&#129302; Audio Generation Sample</p>
                    <p>Enter text below and click <b class="font-bold">Generate</b> to create an audio file.</p>

                    @if($errors)
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-20"
                             role="alert">
                            <strong class="font-bold">Error!</strong>
                            <span class="block sm:inline">{{ $errors }}</span>
                        </div>
                    @endif

                    @if($audio)
                        <div class="m-auto text-center w-1/3 pt-4">
                            <audio id="audioPlayer" controls>
                                <source src="data:audio/mpeg;base64,{{ $audio }}" type="audio/mpeg" />
                            </audio>
                        </div>

                        <div class="m-auto text-center w-1/3 pt-4">
                           <a href="{{ $savedFile }}"
                              class="bg-blue-600 text-white px-4 py-2 ml-2 rounded-md hover:bg-blue-700"
                              download>
                               Download File
                           </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-slate-900 p-4">
        <form wire:submit.prevent="submit">
            <div class="flex gap-4">
                @csrf
                <input type="text"
                       wire:model="prompt"
                       id="prompt"
                       name="prompt"
                       class="flex-1 text-black border border-gray-300 rounded-md p-2 focus:outline-none focus:border-blue-600"
                       placeholder="Type your message..."
                       required>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 ml-2 rounded-md hover:bg-blue-700">&#x23CE;</button>
            </div>
        </form>
    </footer>
</div>

