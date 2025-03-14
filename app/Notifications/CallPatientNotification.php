<?php

namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CallPatientNotification extends Notification
{
    use Queueable;
    protected $room;
    protected $waitingNumber;
    protected $title;
    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($room, $waitingNumber, $title, $message)
    {
        $this->room = $room;
        $this->waitingNumber = $waitingNumber;
        $this->title = $title;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => "LIVE Queue Alert! | " . $this->room->name,
            'message' => $this->message,
            // 'action_url' => env('WEB_CLINICO_URL') . '/patient/profile',
            'type' => 'info'            
        ];        
    }
}
