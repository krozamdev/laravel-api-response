<?php
namespace KrozamDev\LaravelApiResponse\ApiControllers;

use App\Http\Controllers\Controller;
use KrozamDev\LaravelApiResponse\ApiResponse;

class ApiExtendsController extends Controller {
    protected $api;
    public function __construct(ApiResponse $api)
    {
        $this->api = $api;
        $this->api->debug(true);
    }
}