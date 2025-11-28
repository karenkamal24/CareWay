<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Notifications\OrderShippedNotification;
use App\Services\FirebaseNotificationService;
use App\Traits\SendMailTrait;
use Illuminate\Support\Facades\Log;

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
                // إرسال إشعار قاعدة البيانات والبريد
                if ($order->user) {
                    $order->user->notify(new OrderShippedNotification($order));

                    // إرسال إشعار FCM مباشر
                    if ($order->user->fcm_token) {
                        try {
                            $firebaseService = app(FirebaseNotificationService::class);
                            $result = $firebaseService->sendToUser(
                                $order->user->fcm_token,
                                'تم شحن طلبك!',
                                "تم شحن طلبك رقم #{$order->id} بنجاح.",
                                [
                                    'type' => 'order_shipped',
                                    'order_id' => (string)$order->id,
                                ]
                            );

                            if ($result) {
                                Log::info("✅ FCM notification sent successfully for order #{$order->id} to user #{$order->user->id}");
                            } else {
                                Log::warning("⚠️ FCM notification failed for order #{$order->id}");
                            }
                        } catch (\Exception $e) {
                            Log::error("❌ Failed to send FCM notification for order #{$order->id}: " . $e->getMessage());
                            Log::error("Exception trace: " . $e->getTraceAsString());
                        }
                    } else {
                        Log::info("ℹ️ User #{$order->user->id} does not have FCM token for order #{$order->id}");
                    }
                }
            }
        });
    }
}
