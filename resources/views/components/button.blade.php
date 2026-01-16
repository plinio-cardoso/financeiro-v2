<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-primary-50 hover:bg-primary-100 border border-transparent rounded-md font-semibold text-xs text-primary uppercase tracking-widest focus:bg-primary-100 active:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 dark:focus:ring-offset-gray-900 disabled:opacity-50 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
