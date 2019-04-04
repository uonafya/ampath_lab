<?php

namespace App;

use Exception;
use App\BaseModel;
use App\Misc;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use App\Mail\BatchDeletedNotification;

class Batch extends BaseModel
{

    // protected $dates = ['datereceived', 'datedispatchedfromfacility', 'datebatchprinted', 'datedispatched', 'dateindividualresultprinted', 'datemodified', 'dateapproved', 'dateapproved2', 'dateindividualresultprinted', 'datebatchprinted', 'datesynched'];

    // protected $withCount = ['sample'];

    public $keyType = 'string';
    // public $incrementing = 'false';

    /*protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('siteentry', function(Builder $builder){
            $builder->where('site_entry', '!=', 2);
        });
    }*/

    public function tat()
    {
        if(!$this->datereceived) return '';

        $max = date('Y-m-d');
        if($this->batch_complete == 1) $max = $this->datedispatched;
        return Misc::get_days($this->datereceived, $max, false);
    }

    public function full_batch()
    {
        $this->input_complete = 1;
        $this->batch_full = 1;
        $this->pre_update();
    }

    public function premature()
    {
        $this->input_complete = 1;
        $this->pre_update();
    }

    public function outdated()
    {
        $now = \Carbon\Carbon::now();

        if($now->diffInMonths($this->created_at) > 6) return true;
        return false;
    }


	public function sample()
    {
        return $this->hasMany('App\Sample');
    }

    public function facility()
    {
        return $this->belongsTo('App\Facility');
    }

    public function view_facility()
    {
        return $this->belongsTo('App\ViewFacility', 'facility_id');
    }

    public function lab()
    {
        return $this->belongsTo('App\Lab');
    }

    public function facility_lab()
    {
        return $this->belongsTo('App\Facility', 'lab_id');
    }

    public function receiver()
    {
        return $this->belongsTo('App\User', 'received_by');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function setDatedispatchedfromfacilityAttribute($value)
    {
        if($value = '0000-00-00') $this->attributes['datedispatchedfromfacility'] = null;
        else{
            $this->attributes['datedispatchedfromfacility'] = $value;
        }
    }

    public function scopeExisting($query, $facility, $datereceived, $lab)
    {
        if(!$datereceived) return $query->where(['facility_id' => $facility, 'lab_id' => $lab, 'batch_full' => 0])->whereNull('datereceived');
        return $query->where(['facility_id' => $facility, 'datereceived' => $datereceived, 'lab_id' => $lab, 'batch_full' => 0]);
    }

    public function scopeEligible($query, $facility, $datereceived)
    {
        $user = auth()->user();
        $user_id = $user->id ?? 66;
        $today = date('Y-m-d');
        $min_date = date('Y-m-d', strtotime('-4 days'));
        if(!$datereceived){
            return $query->where(['facility_id' => $facility, 'user_id' => $user_id, 'batch_full' => 0, 'batch_complete' => 0])
                    ->whereDate('created_at', $today)->whereNull('datereceived')->whereNull('datedispatched');
        }
        return $query->where(['facility_id' => $facility, 'datereceived' => $datereceived, 'user_id' => $user_id, 'batch_full' => 0, 'batch_complete' => 0])->where('created_at', '>', $min_date)->whereNull('datedispatched');
    }

    public function scopeEditing($query)
    {
        $today = date('Y-m-d');
        return $query->where(['user_id' => auth()->user()->id, 'input_complete' => 0, 'batch_complete' => 0])->whereDate('created_at', $today);
    }

    /**
     * Get the batch's delete button
     *
     * @return string
     */
    public function getDeleteButtonAttribute()
    {
        // $min_time = strtotime("-1 month");
        $min_time = strtotime("-10 days");
        $created_at = strtotime($this->created_at);
        if($this->site_entry == 1 && !$this->datereceived && !$this->datedispatched && $created_at < $min_time && $this->batch_complete == 0){
            return "| <form method='post' action='" . url('batch/' . $this->id) . "' onSubmit=\"return confirm('Are you sure you want to delete the following batch?');\">
                    " . csrf_field() . " 
                    <input name='_method' type='hidden' value='DELETE'>
                    <button type='submit' class='btn btn-xs btn-primary'>Delete</button>
                </form>
             ";
        }
        else{
            return false;
        }
    }

    public function batch_delete()
    {
        if(!$this->delete_button) abort(409, "Batch number {$this->id} is not eligible for deletion.");
        if(env('APP_LAB') != 4){
            $comm = new BatchDeletedNotification($this);
            $bcc_array = ['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke'];
            try {
                if($this->facility->email_array) Mail::to($this->facility->email_array)->bcc($bcc_array)->send($comm);
            } catch (Exception $e) {
                
            }
            
        }
        \App\Sample::where(['batch_id' => $this->id])->delete();
        $this->delete();
        session(['toast_message' => "Batch {$this->id} has been deleted."]);
        return true;
    }

    public function transfer_samples($sample_ids, $submit_type)
    {     
        if(!$sample_ids){
            session(['toast_error' => 1, 'toast_message' => "No samples have been selected."]);
            return 'back';         
        }

        $new_batch = new \App\Batch;
        $new_batch->fill($this->replicate(['synched', 'batch_full', 'national_batch_id', 'sent_email', 'dateindividualresultprinted', 'datebatchprinted', 'dateemailsent'])->toArray());
        if($submit_type != "new_facility"){
            $new_batch->id = (int) $this->id + 0.5;
            $new_id = $this->id + 0.5;
            $existing_batch = \App\Batch::find($new_id);
            if($existing_batch){
                session(['toast_message' => "Batch {$new_id} already exists.", 'toast_error' => 1]);
                return 'back';         
            }
            if($new_batch->id == floor($new_batch->id)){
                session(['toast_message' => "The batch {$this->id} cannot have its samples transferred.", 'toast_error' => 1]);
                return 'back';         
            }    
        }
        $new_batch->created_at = $this->created_at;
        $new_batch->save();

        if($submit_type == "new_facility") $new_id = $new_batch->id;

        $count = 0;
        $s;

        $has_received_status = false;

        foreach ($sample_ids as $key => $id) {
            $sample = \App\Sample::find($id);
            if($submit_type == "new_batch" && ($sample->receivedstatus == 2 || ($sample->repeatt == 0 && $sample->result ))){
                continue;
            }else{
                $parent = $sample->parent;
                if($parent){
                    $parent->batch_id = $new_id;
                    $parent->pre_update();

                    $children = $parent->children;
                    if($children){
                        foreach ($children as $child) {
                            $child->batch_id = $new_id;
                            $child->pre_update();
                        }                        
                    }
                }
            }
            if($sample->result && $submit_type == "new_batch") continue;
            if($sample->receivedstatus) $has_received_status = true;
            $sample->batch_id = $new_id;
            $sample->pre_update();
            $s = $sample;
            $count++;
        }
        // $s = $new_batch->sample->first();

        if(!$has_received_status){
            \App\Batch::where(['id' => $new_id])->update(['datereceived' => null, 'received_by' => null]);
        }

        Misc::check_batch($this->id);
        Misc::check_batch($new_id);

        session(['toast_message' => "The batch {$this->id} has had {$count} samples transferred to  batch {$new_id}."]);
        if($submit_type == "new_facility"){
            session(['toast_message' => "The batch {$this->id} has had {$count} samples transferred to  batch {$new_id}. Update the facility on this form to complete the process."]);
            // return redirect('sample/' . $s->id . '/edit');
            return 'sample/' . $s->id . '/edit';
        }
        // return redirect('batch/' . $new_id);
        return 'batch/' . $new_id;
    }
    
}
