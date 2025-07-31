<?php

use function Livewire\Volt\{state, mount, layout};

layout('components.layouts.company');

state([
    'members' => []
]);

mount(function() {
    $company = auth()->user()->currentCompanyFromRequest() ?? auth()->user()->currentCompany();

    if ($company) {
        $this->members = $company->users()->withPivot('role')->get();
    }
});

$updateRole = function($userId, $role) {
    $company = auth()->user()->currentCompanyFromRequest() ?? auth()->user()->currentCompany();

    if ($company && in_array($role, ['admin', 'member'])) {
        $company->users()->updateExistingPivot($userId, ['role' => $role]);
        $this->members = $company->users()->withPivot('role')->get();
    }
};

$removeMember = function($userId) {
    $company = auth()->user()->currentCompanyFromRequest() ?? auth()->user()->currentCompany();

    if ($company) {
        $company->users()->detach($userId);
        $this->members = $company->users()->withPivot('role')->get();
    }
};

?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-company-settings.layout :heading="__('Company Members')" :subheading="__('Manage team members and their roles')">
        <div class="space-y-6">
            @if(count($members) > 0)
                <div class="bg-white dark:bg-gray-800 shadow overflow-hidden sm:rounded-md">
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($members as $member)
                            <li class="px-6 py-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {{ substr($member->name, 0, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $member->name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $member->email }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <select
                                            wire:change="updateRole({{ $member->id }}, $event.target.value)"
                                            class="text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md focus:ring-blue-500 focus:border-blue-500"
                                        >
                                            <option value="admin" @if($member->pivot->role === 'admin') selected @endif>Admin</option>
                                            <option value="member" @if($member->pivot->role === 'member') selected @endif>Member</option>
                                        </select>

                                        <button
                                            wire:click="removeMember({{ $member->id }})"
                                            onclick="return confirm('Are you sure you want to remove this member from the company?')"
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:bg-red-900 dark:text-red-200 dark:hover:bg-red-800"
                                        >
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="mx-auto h-12 w-12 text-gray-400">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No members</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Get started by inviting team members to your company.
                    </p>
                </div>
            @endif

            <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
                <button
                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150"
                >
                    Invite New Member
                </button>
            </div>
        </div>
    </x-company-settings.layout>
</section>
