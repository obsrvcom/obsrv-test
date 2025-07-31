@extends('components.layouts.site')

@section('content')
<div class="max-w-3xl mx-auto py-10">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-8">
        <h1 class="text-2xl font-bold mb-4">{{ $site->name }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-2"><strong>Address:</strong> {{ $site->address }}</p>
        <p class="text-gray-500 text-xs">Site ID: {{ $site->id }}</p>
    </div>
</div>
@endsection
