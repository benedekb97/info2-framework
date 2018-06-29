@extends('layouts.fasz');

@section('title') Info2 test @endsection

@section('body')
    @if(!Auth::check())
    <form action="{{ Router::getLink('auth.login'); }}" method="POST">
        <input type="text" name="email" placeholder="Email">
        <input type="password" name="password" placeholder="Password">
        <input type="submit" value="Login">
    </form>
    @else
    <a href="{{ Router::getLink('auth.logout'); }}">Log out</a>
    @endif

    {{ Auth::user() }}
@endsection