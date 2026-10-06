<div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="overflow-x-auto">
        <table class="min-w-[1200px] w-full text-left text-sm">
            <thead class="border-b border-gray-100 bg-gray-50 dark:border-slate-700 dark:bg-slate-700/50">
                <tr>
                    @foreach($columns as $label)
                        <th class="whitespace-nowrap px-4 py-3 font-semibold text-gray-600 dark:text-gray-300">{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-slate-700">
                @forelse($rows as $row)
                    @php
                        $gradeClass = match($row['grade']) {
                            'A' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
                            'B' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                            'C' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
                            'D' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300',
                            'E' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                            default => 'bg-gray-100 text-gray-700 dark:bg-slate-700 dark:text-gray-300',
                        };
                        $statusClass = $row['status'] === 'selesai' || $row['status'] === 'sudah_dicek'
                            ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'
                            : 'bg-gray-100 text-gray-700 dark:bg-slate-700 dark:text-gray-300';
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/60">
                        @foreach($columns as $key => $label)
                            <td class="whitespace-nowrap px-4 py-3 {{ in_array($key, ['skor_soft_skill', 'skor_hard_skill', 'skor_akhir'], true) ? 'text-right tabular-nums' : 'text-gray-600 dark:text-gray-300' }}">
                                @if($key === 'grade')
                                    <span class="inline-flex min-w-8 justify-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $gradeClass }}">{{ $row[$key] }}</span>
                                @elseif($key === 'status')
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClass }}">{{ $row[$key] }}</span>
                                @else
                                    {{ $row[$key] }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ count($columns) }}" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>