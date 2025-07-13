<?php

namespace App\Notifications;


use App\AramiscHomework;
use App\AramiscNotification;
use Illuminate\Bus\Queueable;
use App\Services\FcmMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class FlutterAppNotification extends Notification
{
    use Queueable;

    private $aramisc_notification;
    private $title;

  
   public function __construct(AramiscNotification $aramisc_notification,$title)
    {
        $this->aramisc_notification = $aramisc_notification;
        $this->title = $title;
    }

   
    public function via($notifiable)
    {
        return ['fcm'];
    }

    public function toFcm($notifiable)
    {
        $message = new FcmMessage();
        $notification = [
            'title' => $this->title,
            'body' => $this->aramisc_notification->message,
        ];
        $data = [
            'click_action' => "FLUTTER_NOTIFICATION_CLICK",
            'id' => 1,
            'status' => 'done',
            'message' => $notification,
            "image" => "https://freeschoolsoftware.in/spn4/aramiscdu/v7.0.1/public/uploads/settings/logo.png"
        ];
      
        $message->content($notification)
                ->data($data)
                ->priority(FcmMessage::PRIORITY_HIGH); // Optional - Default is 'normal'.
        return $message;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
