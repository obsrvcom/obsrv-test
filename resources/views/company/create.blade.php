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

        <form method="POST" action="{{ route('company.store') }}" class="flex flex-col gap-6"
              x-data="{
                  companyName: '{{ old('name') }}',
                  subdomain: '{{ old('subdomain') }}',
                  availabilityStatus: null,
                  availabilityMessage: '',
                  manuallyEdited: {{ old('subdomain') ? 'true' : 'false' }},
                  availabilityTimeout: null,

                                    updateSubdomain() {
                      if (!this.companyName) return;

                      // Only auto-suggest if subdomain is empty or hasn't been manually edited
                      if (this.subdomain && this.manuallyEdited) return;

                      // Convert company name to subdomain format (no hyphens)
                      this.subdomain = this.companyName
                          .toLowerCase()
                          .replace(/[^a-z0-9\\s]/g, '') // Remove special characters except spaces
                          .replace(/\\s+/g, ''); // Remove all spaces

                      this.checkAvailability();
                  },

                  checkAvailability() {
                      if (!this.subdomain) {
                          this.availabilityStatus = null;
                          this.availabilityMessage = '';
                          return;
                      }

                      // Mark as manually edited if user changes subdomain
                      if (this.subdomain !== this.companyName.toLowerCase().replace(/[^a-z0-9\\s]/g, '').replace(/\\s+/g, '')) {
                          this.manuallyEdited = true;
                      }

                      // Clear previous timeout
                      clearTimeout(this.availabilityTimeout);

                      this.availabilityStatus = 'checking';
                      this.availabilityMessage = 'Checking availability...';

                      // Set a new timeout to avoid too many requests
                      this.availabilityTimeout = setTimeout(() => {
                          fetch(`/api/v1/check-subdomain?subdomain=${encodeURIComponent(this.subdomain)}`)
                              .then(response => response.json())
                              .then(data => {
                                  if (data.available) {
                                      this.availabilityStatus = 'available';
                                      this.availabilityMessage = '✓ Subdomain is available';
                                  } else {
                                      this.availabilityStatus = 'taken';
                                      this.availabilityMessage = '✗ Subdomain is already taken';
                                  }
                              })
                              .catch(error => {
                                  console.error('Error checking subdomain availability:', error);
                                  this.availabilityStatus = null;
                                  this.availabilityMessage = '';
                              });
                      }, 500); // Wait 500ms after user stops typing
                  }
              }"
              x-init="
                  if (companyName && !subdomain) {
                      updateSubdomain();
                  }
                  if (subdomain) {
                      checkAvailability();
                  }
              ">
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
                x-model="companyName"
                @input="updateSubdomain"
            />

            <!-- Subdomain -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Subdomain
                </label>
                <div class="flex rounded-md shadow-sm">
                    <input type="text"
                           name="subdomain"
                           x-model="subdomain"
                           @input="checkAvailability"
                           required
                           :class="{
                               'border-green-500 focus:border-green-500': availabilityStatus === 'available',
                               'border-red-500 focus:border-red-500': availabilityStatus === 'taken',
                               'border-gray-300 dark:border-gray-700 focus:border-blue-500': availabilityStatus === 'checking' || availabilityStatus === null
                           }"
                           class="flex-1 dark:bg-gray-900 dark:text-white rounded-l-md shadow-sm px-3 py-2">
                    <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 text-sm">
                        .{{ request()->getHost() }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Only lowercase letters and numbers allowed
                </p>
                <p x-show="availabilityStatus"
                   x-text="availabilityMessage"
                   :class="{
                       'text-green-600 dark:text-green-400': availabilityStatus === 'available',
                       'text-red-600 dark:text-red-400': availabilityStatus === 'taken',
                       'text-gray-600 dark:text-gray-400': availabilityStatus === 'checking'
                   }"
                   class="text-sm"></p>
                @error('subdomain')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="space-y-2">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Description (Optional)
                </label>
                <textarea name="description" id="description" rows="3"
                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end space-x-2">
                <flux:button
                    variant="outline"
                    href="{{ route('company.select') }}"
                >
                    ← Back to company selection
                </flux:button>
                <flux:button
                    variant="primary"
                    type="submit"
                >
                    Create Company
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts.auth>
