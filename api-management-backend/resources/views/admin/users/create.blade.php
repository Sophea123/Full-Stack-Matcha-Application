@extends('admin.layouts.app')

@section('title', 'Add account')
@section('heading', 'Add account')

@section('content')
    @include('admin.users._form', [
        'user' => $user,
        'isSelf' => $isSelf,
        'action' => route('admin.users.store'),
        'method' => 'POST',
        'submitLabel' => 'Create account',
    ])
@endsection
