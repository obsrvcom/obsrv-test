@extends('components.layouts.app')

@section('content')
<div class="max-w-3xl mx-auto py-10">
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-8">
        <h1 class="text-2xl font-bold mb-4">{{ $company->name }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-2"><strong>Description:</strong> {{ $company->description }}</p>
        <p class="text-gray-500 text-xs">Company ID: {{ $company->id }}</p>
    </div>
</div>
@endsection
