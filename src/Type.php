<?php
namespace AdminGen;

use PhalconX\Enums\Enum;

class Type extends Enum
{
    const MODEL = 'model';

    const FORM = 'form';

    const CONTROLLER = 'controller';

    const VIEW = 'view';

    protected static $PROPERTIES = [
        'namespace' => [
            self::MODEL => 'Models',
            self::FORM => 'Forms',
            self::CONTROLLER => 'Controllers'
        ],
    ];
}
