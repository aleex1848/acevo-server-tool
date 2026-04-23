@props([
    'serverConfigJson' => '',
    'seasonDefinitionJson' => '',
    'serverConfigB64' => '',
    'seasonDefinitionB64' => '',
])

<div
    x-data="{
        tab: 'serverconfig',
        copy(value, event) {
            const btn = event.currentTarget;
            const original = btn.innerText;
            navigator.clipboard.writeText(value).then(() => {
                btn.innerText = 'Copied!';
                setTimeout(() => { btn.innerText = original; }, 1500);
            });
        },
    }"
    class="space-y-4"
>
    <div class="space-y-2">
        <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
            Ansicht
        </p>
        <div
            role="tablist"
            aria-label="Configuration preview tabs"
            class="inline-flex flex-wrap gap-2 rounded-xl border border-gray-200 bg-gray-50 p-2 dark:border-white/10 dark:bg-white/5"
        >
        <button
            type="button"
            @click="tab = 'serverconfig'"
            :class="tab === 'serverconfig'
                ? 'bg-primary-600 text-white shadow-sm'
                : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/10'"
            class="cursor-pointer rounded-lg px-3 py-2 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/60"
        >
            serverconfig.json
        </button>
        <button
            type="button"
            @click="tab = 'season'"
            :class="tab === 'season'
                ? 'bg-primary-600 text-white shadow-sm'
                : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/10'"
            class="cursor-pointer rounded-lg px-3 py-2 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/60"
        >
            seasondefinition.json
        </button>
        <button
            type="button"
            @click="tab = 'payload'"
            :class="tab === 'payload'
                ? 'bg-primary-600 text-white shadow-sm'
                : 'bg-white text-gray-700 hover:bg-gray-100 dark:bg-transparent dark:text-gray-300 dark:hover:bg-white/10'"
            class="cursor-pointer rounded-lg px-3 py-2 text-sm font-medium transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/60"
        >
            Base64 Payload
        </button>
        </div>
    </div>

    <div x-show="tab === 'serverconfig'" x-cloak>
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-gray-500 dark:text-gray-400">Server configuration JSON</span>
            <button
                type="button"
                @click="copy(@js($serverConfigJson), $event)"
                class="inline-flex items-center gap-1 rounded-md bg-primary-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-primary-500"
            >
                Copy JSON
            </button>
        </div>
        <pre class="max-h-[60vh] overflow-auto rounded-lg bg-gray-900 p-4 text-xs text-gray-100"><code>{{ $serverConfigJson }}</code></pre>
    </div>

    <div x-show="tab === 'season'" x-cloak>
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-gray-500 dark:text-gray-400">Season definition JSON</span>
            <button
                type="button"
                @click="copy(@js($seasonDefinitionJson), $event)"
                class="inline-flex items-center gap-1 rounded-md bg-primary-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-primary-500"
            >
                Copy JSON
            </button>
        </div>
        <pre class="max-h-[60vh] overflow-auto rounded-lg bg-gray-900 p-4 text-xs text-gray-100"><code>{{ $seasonDefinitionJson }}</code></pre>
    </div>

    <div x-show="tab === 'payload'" x-cloak class="space-y-4">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">serverconfig (base64)</span>
                <button
                    type="button"
                    @click="copy(@js($serverConfigB64), $event)"
                    class="inline-flex items-center gap-1 rounded-md bg-primary-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-primary-500"
                >
                    Copy
                </button>
            </div>
            <textarea
                readonly
                rows="5"
                class="w-full rounded-lg border border-gray-200 bg-gray-50 p-3 font-mono text-xs text-gray-800 dark:border-white/10 dark:bg-gray-900 dark:text-gray-100"
            >{{ $serverConfigB64 }}</textarea>
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">seasondefinition (base64)</span>
                <button
                    type="button"
                    @click="copy(@js($seasonDefinitionB64), $event)"
                    class="inline-flex items-center gap-1 rounded-md bg-primary-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-primary-500"
                >
                    Copy
                </button>
            </div>
            <textarea
                readonly
                rows="5"
                class="w-full rounded-lg border border-gray-200 bg-gray-50 p-3 font-mono text-xs text-gray-800 dark:border-white/10 dark:bg-gray-900 dark:text-gray-100"
            >{{ $seasonDefinitionB64 }}</textarea>
        </div>

        @php
            $command = './acServer --server-config='.$serverConfigB64.' --season-definition='.$seasonDefinitionB64;
        @endphp

        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Launch command</span>
                <button
                    type="button"
                    @click="copy(@js($command), $event)"
                    class="inline-flex items-center gap-1 rounded-md bg-primary-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-primary-500"
                >
                    Copy command
                </button>
            </div>
            <textarea
                readonly
                rows="4"
                class="w-full rounded-lg border border-gray-200 bg-gray-50 p-3 font-mono text-xs text-gray-800 dark:border-white/10 dark:bg-gray-900 dark:text-gray-100"
            >{{ $command }}</textarea>
        </div>
    </div>
</div>
