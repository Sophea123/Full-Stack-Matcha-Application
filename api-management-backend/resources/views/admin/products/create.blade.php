@extends('admin.layouts.app')

@section('title', 'Add product')
@section('heading', 'Add product')

@section('content')
    @include('admin.products._form', [
        'product' => $product,
        'categories' => $categories,
        'action' => route('admin.products.store'),
        'method' => 'POST',
        'submitLabel' => 'Create product',
    ])
@endsection
