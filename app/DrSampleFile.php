<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DrSampleFile extends BaseModel
{

    public function sample()
    {
        return $this->belongsTo('App\DrSample', 'sample_id');
    }

    public function getPrimerNameAttribute()
    {
    	return 'Seq' . $this->primer;
    }
}
