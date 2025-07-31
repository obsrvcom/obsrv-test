@props(['title', 'description' => null])

<div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ $title }}
            </h1>
            @if($description)
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    {{ $description }}
                </p>
            @endif
        </div>
        @if(isset($actions))
            <div class="flex items-center space-x-4">
                {{ $actions }}
            </div>
        @endif
    </div>
</div>
