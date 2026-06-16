@extends('admin.layout')

@section('content')

<div style="max-width:600px;margin:auto;">

    <h2>✏️ Edit Category</h2>

    @if(session('error'))
        <div class="alert-error">{{ session('error') }}</div>
    @endif

    <div class="card">
        <form method="POST" action="/admin/categories/update/{{ $category['id'] }}">
            @csrf

            <label>Category Name</label>
            <input type="text" name="name" value="{{ $category['name'] }}" required>

            <label>Description</label>
            <textarea name="description" rows="3">{{ $category['description'] ?? '' }}</textarea>

            <label>Price per Night (₱)</label>
            <input type="number" name="price" value="{{ $category['price'] }}" min="0" step="0.01" required>

            <div style="display:flex;gap:10px;margin-top:5px;">
                <button type="submit" class="btn btn-success" style="flex:1;padding:11px;">
                    ✅ Update Category
                </button>
                <a href="/admin/categories" class="btn" style="flex:1;padding:11px;text-align:center;">
                    Cancel
                </a>
            </div>

        </form>
    </div>

</div>

@endsection
