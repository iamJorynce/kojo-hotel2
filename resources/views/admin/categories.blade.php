@extends('admin.layout')

@section('content')

<div class="topbar">
    <h2>🏷 Room Categories</h2>
</div>

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert-error">{{ session('error') }}</div>
@endif

{{-- CREATE FORM --}}
<div class="card" style="margin-bottom:25px;">
    <h3 style="margin-bottom:15px;">➕ Add New Category</h3>

    <form method="POST" action="/admin/categories/create">
        @csrf

        <label>Category Name</label>
        <input type="text" name="name" placeholder="e.g. Deluxe Room" required>

        <label>Description</label>
        <textarea name="description" placeholder="Brief description..." rows="3"></textarea>

        <label>Price per Night (₱)</label>
        <input type="number" name="price" placeholder="e.g. 1500" min="0" step="0.01" required>

        <button type="submit" class="btn btn-primary" style="width:100%;padding:11px;">
            Save Category
        </button>
    </form>
</div>

{{-- CATEGORY LIST --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:15px;">

@forelse($categories as $c)

    <div class="card"
         onmouseover="this.style.transform='translateY(-3px)'"
         onmouseout="this.style.transform='translateY(0)'"
         style="transition:0.2s;">

        <h3 style="margin-bottom:5px;">{{ $c['name'] }}</h3>

        <p style="color:#666;font-size:14px;margin-bottom:8px;">
            {{ $c['description'] ?? 'No description' }}
        </p>

        <p style="font-weight:bold;color:#0a4a6e;font-size:18px;">
            ₱{{ number_format($c['price'], 2) }}
        </p>

        <a href="/admin/categories/edit/{{ $c['id'] }}"
           class="btn btn-primary"
           style="display:block;text-align:center;margin-top:10px;">
            ✏️ Edit
        </a>

        <a href="/admin/categories/delete/{{ $c['id'] }}"
           onclick="return confirm('Delete this category? This cannot be undone.')"
           class="btn btn-danger"
           style="display:block;text-align:center;margin-top:8px;">
            🗑 Delete
        </a>

    </div>

@empty
    <p>No categories yet. Add one above.</p>
@endforelse

</div>

@endsection
