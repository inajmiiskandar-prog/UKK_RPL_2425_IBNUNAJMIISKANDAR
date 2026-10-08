@extends('layouts.dashboard')

@section('title', 'Wizard Penilaian KPI - Step 1')
@section('page-title', 'Wizard Penilaian KPI')
@section('page-breadcrumb', 'KPI / Penilaian / Wizard')

@if(!($reviewMode ?? false))
    @php
        $employeeJson = $employeeData->toJson(JSON_UNESCAPED_UNICODE);
    @endphp
@endif

@section('content')
<div class="mb-6">
    <a href="{{ route('kpi.assessment.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-purple-600 dark:text-gray-400">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali
    </a>
</div>

{{-- Progress Indicator --}}
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-purple-600 text-sm font-bold text-white">1</div>
            <span class="font-medium text-purple-600 dark:text-purple-400">Periode & Karyawan</span>
        </div>
        <div class="flex-1 h-1 mx-4 bg-gray-200 dark:bg-slate-700"><div class="h-full w-0 bg-purple-600"></div></div>
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 text-sm font-bold text-gray-500 dark:bg-slate-700 dark:text-gray-400">2</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">Soft Skill</span>
        </div>
        <div class="flex-1 h-1 mx-4 bg-gray-200 dark:bg-slate-700"><div class="h-full w-0 bg-gray-200 dark:bg-slate-700"></div></div>
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 text-sm font-bold text-gray-500 dark:bg-slate-700 dark:text-gray-400">3</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">Hard Skill</span>
        </div>
        <div class="flex-1 h-1 mx-4 bg-gray-200 dark:bg-slate-700"><div class="h-full w-0 bg-gray-200 dark:bg-slate-700"></div></div>
        <div class="flex items-center gap-2">
            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 text-sm font-bold text-gray-500 dark:bg-slate-700 dark:text-gray-400">4</div>
            <span class="text-sm text-gray-500 dark:text-gray-400">Review</span>
        </div>
    </div>
</div>

{{-- Info Box --}}
<div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-900/20">
    <h3 class="font-semibold text-blue-700 dark:text-blue-300">Step 1: Konfirmasi Review Atasan</h3>
    <p class="mt-1 text-sm text-blue-600 dark:text-blue-400">Konfirmasi assessment self karyawan sebelum mulai review.</p>
</div>

@if(session('error'))<div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">{{ session('error') }}</div>@endif

