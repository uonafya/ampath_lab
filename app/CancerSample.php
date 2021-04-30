<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CancerSample extends BaseModel
{
    public function patient()
    {
    	return $this->belongsTo(CancerPatient::class, 'patient_id', 'id');
    }

    public function facility()
    {
    	return $this->belongsTo('App\Facility');
    }

    public function facility_lab()
    {
        return $this->belongsTo(Facility::class, 'lab_id', 'id');
    }

    public function worksheet()
    {
        return $this->belongsTo('App\CancerWorksheet');
    }

    // Parent sample
    public function parent()
    {
        return $this->belongsTo('App\CancerSample', 'parentid');
    }

    // Child samples
    public function child()
    {
        return $this->hasMany('App\CancerSample', 'parentid');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'createdby');
    }

    public function canceller()
    {
        return $this->belongsTo('App\User', 'cancelledby');
    }

    public function approver()
    {
        return $this->belongsTo('App\User', 'approvedby');
    }

    public function final_approver()
    {
        return $this->belongsTo('App\User', 'approvedby2');
    }


    public function scopeRuns($query, $sample)
    {
        if($sample->parentid == 0){
            return $query->whereRaw("parentid = {$sample->id} or id = {$sample->id}")->orderBy('run', 'asc');
        }
        else{
            return $query->whereRaw("parentid = {$sample->parentid} or id = {$sample->parentid}")->orderBy('run', 'asc');
        }
    }

    /**
     * Get the sample's result name
     *
     * @return string
     */
    public function getResultNameAttribute()
    {
        if($this->result == 1){ return "Negative"; }
        else if($this->result == 2){ return "Positive"; }
        else if($this->result == 3){ return "Failed"; }
        else if($this->result == 5){ return "Collect New Sample"; }
        else{ return ""; }
    }
    public function remove_rerun()
    {
        if($this->parentid == 0) $this->remove_child();
        else{
            $this->remove_sibling();
        }
    }

    public function remove_child()
    {
        $children = $this->child;

        foreach ($children as $s) {
            $s->delete();
        }

        $this->repeatt=0;
        $this->save();
    }

    public function remove_sibling()
    {
        $parent = $this->parent;
        $children = $parent->child;

        foreach ($children as $s) {
            if($s->run > $this->run) $s->delete();            
        }

        $this->repeatt=0;
        $this->save();
    }

    public function setTATs()
    {        
        $this->setTAT1();
        // $this->setTAT2();
        // $this->setTAT3();
        // $this->setTAT4();
        dd($this);
        return $this->save();
    }

    private function setTAT1()
    {
        if (null !== $this->datecollected && null !== $this->datereceived) 
            $this->tat1 = date_diff(date_create($this->datereceived), date_create($this->datecollected));
    }

    private function setTAT2()
    {
        if (null !== $this->datetested && null !== $this->datereceived) 
            $this->tat2 = date_diff(date_create($this->datetested), date_create($this->datereceived));
    }

    private function setTAT3()
    {
        if (null !== $this->datetested && null !== $this->datedispatched) 
            $this->tat3 = date_diff(date_create($this->datedispatched), date_create($this->datetested));
    }

    private function setTAT4()
    {
        if (null !== $this->datecollected && null !== $this->datedispatched) 
            $this->tat3 = date_diff(date_create($this->datedispatched), date_create($this->datecollected));
    }
}
