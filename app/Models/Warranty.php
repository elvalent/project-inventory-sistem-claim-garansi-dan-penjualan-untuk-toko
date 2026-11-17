<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Warranty extends Model { protected $fillable=['sale_id','warranty_code','start_date','end_date','status','claim_description','admin_notes']; protected $casts=['start_date'=>'date','end_date'=>'date']; public function sale(){return $this->belongsTo(Sale::class);} }
