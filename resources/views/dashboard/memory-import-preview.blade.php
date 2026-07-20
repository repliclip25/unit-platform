<x-app-layout title="Import Preview">

    <div class="max-w-5xl mx-auto">

        <div class="mb-4 flex items-center gap-3">
            <a href="{{ route('memory') }}" class="text-gray-500 hover:text-white text-sm">← Back to Memory</a>
            <span class="text-gray-700">/</span>
            <span class="text-gray-400 text-sm capitalize">Import {{ $type }}</span>
        </div>

        <div class="bg-gray-900 border border-gray-800 rounded-xl mb-4">
            <div class="px-6 py-4 border-b border-gray-800 flex items-center justify-between">
                <div>
                    <h2 class="text-white font-semibold">Review Column Mapping</h2>
                    <p class="text-gray-500 text-xs mt-0.5">{{ $total }} rows detected · showing first 5 · adjust mapping below then confirm import</p>
                <p class="text-gray-600 text-xs mt-1">Need the template? <a href="{{ route('memory.import.template', $type) }}" class="text-brand-text hover:underline">Download {{ $type }}_import_template.csv</a></p>
                </div>
                <span class="text-xs bg-brand/15 text-brand-text border border-brand/40 px-2 py-1 rounded capitalize">{{ $type }}</span>
            </div>

            <form method="POST" action="{{ route('memory.import.commit') }}">
                @csrf

                {{-- Column mapping selectors --}}
                <div class="px-6 py-4 border-b border-gray-800">
                    <p class="text-gray-400 text-xs mb-3 font-medium uppercase tracking-wide">Map your columns</p>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($headers as $i => $header)
                            <div class="flex items-center gap-3">
                                <span class="text-gray-500 text-xs w-32 truncate" title="{{ $header }}">{{ $header }}</span>
                                <span class="text-gray-700 text-xs">→</span>
                                <select name="mapping[{{ $i }}]" class="flex-1 bg-gray-800 text-white text-xs rounded-lg px-2 py-1.5 border border-gray-700 focus:outline-none focus:border-brand">
                                    <option value="">— skip —</option>
                                    @php
                                        $fields = match($type) {
                                            'clients'  => ['name' => 'Name', 'industry' => 'Industry', 'preferred_style' => 'Preferred Style', 'notes' => 'Notes'],
                                            'contacts' => ['name' => 'Name', 'email' => 'Email', 'phone' => 'Phone', 'role' => 'Role', 'client_name' => 'Client Name'],
                                            'assets'   => ['name' => 'Name', 'type' => 'Type', 'vendor' => 'Vendor', 'renewal_date' => 'Renewal Date', 'cost_per_year' => 'Cost/Year', 'client_name' => 'Client Name'],
                                            default    => [],
                                        };
                                    @endphp
                                    @foreach($fields as $value => $label)
                                        <option value="{{ $value }}" {{ ($mapping[$i] ?? null) === $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Data preview --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b border-gray-800">
                                @foreach($headers as $h)
                                    <th class="px-4 py-2 text-left text-gray-500 font-medium whitespace-nowrap">{{ $h }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach($rows as $row)
                                <tr>
                                    @foreach($row as $cell)
                                        <td class="px-4 py-2 text-gray-300 whitespace-nowrap max-w-xs truncate">{{ $cell }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-800 flex items-center justify-between">
                    <a href="{{ route('memory') }}" class="text-sm text-gray-500 hover:text-white">Cancel</a>
                    <button type="submit" class="bg-brand hover:bg-brand-deep text-brand-text text-sm font-medium rounded-lg px-6 py-2 transition">
                        Import {{ $total }} {{ ucfirst($type) }} →
                    </button>
                </div>
            </form>
        </div>

    </div>

</x-app-layout>
