@extends('admin.layouts.app')

@section('title', 'Edit account')
@section('heading', 'Edit account')

@section('content')
    @include('admin.users._form', [
        'user' => $user,
        'isSelf' => $isSelf,
        'action' => route('admin.users.update', $user),
        'method' => 'PUT',
        'submitLabel' => 'Save changes',
    ])
@endsection
