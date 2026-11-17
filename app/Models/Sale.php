<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Sale extends Model { protected $fillable=['invoice_number','product_id','customer_id','salesperson_id','selling_price','sold_at','payment_status','notes']; protected $casts=['sold_at'=>'date','selling_price'=>'integer']; public function product(){return $this->belongsTo(Product::class);} public function customer(){return $this->belongsTo(User::class,'customer_id');} public function salesperson(){return $this->belongsTo(User::class,'salesperson_id');} public function warranty(){return $this->hasOne(Warranty::class);} }
