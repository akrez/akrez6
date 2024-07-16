<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Supports\ResponseBuilder status(int $status);
 * @method static int getStatus();
 * @method static \App\Supports\ResponseBuilder message(?string $message);
 * @method static ?string getMessage();
 * @method static \App\Supports\ResponseBuilder data(mixed $data);
 * @method static mixed getData();
 * @method static \App\Supports\ResponseBuilder errors(mixed $errors);
 * @method static mixed getErrors();
 * @method static \Illuminate\Http\Response toResponse(\Illuminate\Http\Request $request);
 */
class ResponseBuilder extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'ResponseBuilder';
    }
}
