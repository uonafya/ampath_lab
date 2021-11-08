<?php

namespace App;

use App\BaseModel;

class DrWarning extends BaseModel
{

    public function sample()
    {
        return $this->belongsTo('App\DrSample', 'sample_id');
    }

    public function warning_code()
    {
        return $this->belongsTo('App\DrWarningCode', 'warning_id');
    }
}
