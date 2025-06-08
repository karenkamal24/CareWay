<?php

use App\Helpers\ApiResponseHelper;

if (!function_exists('apiResponse')) {
    function apiResponse(): ApiResponseHelper
    {
        return new ApiResponseHelper();
    }
}
