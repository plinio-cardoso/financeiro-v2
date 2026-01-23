<div x-data="{
    notifications: [],
    add(message, type = 'success') {
        const id = Date.now();
        this.notifications.push({ id, message, type });
        setTimeout(() => this.remove(id), 5000);
    },
    remove(id) {
        const notification = this.notifications.find(n => n.id === id);
        if (notification) {
            notification.visible = false; 
            setTimeout(() => {
                this.notifications = this.notifications.filter(n => n.id !== id);
            }, 300); // Wait for transition
        }
    },
    init() {
        // Listen for Livewire events
        window.addEventListener('notify', event => {
            const detail = Array.isArray(event.detail) ? event.detail[0] : event.detail;
            this.add(detail.message, detail.type || 'success');
        });

        // Check for session flash messages
        @if (session()->has('success'))
            this.add('{{ session('success') }}', 'success');
        @endif
        @if (session()->has('error'))
            this.add('{{ session('error') }}', 'error');
        @endif
        @if (session()->has('flash.banner'))
            this.add('{{ session('flash.banner') }}', '{{ session('flash.bannerStyle') == 'danger' ? 'error' : 'success' }}');
        @endif
    }
}"
    class="fixed bottom-4 left-1/2 -translate-x-1/2 sm:top-8 sm:right-8 sm:bottom-auto sm:left-auto sm:translate-x-0 z-50 space-y-4 w-full max-w-xs pointer-events-none px-4 sm:px-0">

    <template x-for="notification in notifications" :key="notification.id">
        <div x-show="true" x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-2xl shadow-2xl border border-gray-100 dark:border-white/10 backdrop-blur-md"
            :class="{
                'bg-white/95 dark:bg-gray-800/95': true
             }">
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <template x-if="notification.type === 'success'">
                            <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                        <template x-if="notification.type === 'error'">
                            <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                        </template>
                        <template x-if="notification.type === 'info'">
                            <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                        </template>
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="notification.message">
                        </p>
                    </div>
                    <div class="ml-4 flex flex-shrink-0">
                        <button @click="remove(notification.id)" type="button"
                            class="inline-flex rounded-md bg-white dark:bg-gray-800 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>