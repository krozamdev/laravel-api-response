<?php
namespace KrozamDev\LaravelApiResponse\ApiControllers;

use KrozamDev\LaravelApiResponse\ApiResponse;

class ApiController extends ApiExtendsController {
    public function __construct(ApiResponse $api)
    {
        parent::__construct($api);
    }
}