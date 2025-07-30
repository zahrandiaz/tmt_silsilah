<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard Admin') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-6 rounded-lg shadow">
                            <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Total Individu</h3>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $totalPeople }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-6 rounded-lg shadow">
                            <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Total Pengguna</h3>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $totalUsers }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-6 rounded-lg shadow">
                            <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Total Keluarga</h3>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $totalFamilies }}</p>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-6 rounded-lg shadow">
                            <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Total Generasi</h3>
                            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $maxGeneration }}</p>
                        </div>
                    </div>

                    <h3 class="text-xl font-semibold mb-4 border-b border-gray-200 dark:border-gray-700 pb-2">Laporan Kualitas Data</h3>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg flex flex-col">
                            <h4 class="font-bold text-yellow-800 dark:text-yellow-300">Individu tanpa Tgl. Lahir ({{ $totalPeopleWithoutBirthDate }})</h4>
                            <ul class="mt-3 space-y-2 flex-grow">
                                @forelse ($peopleWithoutBirthDate as $person)
                                    <li class="text-sm">
                                        <a href="{{ route('people.edit', $person) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                            {{ $person->name }}
                                        </a>
                                    </li>
                                @empty
                                    <li class="text-sm text-gray-500 dark:text-gray-400">✅ Semua data lengkap.</li>
                                @endforelse
                            </ul>
                            @if ($totalPeopleWithoutBirthDate > $peopleWithoutBirthDate->count())
                                <div class="mt-4 text-right">
                                    <a href="{{ route('reports.people.no_birth_date') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                        Lihat Semua &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="bg-orange-50 dark:bg-orange-900/20 p-4 rounded-lg flex flex-col">
                            <h4 class="font-bold text-orange-800 dark:text-orange-300">Individu tanpa Orang Tua ({{ $totalPeopleWithoutParents }})</h4>
                             <ul class="mt-3 space-y-2 flex-grow">
                                @forelse ($peopleWithoutParents as $person)
                                    <li class="text-sm">
                                        <a href="{{ route('people.edit', $person) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                            {{ $person->name }}
                                        </a>
                                    </li>
                                @empty
                                    <li class="text-sm text-gray-500 dark:text-gray-400">✅ Semua data terhubung.</li>
                                @endforelse
                            </ul>
                             @if ($totalPeopleWithoutParents > $peopleWithoutParents->count())
                                <div class="mt-4 text-right">
                                    <a href="{{ route('reports.people.no_parents') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                        Lihat Semua &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>

                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg flex flex-col">
                            <h4 class="font-bold text-blue-800 dark:text-blue-300">Keluarga tanpa Anak ({{ $totalFamiliesWithoutChildren }})</h4>
                            <ul class="mt-3 space-y-2 flex-grow">
                                @forelse ($familiesWithoutChildren as $familyId => $members)
                                    <li class="text-sm">
                                        @foreach ($members as $member)
                                            <a href="{{ route('people.show', $member->person_id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                                {{ $member->person->name }}
                                            </a>
                                            @if (!$loop->last)
                                                &
                                            @endif
                                        @endforeach
                                    </li>
                                @empty
                                    <li class="text-sm text-gray-500 dark:text-gray-400">✅ Semua keluarga punya anak.</li>
                                @endforelse
                            </ul>
                            @if ($totalFamiliesWithoutChildren > $familiesWithoutChildren->count())
                                <div class="mt-4 text-right">
                                    <a href="{{ route('reports.families.no_children') }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                        Lihat Semua &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>