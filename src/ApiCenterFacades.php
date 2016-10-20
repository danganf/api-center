<?php

namespace Ufox;

use Illuminate\Support\Facades\Facade;

class ApiCenter extends Facade
{
    protected static function getFacadeAccessor() { return 'ApiCenter'; } // most likely you want MyClass here
}