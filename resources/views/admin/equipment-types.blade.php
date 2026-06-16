@extends('admin.layout')

@section('content')

@if($errors->any())
    <div style="background:#fadbd8;border:2px solid #c0392b;padding:16px;border-radius:8px;margin-bottom:20px;">
        <p style="margin:0;color:#c0392b;font-weight:600;">❌ Error:</p>
        @foreach($errors->all() as $error)
            <p style="margin:4px 0 0 0;color:#c0392b;font-size:13px;">{{ $error }}</p>
        @endforeach
    </div>
@endif

<div class="topbar">
    <h2>⚙️ Equipment Types (Chairs, Tables, etc)</h2>
</div>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

{{-- ADD FORM --}}
<div class="card" style="margin-bottom:20px;">
    <h3>Add Equipment Type</h3>
    <form method="POST" action="/admin/equipment-types/create" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:12px;align-items:flex-end;">
        @csrf
        <div>
            <label>Name *</label>
            <input type="text" name="name" required style="width:100%;padding:10px;">
        </div>
        <div>
            <label>Price per Day (₱) *</label>
            <input type="number" name="unit_price" step="0.01" min="0" required style="width:100%;padding:10px;">
        </div>
        <div>
            <label>Quantity Available *</label>
            <input type="number" name="quantity_available" min="0" required style="width:100%;padding:10px;">
        </div>
        <button type="submit" class="btn btn-primary" style="padding:10px 20px;">Add</button>
    </form>
</div>

{{-- TABLE --}}
@if($equipmentTypes->isEmpty())
    <div class="card" style="text-align:center;padding:40px;color:#999;">
        No equipment types yet.
    </div>
@else
<div style="overflow-x:auto;">
<table style="width:100%;border-collapse:collapse;background:white;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);font-size:13px;">
    <thead>
        <tr style="background:#0f172a;color:white;">
            <th style="padding:11px 14px;text-align:left;">Name</th>
            <th style="padding:11px 14px;text-align:right;">Price/Day</th>
            <th style="padding:11px 14px;text-align:center;">Available</th>
            <th style="padding:11px 14px;text-align:center;">Actions</th>
        </tr>
    </thead>
    <tbody>
    @foreach($equipmentTypes as $equip)
    <tr style="border-bottom:1px solid #f0f0f0;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
        <td style="padding:10px 14px;font-weight:600;">{{ $equip['name'] }}</td>
        <td style="padding:10px 14px;text-align:right;">₱{{ number_format($equip['unit_price'], 2) }}</td>
        <td style="padding:10px 14px;text-align:center;font-weight:600;">{{ $equip['quantity_available'] ?? 0 }}</td>
        <td style="padding:10px 14px;text-align:center;">
            <button onclick="toggleEdit({{ $equip['id'] }})" class="btn" style="padding:4px 8px;font-size:11px;">Edit</button>
        </td>
    </tr>
    <tr id="edit-{{ $equip['id'] }}" style="display:none;background:#f8fafc;">
        <td colspan="4" style="padding:12px 14px;">
            <form method="POST" action="/admin/equipment-types/update/{{ $equip['id'] }}" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:12px;align-items:flex-end;">
                @csrf
                <div>
                    <label style="font-size:12px;">Name</label>
                    <input type="text" name="name" value="{{ $equip['name'] }}" style="width:100%;padding:8px;">
                </div>
                <div>
                    <label style="font-size:12px;">Price/Day</label>
                    <input type="number" name="unit_price" step="0.01" value="{{ $equip['unit_price'] }}" style="width:100%;padding:8px;">
                </div>
                <div>
                    <label style="font-size:12px;">Available</label>
                    <input type="number" name="quantity_available" value="{{ $equip['quantity_available'] }}" style="width:100%;padding:8px;">
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" class="btn btn-primary" style="padding:8px 12px;font-size:11px;">Save</button>
                    <button type="button" onclick="toggleEdit({{ $equip['id'] }})" class="btn" style="padding:8px 12px;font-size:11px;">Cancel</button>
                </div>
            </form>
        </td>
    </tr>
    @endforeach
    </tbody>
</table>
</div>
@endif

<script>
function toggleEdit(id) {
    const row = document.getElementById('edit-' + id);
    row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
}
</script>

@endsection
