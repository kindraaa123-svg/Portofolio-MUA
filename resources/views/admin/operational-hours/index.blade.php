@extends('layouts.admin')
@section('title', 'Jam Operasional')
@section('content')
<section class="mb-6 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
    <div class="relative bg-white p-6 md:p-8">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Business Schedule</p>
        <h1 class="mt-2 font-serif text-3xl leading-tight text-slate-900 md:text-4xl">Jam Operasional</h1>
        <p class="mt-3 max-w-3xl text-sm text-slate-600">Atur jam buka dan hari libur mingguan. Pengaturan ini dipakai sebagai acuan ketersediaan layanan.</p>
    </div>
</section>

<form method="POST" action="{{ route('admin.operational-hours.update') }}" class="space-y-6">
    @csrf
    <section class="card-premium bg-white space-y-5">
        <div class="flex flex-wrap items-end justify-between gap-3 border-b border-slate-200 pb-4">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Pengaturan Per Hari</h2>
                <p class="mt-1 text-sm text-slate-600">Centang `Tutup` jika hari tersebut libur. Jam otomatis dinonaktifkan.</p>
            </div>
            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600">7 Hari</span>
        </div>

        <div class="overflow-x-auto">
            <table class="table-admin min-w-[820px]">
                <thead>
                <tr>
                    <th>Hari</th>
                    <th>Jam Buka</th>
                    <th>Jam Tutup</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @foreach($rows as $index => $row)
                    <tr class="{{ $row['is_closed'] ? 'bg-slate-50/60' : '' }}">
                        <td>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-slate-800">{{ $row['day_name'] }}</span>
                                <span
                                    class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $row['is_closed'] ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }}"
                                    data-status-chip
                                >
                                    {{ $row['is_closed'] ? 'Tutup' : 'Buka' }}
                                </span>
                            </div>
                            <input type="hidden" name="hours[{{ $index }}][day_of_week]" value="{{ $row['day_of_week'] }}">
                            <input type="hidden" name="hours[{{ $index }}][day_name]" value="{{ $row['day_name'] }}">
                        </td>
                        <td>
                            <input class="input w-full" type="time" name="hours[{{ $index }}][open_time]" value="{{ $row['open_time'] }}" {{ $row['is_closed'] ? 'disabled' : '' }}>
                        </td>
                        <td>
                            <input class="input w-full" type="time" name="hours[{{ $index }}][close_time]" value="{{ $row['close_time'] }}" {{ $row['is_closed'] ? 'disabled' : '' }}>
                        </td>
                        <td>
                            <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                                <input type="checkbox" name="hours[{{ $index }}][is_closed]" value="1" @checked($row['is_closed']) onchange="toggleClosed(this)">
                                Tutup
                            </label>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 pt-4">
            <button class="btn-primary w-full sm:w-auto" type="submit" onclick="return confirm('Simpan perubahan jam operasional?');">Simpan Jam Operasional</button>
        </div>
    </section>
</form>

<script>
function toggleClosed(checkbox) {
    const row = checkbox.closest('tr');
    if (!row) return;

    const inputs = row.querySelectorAll('input[type="time"]');
    const chip = row.querySelector('[data-status-chip]');

    inputs.forEach((input) => {
        input.disabled = checkbox.checked;
    });

    row.classList.toggle('bg-slate-50/60', checkbox.checked);

    if (chip) {
        chip.textContent = checkbox.checked ? 'Tutup' : 'Buka';
        chip.className = checkbox.checked
            ? 'rounded-full px-2 py-0.5 text-[11px] font-semibold bg-amber-100 text-amber-700'
            : 'rounded-full px-2 py-0.5 text-[11px] font-semibold bg-emerald-100 text-emerald-700';
    }
}
</script>
@endsection
