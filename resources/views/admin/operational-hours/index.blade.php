@extends('layouts.admin')
@section('title', 'Jam Operasional')
@section('content')
<h1 class="text-2xl font-semibold mb-6">Jam Operasional</h1>

<form method="POST" action="{{ route('admin.operational-hours.update') }}" class="card-premium bg-white overflow-x-auto">
    @csrf
    <table class="table-admin min-w-[760px]">
        <thead>
        <tr>
            <th>Hari</th>
            <th>Jam Buka</th>
            <th>Jam Tutup</th>
            <th>Tutup/Libur</th>
        </tr>
        </thead>
        <tbody>
        @foreach($rows as $index => $row)
            <tr>
                <td>
                    {{ $row['day_name'] }}
                    <input type="hidden" name="hours[{{ $index }}][day_of_week]" value="{{ $row['day_of_week'] }}">
                    <input type="hidden" name="hours[{{ $index }}][day_name]" value="{{ $row['day_name'] }}">
                </td>
                <td>
                    <input class="input" type="time" name="hours[{{ $index }}][open_time]" value="{{ $row['open_time'] }}" {{ $row['is_closed'] ? 'disabled' : '' }}>
                </td>
                <td>
                    <input class="input" type="time" name="hours[{{ $index }}][close_time]" value="{{ $row['close_time'] }}" {{ $row['is_closed'] ? 'disabled' : '' }}>
                </td>
                <td>
                    <label class="inline-flex items-center gap-2 text-sm">
                        <input type="checkbox" name="hours[{{ $index }}][is_closed]" value="1" @checked($row['is_closed']) onchange="toggleClosed(this)">
                        Tutup
                    </label>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-4">
        <button class="btn-primary" type="submit" onclick="return confirm('Simpan perubahan jam operasional?');">Simpan Jam Operasional</button>
    </div>
</form>

<script>
function toggleClosed(checkbox) {
    const row = checkbox.closest('tr');
    if (!row) return;
    const inputs = row.querySelectorAll('input[type="time"]');
    inputs.forEach((input) => {
        input.disabled = checkbox.checked;
    });
}
</script>
@endsection
