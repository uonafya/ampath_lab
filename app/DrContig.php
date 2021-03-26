<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DrContig extends BaseModel
{
	
    public function sample()
    {
        return $this->belongsTo('App\DrSample', 'sample_id');
    }

    public function warning()
    {
        return $this->hasMany('App\DrContigWarning', 'contig_id');
    }
}
