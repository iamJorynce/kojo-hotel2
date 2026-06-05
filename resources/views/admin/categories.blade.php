@extends('admin.layout')

@section('content')

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <h2>🏷 Room Categories</h2>
</div>

@if(session('success'))
    <div style="background:#d1fae5;color:#065f46;padding:10px;border-radius:8px;margin-bottom:15px;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background:#fee2e2;color:#991b1b;padding:10px;border-radius:8px;margin-bottom:15px;">
        {{ session('error') }}
    </div>
@endif

{{-- CREATE CATEGORY FORM --}}
<div style="
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
    margin-bottom:25px;
">

    <h3 style="margin-bottom:15px;">➕ Add New Category</h3>

    <form method="POST" action="/admin/categories/create">
        @csrf

        <input type="text" name="name" placeholder="Category Name" required
               style="width:100%;padding:10px;margin-bottom:10px;border:1px solid #ddd;border-radius:8px;">

        <textarea name="description" placeholder="Category Description" required
                  style="width:100%;padding:10px;margin-bottom:10px;border:1px solid #ddd;border-radius:8px;"></textarea>

        <input type="number" name="price" placeholder="Price" required
               style="width:100%;padding:10px;margin-bottom:10px;border:1px solid #ddd;border-radius:8px;">

        <button type="submit"
                style="background:#0f172a;color:white;padding:10px 15px;border:none;border-radius:8px;width:100%;">
            Save Category
        </button>
    </form>
</div>

{{-- CATEGORY LIST --}}
<div style="
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
    gap:15px;
">

@foreach($categories as $c)

    <div style="
        background:white;
        padding:15px;
        border-radius:12px;
        box-shadow:0 5px 15px rgba(0,0,0,0.05);
        transition:0.2s;
    " onmouseover="this.style.transform='scale(1.02)'"
       onmouseout="this.style.transform='scale(1)'">

        <h3 style="margin-bottom:5px;">{{ $c['name'] }}</h3>

        <p style="color:#666;font-size:14px;">
            {{ $c['description'] }}
        </p>

        <p style="font-weight:bold;color:#0a4a6e;">
            ₱{{ number_format($c['price'], 2) }}
        </p>
        <a href="/admin/categories/edit/{{ $c['id'] }}"
   style="
        display:block;
        margin-top:8px;
        text-align:center;
        background:#0a4a6e;
        color:white;
        padding:8px;
        border-radius:8px;
        text-decoration:none;
   ">
    Edit
</a>
        <a href="/admin/categories/delete/{{ $c['id'] }}"
           onclick="return confirm('Delete this category?')"
           style="
                display:block;
                margin-top:10px;
                text-align:center;
                background:#ef4444;
                color:white;
                padding:8px;
                border-radius:8px;
                text-decoration:none;
           ">
            Delete
        </a>

    </div>

@endforeach

</div>

@endsection