<footer class="bg-white dark:bg-gray-800 mt-12 py-6 border-t border-gray-200 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-500 dark:text-gray-400">
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'TMT Silsilah') }}. All rights reserved.</p>
        <p class="mt-2">
            <a href="{{ route('login') }}" class="hover:underline">Login Area</a>
            <span class="mx-2">|</span>
            <span>Versi {{ config('app.version', '0.10.0') }}</span>
        </p>
    </div>
</footer>