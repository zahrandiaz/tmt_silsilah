<footer class="bg-white dark:bg-gray-800 mt-12 py-6 border-t border-gray-200 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-500 dark:text-gray-400">
        <div class="flex justify-center items-center space-x-4 mb-2">
            <a href="{{ route('pages.about') }}" class="hover:underline">Tentang</a>
            <span class="text-gray-400">|</span>
            <a href="{{ route('pages.donation') }}" class="hover:underline">Donasi</a>
            <span class="text-gray-400">|</span>
            <a href="{{ route('login') }}" class="hover:underline">Login Area</a>
        </div>
        <p>&copy; {{ date('Y') }} {{ config('app.name', 'TMT Silsilah') }}. All rights reserved.</p>
        <p class="mt-2">
            <span>Versi {{ config('app.version', '0.10.0') }}</span>
        </p>
    </div>
</footer>