<x-filament::page>
    <div class="flex flex-col h-[500px] border rounded-lg">
        <!-- الرسائل -->
        <div class="flex-1 overflow-y-auto p-4 space-y-2">
            @foreach($messages as $msg)
                <div class="@if($msg['sender_id'] == auth()->id()) text-right @else text-left @endif">
                    <div class="inline-block px-4 py-2 rounded-lg
                        @if($msg['sender_id'] == auth()->id()) bg-blue-500 text-white @else bg-gray-200 @endif">
                        {{ $msg['message'] }}
                    </div>
                </div>
            @endforeach
        </div>

        <!-- إدخال رسالة -->
        <div class="p-4 border-t flex space-x-2">
            <input wire:model="newMessage" wire:keydown.enter="sendMessage"
                   class="flex-1 border rounded px-3 py-2" placeholder="اكتب رسالتك...">
            <button wire:click="sendMessage" class="bg-blue-500 text-white px-4 py-2 rounded">إرسال</button>
        </div>
    </div>
</x-filament::page>