@if($errors->any())
<div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
    <ul class="list-inside list-disc">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <form action="{{ route('kpi.assessment.wizard.step1') }}" method="POST" id="wizard-form">
        @csrf
        @if($reviewMode ?? false)
        <input type="hidden" name="assessment_id" value="{{ $assessment->id }}">
        <div class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
            <p>Periode: <strong>{{ $assessment->period?->nama ?? '-' }}</strong></p>
            <p>Karyawan: <strong>{{ $assessment->user->nama }}</strong></p>
            <p>Status: <strong>Menunggu review atasan</strong></p>
        </div>
        @else
        <div class="space-y-6">
            {{-- Pilih Periode --}}
            <div>
                <label for="period_value" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Periode Penilaian (YYYY-MM) <span class="text-red-500">*</span>
                </label>
                <input type="month" name="period_value" id="period_value" required
                      value="{{ $periodValue }}"
                       class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm focus:border-purple-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                @error('period_value')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Pilih Karyawan - Custom Dropdown --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Karyawan <span class="text-red-500">*</span>
                </label>

                {{-- Hidden input untuk form submission --}}
                <input type="hidden" name="user_id" id="selected_user_id" value="{{ $wizardData['user_id'] }}">

                {{-- Custom Dropdown Trigger --}}
                <div class="relative" id="employee-dropdown-container">
                    <button type="button" id="dropdown-toggle"
                            class="flex w-full items-center justify-between rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-left transition-colors hover:border-purple-300 focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:hover:border-purple-500"
                            aria-haspopup="listbox"
                            aria-expanded="false">
                        <span id="dropdown-placeholder" class="text-gray-500 dark:text-gray-400">-- Pilih Karyawan --</span>
                        <span id="dropdown-selected" class="hidden flex items-center gap-2">
                            <span id="selected-initials" class="flex h-6 w-6 items-center justify-center rounded-full bg-purple-100 text-xs font-semibold text-purple-700 dark:bg-purple-900 dark:text-purple-300"></span>
                            <span id="selected-name"></span>
                        </span>
                        <svg class="ml-2 h-4 w-4 text-gray-400 transition-transform" id="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Dropdown Panel --}}
                    <div id="dropdown-panel"
                         class="absolute z-50 mt-1 w-full rounded-xl border border-gray-200 bg-white shadow-lg ring-1 ring-black/5 dark:border-slate-600 dark:bg-slate-800"
                         style="display: none;">
                        <div class="p-2">
                            {{-- Search Input --}}
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input type="text" id="dropdown-search"
                                       class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-10 pr-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-slate-600 dark:bg-slate-700 dark:text-white dark:placeholder-gray-400"
                                       placeholder="Cari nama, NIK, atau divisi..."
                                       autocomplete="off">
                            </div>
                        </div>

                        {{-- Employee List with Scroll --}}
                        <div id="employee-list"
                             class="max-h-64 overflow-y-auto border-t border-gray-100 dark:border-slate-700"
                             role="listbox">
                            {{-- Items will be rendered by JavaScript --}}
                        </div>

                        {{-- No Results Message --}}
                        <div id="no-results" class="hidden p-4 text-center text-sm text-gray-500 dark:text-gray-400">
                            Tidak ada karyawan yang cocok
                        </div>
                    </div>
                </div>

                @error('user_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    @if($hasBawahan)
                    Anda dapat memilih bawahan atau menilai diri sendiri.
                    @else
                    Anda hanya dapat menilai diri sendiri.
                    @endif
                </p>
            </div>
        </div>

        @endif

        <div class="mt-8 flex justify-end">
            <button type="submit" class="rounded-xl bg-purple-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-purple-700">
                Selanjutnya
                <svg class="ml-2 inline h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </form>
</div>

{{-- JavaScript for Custom Dropdown --}}
@if(!($reviewMode ?? false))
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data karyawan dari server
    const employees = {!! $employeeJson !!};
    const selectedUserId = document.getElementById('selected_user_id').value;

    // Elemen DOM
    const dropdownToggle = document.getElementById('dropdown-toggle');
    const dropdownPanel = document.getElementById('dropdown-panel');
    const dropdownSearch = document.getElementById('dropdown-search');
    const employeeList = document.getElementById('employee-list');
    const noResults = document.getElementById('no-results');
    const dropdownPlaceholder = document.getElementById('dropdown-placeholder');
    const dropdownSelected = document.getElementById('dropdown-selected');
    const dropdownArrow = document.getElementById('dropdown-arrow');
    const periodInput = document.getElementById('period_value');

    periodInput.addEventListener('change', function() {
        const nextUrl = new URL(window.location.href);
        nextUrl.searchParams.set('period_value', this.value);

        const selectedId = document.getElementById('selected_user_id').value;
        if (selectedId) {
            nextUrl.searchParams.set('user_id', selectedId);
        } else {
            nextUrl.searchParams.delete('user_id');
        }

        window.location.assign(nextUrl.toString());
    });

    let focusedIndex = -1;
    let filteredEmployees = [...employees];

    // Generate color untuk avatar berdasarkan nama
    function getAvatarColor(name) {
        const colors = [
            'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
            'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300',
            'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300',
            'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
            'bg-lime-100 text-lime-700 dark:bg-lime-900 dark:text-lime-300',
            'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
            'bg-emerald-100 text-emerald-700 dark:bg-emerald-900 dark:text-emerald-300',
            'bg-teal-100 text-teal-700 dark:bg-teal-900 dark:text-teal-300',
            'bg-cyan-100 text-cyan-700 dark:bg-cyan-900 dark:text-cyan-300',
            'bg-sky-100 text-sky-700 dark:bg-sky-900 dark:text-sky-300',
            'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
            'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300',
            'bg-violet-100 text-violet-700 dark:bg-violet-900 dark:text-violet-300',
            'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300',
            'bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-900 dark:text-fuchsia-300',
            'bg-pink-100 text-pink-700 dark:bg-pink-900 dark:text-pink-300',
            'bg-rose-100 text-rose-700 dark:bg-rose-900 dark:text-rose-300',
        ];
        let hash = 0;
        for (let i = 0; i < name.length; i++) {
            hash = name.charCodeAt(i) + ((hash << 5) - hash);
        }
        return colors[Math.abs(hash) % colors.length];
    }

    // Render satu item karyawan
    function renderEmployeeItem(emp, index) {
        const isSelected = emp.id_user == selectedUserId;
        const isDisabled = emp.disabled;
        const badgeClass = emp.disabled
            ? 'bg-gray-100 text-gray-500 dark:bg-slate-700 dark:text-gray-400'
            : emp.status === 'menunggu_review'
                ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300'
                : 'bg-gray-100 text-gray-500 dark:bg-slate-700 dark:text-gray-400';
        const avatarColor = getAvatarColor(emp.nama);
        const cursorClass = isDisabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:bg-gray-50 dark:hover:bg-slate-700';
        const tabIndex = isDisabled ? '' : 'tabindex="0"';
        const role = isDisabled ? 'aria-disabled="true"' : '';
        const selectedClass = isSelected && !isDisabled ? 'bg-purple-50 dark:bg-purple-900/20' : '';

        return `
            <div class="employee-item flex items-center gap-3 px-3 py-2.5 ${cursorClass} ${selectedClass} rounded-lg transition-colors"
                 data-index="${index}"
                 data-id="${emp.id_user}"
                 data-disabled="${emp.disabled}"
                 ${tabIndex}
                 ${role}
                 role="option"
                 aria-selected="${isSelected && !isDisabled}"
                 aria-disabled="${isDisabled}">
                {{-- Avatar / Initials --}}
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full ${avatarColor} text-xs font-semibold">
                    ${emp.initials}
                </span>
                {{-- Info --}}
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="truncate font-medium text-gray-900 dark:text-white">${emp.nama}</span>
                        ${emp.is_current_user ? '<span class="shrink-0 rounded bg-blue-100 px-1.5 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900 dark:text-blue-300">Saya</span>' : ''}
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <span>${emp.nik !== '-' ? 'NIK: ' + emp.nik + ' • ' : ''}${emp.divisi} / ${emp.jabatan}</span>
                    </div>
                </div>
                {{-- Status Badge --}}
                ${emp.disabled ? `<span class="shrink-0 rounded px-2 py-1 text-xs font-medium ${badgeClass}">${emp.badge}</span>` : ''}
            </div>
        `;
    }

    // Render semua karyawan
    function renderEmployees(emps) {
        if (emps.length === 0) {
            employeeList.innerHTML = '';
            noResults.classList.remove('hidden');
            return;
        }

        noResults.classList.add('hidden');
        employeeList.innerHTML = emps.map((emp, i) => renderEmployeeItem(emp, i)).join('');
        focusedIndex = -1;

        // Attach click handlers
        employeeList.querySelectorAll('.employee-item').forEach(item => {
            item.addEventListener('click', function() {
                if (this.dataset.disabled === 'true') return;
                selectEmployee(parseInt(this.dataset.index));
            });

            item.addEventListener('keydown', function(e) {
                if (this.dataset.disabled === 'true') return;
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    selectEmployee(parseInt(this.dataset.index));
                }
            });
        });
    }

    // Filter karyawan
    function filterEmployees(query) {
        query = query.toLowerCase().trim();
        if (!query) {
            filteredEmployees = [...employees];
        } else {
            filteredEmployees = employees.filter(emp =>
                emp.nama.toLowerCase().includes(query) ||
                emp.nik.toLowerCase().includes(query) ||
                emp.divisi.toLowerCase().includes(query) ||
                emp.jabatan.toLowerCase().includes(query)
            );
        }
        renderEmployees(filteredEmployees);
    }

    // Pilih karyawan
    function selectEmployee(index) {
        const emp = filteredEmployees[index];
        if (!emp || emp.disabled) return;

        // Update hidden input
        document.getElementById('selected_user_id').value = emp.id_user;

        // Update trigger display
        dropdownPlaceholder.classList.add('hidden');
        dropdownSelected.classList.remove('hidden');
        document.getElementById('selected-initials').textContent = emp.initials;
        document.getElementById('selected-name').textContent = emp.nama;

        // Tutup dropdown
        closeDropdown();

        // Update aria
        dropdownToggle.setAttribute('aria-expanded', 'false');

        // Re-render list dengan selected state
        renderEmployees(filteredEmployees);
    }

    // Toggle dropdown
    function toggleDropdown() {
        const isOpen = dropdownPanel.style.display !== 'none';
        if (isOpen) {
            closeDropdown();
        } else {
            openDropdown();
        }
    }

    function openDropdown() {
        dropdownPanel.style.display = 'block';
        dropdownArrow.style.transform = 'rotate(180deg)';
        dropdownToggle.setAttribute('aria-expanded', 'true');
        dropdownSearch.focus();
        filteredEmployees = [...employees];
        renderEmployees(filteredEmployees);

        // Select existing value if any
        if (selectedUserId) {
            const empIndex = filteredEmployees.findIndex(e => e.id_user == selectedUserId);
            if (empIndex !== -1) {
                selectEmployee(empIndex);
            }
        }
    }

    function closeDropdown() {
        dropdownPanel.style.display = 'none';
        dropdownArrow.style.transform = 'rotate(0deg)';
        dropdownToggle.setAttribute('aria-expanded', 'false');
        dropdownSearch.value = '';
    }

    // Keyboard navigation
    function handleKeyboard(e) {
        if (dropdownPanel.style.display === 'none') {
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openDropdown();
            }
            return;
        }

        switch (e.key) {
            case 'Escape':
                e.preventDefault();
                closeDropdown();
                dropdownToggle.focus();
                break;

            case 'ArrowDown':
                e.preventDefault();
                navigateList(1);
                break;

            case 'ArrowUp':
                e.preventDefault();
                navigateList(-1);
                break;

            case 'Enter':
                e.preventDefault();
                if (focusedIndex >= 0 && focusedIndex < filteredEmployees.length) {
                    const emp = filteredEmployees[focusedIndex];
                    if (!emp.disabled) {
                        selectEmployee(focusedIndex);
                    }
                }
                break;

            case 'Tab':
                closeDropdown();
                break;
        }
    }

    function navigateList(direction) {
        // Find next non-disabled item
        let newIndex = focusedIndex;
        let attempts = 0;
        do {
            newIndex += direction;
            if (newIndex < 0) newIndex = filteredEmployees.length - 1;
            if (newIndex >= filteredEmployees.length) newIndex = 0;
            attempts++;
        } while (attempts < filteredEmployees.length && filteredEmployees[newIndex]?.disabled);

        if (attempts < filteredEmployees.length) {
            focusedIndex = newIndex;
            const items = employeeList.querySelectorAll('.employee-item');
            const targetItem = items[focusedIndex];
            if (targetItem) {
                targetItem.scrollIntoView({ block: 'nearest' });
                // Remove focus from all first
                items.forEach(i => i.classList.remove('ring-2', 'ring-purple-500'));
                targetItem.classList.add('ring-2', 'ring-purple-500');
            }
        }
    }

    // Event listeners
    dropdownToggle.addEventListener('click', toggleDropdown);
    dropdownSearch.addEventListener('input', (e) => filterEmployees(e.target.value));
    dropdownSearch.addEventListener('keydown', handleKeyboard);

    // Close on click outside
    document.addEventListener('click', (e) => {
        const container = document.getElementById('employee-dropdown-container');
        if (!container.contains(e.target)) {
            closeDropdown();
        }
    });

    // Global keyboard handler for the dropdown
    document.addEventListener('keydown', (e) => {
        if (dropdownPanel.style.display !== 'none') {
            handleKeyboard(e);
        }
    });

    // Initialize - select existing value if any
    if (selectedUserId) {
        const emp = employees.find(e => e.id_user == selectedUserId);
        if (emp && !emp.disabled) {
            dropdownPlaceholder.classList.add('hidden');
            dropdownSelected.classList.remove('hidden');
            document.getElementById('selected-initials').textContent = emp.initials;
            document.getElementById('selected-name').textContent = emp.nama;
        }
    }
});
</script>
@endif

{{-- Dark mode styles for dropdown arrow animation --}}
<style>
#dropdown-arrow {
    transition: transform 0.2s ease;
}
</style>
@endsection
