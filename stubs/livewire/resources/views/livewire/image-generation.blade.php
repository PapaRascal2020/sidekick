<div class="bg-slate-700 flex-1 flex flex-col">
    <div class="bg-slate-700 flex-1 p-6 overflow-y-auto">
        <div class="space-y-4">
            <div class="flex items-start justify-center">
                <div class="text-gray-300 w-3/4 text-center">
                    <p class="font-bold text-3xl mb-20">&#129302; Image Generation Sample</p>
                    <p>Describe the image you want in the text box below and click <b class="font-bold">&#x23CE;</b> to generate an image.</p>
                    @if(isset($image))
                        <img src="{!! $image !!}" alt="generated image" class="w-full pt-20"/>

                        <div class="m-auto text-center w-1/3 pt-4">
                            <a href="{{ $savedFile }}"
                               class="bg-blue-600 text-white px-4 py-2 ml-2 rounded-md hover:bg-blue-700"
                               download>
                                Download File
                            </a>
                        </div>
                    @endif
                    @if($errors)
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-20"
                             role="alert">
                            <strong class="font-bold">Error!</strong>
                            <span class="block sm:inline">{{ $errors }}</span>
                        </div>
                    @endif
                    <div wire:loading>
                        <div class="flex items-center justify-center bg-slate-700 text-center text-gray-400 mt-20">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <div>Generating Image...</div>
                        </div>
                    </div>
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
