@props(['items' => []])

<div class="flex items-center space-x-6 px-6 py-3 border-b border-zinc-200 dark:border-zinc-700">
    @foreach($items as $item)
        <div class="flex items-center space-x-2">
            @if(isset($item['icon']))
                <flux:icon name="{{ $item['icon'] }}" class="h-4 w-4" />
            @endif
            <span class="text-sm font-medium">{{ $item['label'] }}</span>
        </div>
    @endforeach

    {{ $slot }}
</div>
