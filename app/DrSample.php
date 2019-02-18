<?php

namespace App;

use App\BaseModel;

class DrSample extends BaseModel
{

    public function patient()
    {
        return $this->belongsTo('App\Viralpatient', 'patient_id');
    }

    public function worksheet()
    {
        return $this->belongsTo('App\DrWorksheet', 'worksheet_id');
    }

    public function extraction_worksheet()
    {
        return $this->belongsTo('App\DrExtractionWorksheet', 'extraction_worksheet_id');
    }

    public function receiver()
    {
        return $this->belongsTo('App\User', 'received_by');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function facility()
    {
        return $this->belongsTo('App\Facility');
    }

    public function view_facility()
    {
        return $this->belongsTo('App\ViewFacility', 'facility_id');
    }


    // Parent sample
    public function parent()
    {
        return $this->belongsTo('App\DrSample', 'parentid');
    }

    // Child samples
    public function child()
    {
        return $this->hasMany('App\DrSample', 'parentid');
    }



    public function warning()
    {
        return $this->hasMany('App\DrWarning', 'sample_id');
    }

    public function dr_call()
    {
        return $this->hasMany('App\DrCall', 'sample_id');
    }

    public function genotype()
    {
        return $this->hasMany('App\DrGenotype', 'sample_id');
    }

    /**
     * Get if rerun has been created
     *
     * @return string
     */
    public function getHasRerunAttribute()
    {
        if($this->parentid == 0){
            $child_count = $this->child->count();
            if($child_count) return true;
        }
        else{
            $run = $this->run + 1;
            $child = \App\DrSample::where(['parentid' => $this->parentid, 'run' => $run])->first();
            if($child) return true;
        }
        return false;
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




    // mid being my id
    // Used when sending samples to sanger
    public function getMidAttribute()
    {
        return env('DR_PREFIX') . $this->id;
    }

    public function getChromatogramLinkAttribute()
    {
        $ui_url = 'http://sangelamerkel.exatype.co.za';
        return $ui_url . $this->chromatogram_url;
    }

    public function getViewChromatogramAttribute()
    {
        $full_link = "<a href='{$this->chromatogram_link}' target='_blank'> View Chromatogram </a>";
        return $full_link;
    }



    public function setArvToxicitiesAttribute($value)
    {
        $val = '[';
        foreach ($value as $v) {
            $val .= "'" . $v . "',";
        }
        $this->attributes['arv_toxicities'] = $val . ']';
    }

    public function getArvToxicitiesArrayAttribute()
    {
        return eval("return " . $this->arv_toxicities . ";");
    }

    public function setClinicalIndicationsAttribute($value)
    {
        $val = '[';
        foreach ($value as $v) {
            $val .= "'" . $v . "',";
        }
        $this->attributes['clinical_indications'] = $val . ']';
    }

    public function getClinicalIndicationsArrayAttribute()
    {
        return eval("return " . $this->clinical_indications . ";");
    }

    public function setOtherMedicationsAttribute($value)
    {
        $val = '[';
        foreach ($value as $v) {
            $val .= "'" . $v . "',";
        }
        $this->attributes['other_medications'] = $val . ']';
    }

    public function getOtherMedicationsArrayAttribute()
    {
        return eval("return " . $this->other_medications . ";");   
    }

    public function getOtherMedicationsStringAttribute()
    {
        $my_array = $this->other_medications_array;
        $str = '';

        if(is_array($my_array)){
            foreach ($my_array as $value) {
                if(!is_numeric($value)) $str .= trim($value) . ', ';
            }
        }

        return $str;   
    }

    public function get_primers($date_created=null, $row, $column = 1)
    {
        $primers = ['F1', 'F2', 'F3', 'R1', 'R2', 'R3'];
        $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        if(!$date_created) return null;

        $str = '';

        foreach ($primers as $key => $value) {
            $loc = $key+1;
            if($column == 2) $loc += 6;

            $str .= '<td>';

            
        }
    }

    public function create_rerun($data=null)
    {
        $fields = \App\Lookup::viralsamples_arrays();

        if(!$this->has_rerun){
            $child = new DrSample;
            $child->fill($original->only($fields['dr_sample_rerun']));                
            $child->run++;
            if($child->parentid == 0) $child->parentid = $this->id;
            $child->save();

            if($data) $this->fill($data);
            $this->collect_new_sample = 0;
            $this->repeatt = 1;
            $this->pre_update();
        }            
    }


    /**
     * Get the patient's gender
     *
     * @return string
     */
    public function getControlTypeAttribute()
    {
        if($this->control == 1){ return "Negative Control"; }
        else if($this->control == 2){ return "Positive Control"; }
        else{ return "Normal Sample"; }
    }







}
