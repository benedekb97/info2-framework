@extends('layouts.fasz');

@section('title') Info2 test @endsection

@section('body')
    <div class="container">
        <div class="fasz">
            <b>Szeretem a kukkert</b>
        </div>
    </div>
    <form action="{{ Router::getLink('auth.login'); }}" method="POST">
        <input type="text" name="email" placeholder="Email">
        <input type="password" name="password" placeholder="Password">
        <input type="submit" value="Login">
    </form>

{{ "fasz" }}
@endsection