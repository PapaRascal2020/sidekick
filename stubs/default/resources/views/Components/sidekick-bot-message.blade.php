<div class="flex items-start justify-end">
    <div class="bg-blue-800 text-white p-4 rounded-lg w-3/4">
        <p class="font-bold flex items-center gap-x-1 pb-2">
            <svg class="h-5 w-5 text-white"  width="24" height="24" viewBox="0 0 24 24"
                 stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z"/>
                <path d="M4 8v-2a2 2 0 0 1 2 -2h2" />
                <path d="M4 16v2a2 2 0 0 0 2 2h2" />
                <path d="M16 4h2a2 2 0 0 1 2 2v2" />
                <path d="M16 20h2a2 2 0 0 0 2 -2v-2" />
                <line x1="9" y1="10" x2="9.01" y2="10" />
                <line x1="15" y1="10" x2="15.01" y2="10" />
                <path d="M9.5 15a3.5 3.5 0 0 0 5 0" />
            </svg> Assistant</p>
        <p id="response-{{ $r }}">{{ $slot }}</p>
    </div>
</div>
