@extends('admin.layout')

@section('content')

<h2>Edit Category</h2>

<form method="POST" action="/admin/categories/update/{{ $category['id'] }}">
    @csrf
<label>Room Category</label>
    <input type="text" name="name"
           value="{{ $category['name'] }}"
           style="width:100%;padding:10px;margin-bottom:10px;">

<label>Room Description</label>
    <textarea name="description"
              style="width:100%;padding:10px;margin-bottom:10px;">{{ $category['description'] }}</textarea>
<label>Room Price</label>
    <input type="number" name="price"
           value="{{ $category['price'] }}"
           style="width:100%;padding:10px;margin-bottom:10px;">

    <button style="background:#0a4a6e;color:white;padding:10px 15px;border:none;border-radius:8px;">
        Update Category
    </button>

</form>

@endsection