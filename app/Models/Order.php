<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Notifications\OrderShippedNotification;
use App\Traits\SendMailTrait;

class Order extends Model
{  use SendMailTrait;
    use HasFactory,Notifiable;
   
    protected $fillable = ['user_id', 'name', 'phone', 'latitude', 'longitude', 'address', 'delivery_zone_id', 'total_price', 'payment_method', 'paymob_order_id', 'status', 'payment_state' ,];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryZone()
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    protected static function boot()
    {
        parent::boot();

        static::updated(function ($order) {
            if ($order->isDirty('status') && $order->status == 'shipped') {
                $order->user->notify(new OrderShippedNotification($order));
            }
        });
    }
}
