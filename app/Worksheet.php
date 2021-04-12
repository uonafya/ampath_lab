<?php

namespace App;

use App\BaseModel;

class Worksheet extends BaseModel
{
    protected $dates = ['datecut', 'datereviewed', 'datereviewed2', 'dateuploaded', 'datecancelled', 'daterun', 'kitexpirydate',  'sampleprepexpirydate',  'bulklysisexpirydate',  'controlexpirydate',  'calibratorexpirydate',  'amplificationexpirydate', ];

    // protected $withCount = ['sample'];
    
    // public $timestamps = false;

    public function sample()
    {
    	return $this->hasMany('App\Sample');
    }

    public function runner()
    {
    	return $this->belongsTo('App\User', 'runby');
    }

    public function sorter()
    {
        return $this->belongsTo('App\User', 'sortedby');
    }

    public function bulker()
    {
        return $this->belongsTo('App\User', 'bulkedby');
    }

    public function quoter()
    {
        return $this->belongsTo('App\User', 'alliquotedby');
    }

    public function creator()
    {
    	return $this->belongsTo('App\User', 'createdby');
    }

    public function uploader()
    {
        return $this->belongsTo('App\User', 'uploadedby');
    }

    public function canceller()
    {
        return $this->belongsTo('App\User', 'cancelledby');
    }

    public function reviewer()
    {
        return $this->belongsTo('App\User', 'reviewedby');
    }

    public function reviewer2()
    {
        return $this->belongsTo('App\User', 'reviewedby2');
    }

    public function scopeExisting($query, $createdby, $created_at)
    {
        return $query->where(['createdby' => $createdby, 'created_at' => $created_at]);
    }


    public function getFailedAttribute()
    {
        if(!in_array($this->neg_control_result, [1,6]) || !in_array($this->pos_control_result, [2,6])) return true;
        return false;
    }

}
