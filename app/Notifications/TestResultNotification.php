<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Models\TestResult;  // نموذج للاختبار (يمكنك تخصيصه حسب حاجتك)

class TestResultNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $testResult;

    // تمرير بيانات الاختبار في الكائن
    public function __construct(TestResult $testResult)
    {
        $this->testResult = $testResult;
    }

    // القنوات التي سيتم إرسال الإشعار من خلالها
    public function via($notifiable)
    {
        return ['mail', 'broadcast'];
    }

    // إرسال إشعار عبر البريد الإلكتروني
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Test Result Ready')
            ->line('Your test result is now available.')
            ->line('Thank you for using our service!')
            ->line('Lab CareWay');
    }

   
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'message' => 'Your test result is ready!',
            'test_id' => $this->testResult->id,
        ]);
    }

  
    public function broadcastOn()
    {
        return ['private-test-results.' . $this->testResult->id];  // قناة خاصة حسب ID الاختبار
    }
    

   
    public function broadcastAs()
    {
        return 'test.result.ready';
    }
}
