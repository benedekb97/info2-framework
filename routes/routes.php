<?php

namespace App;

Router::get('', 'HomeController@index', 'index');

Router::post('login', 'AuthController@login', 'auth.login');
