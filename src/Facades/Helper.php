<?php

namespace Kraenkvisuell\StatamicHelpers\Facades;

use Illuminate\Support\Facades\Facade;

class Helper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'helper';
    }
}
