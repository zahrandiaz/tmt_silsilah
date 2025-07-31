<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Ubah Pengguna:') }} {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    
                    <div x-data="{ role: '{{ old('role', $user->role) }}' }">
                        <form method="POST" action="{{ route('users.update', $user) }}">
                            @csrf
                            @method('PATCH')

                            <div class="space-y-6">
                                {{-- Dropdown Peran --}}
                                <div>
                                    <x-input-label for="role" :value="__('Peran')" />
                                    <select name="role" id="role" x-model="role" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                        <option value="admin" @selected(old('role', $user->role) == 'admin')>Admin</option>
                                        <option value="operator" @selected(old('role', $user->role) == 'operator')>Operator</option>
                                        <option value="user" @selected(old('role', $user->role) == 'user')>User</option>
                                    </select>
                                </div>

                                {{-- Form Pengaturan Operator --}}
                                <div x-show="role === 'operator'" x-transition class="p-4 border border-gray-200 dark:border-gray-700 rounded-md space-y-4">
                                    <h3 class="font-semibold text-lg">Pengaturan Hak Akses Operator</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Atur garis keturunan dan jangkauan generasi yang dapat dikelola oleh operator ini.</p>
                                    
                                    <div>
                                        <x-input-label for="operator_person_id" :value="__('Akar Garis Keturunan')" />
                                        <input type="text" name="operator_person_id" id="operator_person_id" placeholder="Ketik untuk mencari nama...">
                                        <x-input-error :messages="$errors->get('operator_person_id')" class="mt-2" />
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <x-input-label for="generations_up" :value="__('Jangkauan ke Atas (Leluhur)')" />
                                            <x-text-input id="generations_up" class="block mt-1 w-full" type="number" name="generations_up" min="0" value="{{ old('generations_up', $user->accessControl?->generations_up ?? 0) }}" />
                                            <x-input-error :messages="$errors->get('generations_up')" class="mt-2" />
                                        </div>
                                        <div>
                                            <x-input-label for="generations_down" :value="__('Jangkauan ke Bawah (Keturunan)')" />
                                            <x-text-input id="generations_down" class="block mt-1 w-full" type="number" name="generations_down" min="0" value="{{ old('generations_down', $user->accessControl?->generations_down ?? 0) }}" />
                                            <x-input-error :messages="$errors->get('generations_down')" class="mt-2" />
                                        </div>
                                    </div>
                                </div>

                                {{-- Form Tautan Person --}}
                                <div x-show="role === 'user'" x-transition>
                                    <x-input-label for="person_id" :value="__('Tautkan ke Anggota Silsilah (untuk Peran User)')" />
                                    <input type="text" name="person_id" id="person_id" class="mt-1" placeholder="Ketik untuk mencari nama...">
                                    <x-input-error :messages="$errors->get('person_id')" class="mt-2" />
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <a href="{{ route('users.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 mr-4">
                                    Batal
                                </a>
                                <x-primary-button>
                                    {{ __('Simpan Perubahan') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function createTomSelect(selector, initialOptions = []) {
                return new window.TomSelect(selector, {
                    valueField: 'value',
                    labelField: 'text',
                    searchField: 'text',
                    maxItems: 1,
                    options: initialOptions,
                    create: false,
                    load: function(query, callback) {
                        if (!query.length) return callback();
                        let url = `{{ route('people.search') }}?search=${encodeURIComponent(query)}`;
                        fetch(url)
                            .then(response => response.json())
                            .then(json => callback(json))
                            .catch(() => callback());
                    },
                    render: {
                        option: (item, escape) => `<div>${escape(item.text)}</div>`,
                        item: (item, escape) => `<div>${escape(item.text)}</div>`
                    }
                });
            }

            // Inisialisasi untuk Tautan Person (User)
            let initialPersonOptions = [];
            @if($linkedPerson)
                initialPersonOptions.push({
                    value: '{{ $linkedPerson->id }}',
                    text: '{{ $linkedPerson->name }} (ID: {{ $linkedPerson->id }})'
                });
            @endif
            const personSelect = createTomSelect('#person_id', initialPersonOptions);
            @if($linkedPerson)
                personSelect.setValue('{{ $linkedPerson->id }}');
            @endif

            // Inisialisasi untuk Akar Silsilah (Operator)
            let initialOperatorOptions = [];
            @if($user->accessControl && $user->accessControl->person)
                initialOperatorOptions.push({
                    value: '{{ $user->accessControl->person->id }}',
                    text: '{{ $user->accessControl->person->name }} (ID: {{ $user->accessControl->person->id }})'
                });
            @endif
            const operatorSelect = createTomSelect('#operator_person_id', initialOperatorOptions);
            @if($user->accessControl && $user->accessControl->person)
                operatorSelect.setValue('{{ $user->accessControl->person->id }}');
            @endif
        });
    </script>
    @endpush
</x-app-layout>