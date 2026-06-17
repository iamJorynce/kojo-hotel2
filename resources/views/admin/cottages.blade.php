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
    <h2>🏠 Cottages Management</h2>
</div>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

{{-- ADD FORM --}}
<div class="card" style="margin-bottom:20px;">
    <h3>Add Cottage</h3>
    <form method="POST" action="/admin/cottages/create" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:12px;align-items:flex-end;">
        @csrf
        <div>
            <label>Name *</label>
            <input type="text" name="name" placeholder="e.g., Beach Bale 1" required style="width:100%;padding:10px;">
        </div>
        <div>
            <label>Size *</label>
            <select name="size_category" required style="width:100%;padding:10px;">
                <option value="">Select...</option>
                <option value="small">Small</option>
                <option value="medium">Medium</option>
                <option value="large">Large</option>
                <option value="extra-large">Extra Large</option>
            </select>
        </div>
        <div>
            <label>Price/Day (₱) *</label>
            <input type="number" name="price_per_day" step="0.01" min="0" required style="width:100%;padding:10px;">
        </div>
        <div>
            <label>Description</label>
            <input type="text" name="description" placeholder="Optional" style="width:100%;padding:10px;">
        </div>
        <button type="submit" class="btn btn-primary" style="padding:10px 16px;">Add</button>
    </form>
</div>

{{-- TABLE --}}
@if($cottages->isEmpty())
    <div class="card" style="text-align:center;padding:40px;color:#999;">
        No cottages yet.
    </div>
@else
<div style="overflow-x:auto;">
<table style="width:100%;border-collapse:collapse;background:white;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);font-size:13px;">
    <thead>
        <tr style="background:#0f172a;color:white;">
            <th style="padding:11px 14px;text-align:left;">Name</th>
            <th style="padding:11px 14px;text-align:center;">Size</th>
            <th style="padding:11px 14px;text-align:right;">Price/Day</th>
            <th style="padding:11px 14px;text-align:left;">Description</th>
            <th style="padding:11px 14px;text-align:center;">Actions</th>
        </tr>
    </thead>
    <tbody>
    @foreach($cottages as $c)
    <tr style="border-bottom:1px solid #f0f0f0;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
        <td style="padding:10px 14px;font-weight:600;">{{ $c['name'] }}</td>
        <td style="padding:10px 14px;text-align:center;text-transform:capitalize;">{{ $c['size_category'] ?? '-' }}</td>
        <td style="padding:10px 14px;text-align:right;font-weight:600;">₱{{ number_format($c['price_per_day'], 2) }}</td>
        <td style="padding:10px 14px;font-size:12px;color:#666;">{{ $c['description'] ?? '-' }}</td>
        <td style="padding:10px 14px;text-align:center;">
            <button onclick="toggleEdit({{ $c['id'] }})" class="btn" style="padding:4px 8px;font-size:11px;margin-right:4px;">Edit</button>
            <a href="/admin/cottages/delete/{{ $c['id'] }}" class="btn" style="padding:4px 8px;font-size:11px;" onclick="return confirm('Delete?')">Delete</a>
        </td>
    </tr>
    <tr id="edit-{{ $c['id'] }}" style="display:none;background:#f8fafc;">
        <td colspan="5" style="padding:12px 14px;">
            <form method="POST" action="/admin/cottages/update/{{ $c['id'] }}" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr auto;gap:12px;align-items:flex-end;">
                @csrf
                <div>
                    <label style="font-size:12px;">Name</label>
                    <input type="text" name="name" value="{{ $c['name'] }}" style="width:100%;padding:8px;">
                </div>
                <div>
                    <label style="font-size:12px;">Size</label>
                    <select name="size_category" style="width:100%;padding:8px;">
                        <option value="small" {{ ($c['size_category'] ?? '') === 'small' ? 'selected' : '' }}>Small</option>
                        <option value="medium" {{ ($c['size_category'] ?? '') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="large" {{ ($c['size_category'] ?? '') === 'large' ? 'selected' : '' }}>Large</option>
                        <option value="extra-large" {{ ($c['size_category'] ?? '') === 'extra-large' ? 'selected' : '' }}>Extra Large</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;">Price/Day</label>
                    <input type="number" name="price_per_day" step="0.01" value="{{ $c['price_per_day'] }}" style="width:100%;padding:8px;">
                </div>
                <div>
                    <label style="font-size:12px;">Description</label>
                    <input type="text" name="description" value="{{ $c['description'] ?? '' }}" style="width:100%;padding:8px;">
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="submit" class="btn btn-primary" style="padding:8px 12px;font-size:11px;">Save</button>
                    <button type="button" onclick="toggleEdit({{ $c['id'] }})" class="btn" style="padding:8px 12px;font-size:11px;">Cancel</button>
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
