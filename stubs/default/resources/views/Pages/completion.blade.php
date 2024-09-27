@extends('Shared.layout')

@section('title')
    Completion Sample
@endsection

@section('content')

    <!-- Chat Messages Area -->
    <div class="bg-slate-700 flex-1 p-6 overflow-y-auto" id="completion-form-container">
        <div class="space-y-4">
            <div class="flex items-start justify-center">
                <div class="text-gray-300 w-3/4 text-center">
                    <p class="font-bold text-3xl mb-20">&#129302; Completion Sample</p>
                    <p>Type some text into the box below and click <strong class="font-bold">&#x23CE;</strong> to see the AI's response.</p>
                </div>
            </div>

            <div class="flex items-start justify-center">
                <div id="response-container" class="bg-gray-200 p-4 mt-20 rounded-lg w-3/4" style="display: none;">
                    <p class="text-gray-800 font-bold">Response</p>
                    <p id="response-text" class="text-gray-800"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Input Area -->
    <x-sidekick-form url="/sidekick/completion" />

@endsection

@prepend('page-scripts')
    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('sidekick-form');
            const messageInput = document.getElementById('prompt');
            const responseContainer = document.getElementById('response-container');
            const responseText = document.getElementById('response-text');

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                event.stopImmediatePropagation();
                event.stopPropagation();

                const message = messageInput.value;
                responseContainer.style.display = 'none';
                responseText.textContent = '';

                fetch('/sidekick/completion', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ prompt: message })
                })
                    .then(response => response.text())
                    .then(data => {
                        responseText.textContent = data;
                        responseContainer.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        responseText.textContent = 'An error occurred while processing the request.';
                        responseContainer.style.display = 'block';
                    });
            });
        });
    </script>
@endprepend


