<?php

namespace App\Notifications\Messages;

class FirebaseMessage
{
    public $title;
    public $body;
    public $data = [];

    /**
     * إنشاء رسالة Firebase جديدة
     */
    public static function create($title = null, $body = null)
    {
        $message = new static();
        $message->title = $title;
        $message->body = $body;
        return $message;
    }

    /**
     * تعيين العنوان
     */
    public function title($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * تعيين النص
     */
    public function body($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * إضافة بيانات إضافية
     */
    public function data(array $data)
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * تحويل إلى مصفوفة
     */
    public function toArray()
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
        ];
    }
}


