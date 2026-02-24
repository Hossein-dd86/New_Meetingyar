<x-filament-panels::page>
    {{-- Page content --}}<div>
        {{ $this->form }}

        <button wire:click="sendSms" class="bg-blue-500 text-white px-4 py-2 mt-2 rounded">
            ارسال پیامک
        </button>
    </div>
</x-filament-panels::page>
