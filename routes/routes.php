<?php

namespace App;

Router::get('', 'HomeController@index', 'index');

Router::post('login', 'AuthController@login', 'auth.login');
Router::get('logout', 'AuthController@logout', 'auth.logout');