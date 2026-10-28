@extends('admin.layouts.app')

@section('title', 'Edit product')
@section('heading', 'Edit product')

@section('content')
    @include('admin.products._form', [
        'product' => $product,
        'categories' => $categories,
        'action' => route('admin.products.update', $product),
        'method' => 'PUT',
        'submitLabel' => 'Save changes',
    ])
@endsection
