<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Models\TestResult;
use App\Notifications\Channels\FirebaseChannel;
use App\Notifications\Contracts\FirebaseNotification;

class TestResultNotification extends Notification implements FirebaseNotification
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
        // فقط البريد - Firebase يتم إرساله مباشرة في TestResultResource
        return ['mail'];
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

    /**
     * إشعار Firebase Cloud Messaging
     */
    public function toFirebase($notifiable): array
    {
        return [
            'title' => 'نتيجة الفحص جاهزة',
            'body' => 'نتيجة فحصك أصبحت جاهزة الآن. يمكنك الاطلاع عليها.',
            'data' => [
                'type' => 'test_result_ready',
                'test_id' => $this->testResult->id,
                'message' => 'Your test result is ready!',
            ],
        ];
    }
}
