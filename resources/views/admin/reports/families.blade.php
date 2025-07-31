<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4">
                        <a href="{{ route('dashboard') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                            &larr; Kembali ke Dashboard
                        </a>
                    </div>
                    
                    <!-- --- TAMBAHKAN FORM PENCARIAN DI SINI --- -->
                    <div class="mb-4">
                        <form action="{{ url()->current() }}" method="GET" class="flex items-center space-x-2">
                            <x-text-input type="text" name="search" placeholder="Cari berdasarkan nama pasangan..." class="w-full md:w-1/3" value="{{ request('search') }}" />
                            <x-primary-button type="submit">Cari</x-primary-button>
                            @if(request('search'))
                                <a href="{{ url()->current() }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Reset</a>
                            @endif
                        </form>
                    </div>
                    <!-- --------------------------------------- -->
                    
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Pasangan dalam Keluarga
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($paginatedFamilies as $familyId => $members)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        @foreach ($members as $member)
                                            {{ $member->person->name }}
                                            @if (!$loop->last)
                                                &
                                            @endif
                                        @endforeach
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if ($members->first())
                                            <a href="{{ route('people.show', $members->first()->person_id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Lihat</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                        @if(request('search'))
                                            Tidak ada keluarga yang cocok dengan pencarian "{{ request('search') }}".
                                        @else
                                            Tidak ada data untuk ditampilkan.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $paginatedFamilies->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>