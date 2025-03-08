<?php

namespace App\Services\Traits;

use App\Exceptions\ValidationException;
use Illuminate\Support\Facades\Validator;

trait ValidatesData
{
    protected function validate(array $data, array $rules, array $messages = [])
    {
        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }

        return $data;
    }
} 