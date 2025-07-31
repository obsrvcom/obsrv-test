<x-layouts.auth :title="__('Create Company')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create Company')" :description="__('Set up a new company workspace')" />

        @if(session('error'))
            <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <p class="text-sm text-red-800 dark:text-red-200">
                    {{ session('error') }}
                </p>
            </div>
        @endif

        <form method="POST" action="{{ route('company.store') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Company Name -->
            <flux:input
                name="name"
                :label="__('Company Name')"
                type="text"
                required
                autofocus
                :value="old('name')"
                placeholder="Acme Corporation"
            />
            <!-- Description -->
            <flux:input
                name="description"
                :label="__('Description')"
                type="text"
                :value="old('description')"
                placeholder="Optional description"
            />
            <button type="submit" class="btn btn-primary">
                {{ __('Create Company') }}
            </button>
        </form>
    </div>
</x-layouts.auth>
