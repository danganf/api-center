<?php

namespace Ufox;

use Illuminate\Support\Facades\Facade;

class ApiCenterFacades extends Facade
{
    protected static function getFacadeAccessor() { return 'ApiCenter'; } // most likely you want MyClass here
}