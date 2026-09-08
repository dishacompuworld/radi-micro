<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PrtgAlert extends Notification
{
    use Queueable;
    
    protected $alertData;
    
    public function __construct(array $alertData)
    {
        $this->alertData = $alertData;
    }
    
    public function via($notifiable)
    {
        return ['mail', 'database']; // Choose your channels
    }
    
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->error() // Makes the email look like an alert
                    ->subject("PRTG Alert: {$this->alertData['status']} on {$this->alertData['device']}")
                    ->line("Sensor: {$this->alertData['sensor']}")
                    ->line("Status: {$this->alertData['status']}")
                    ->line('Message: '.($this->alertData['message'] ?? ''))
                    ->line('Time: '.($this->alertData['date'] ?? ''));
    }
    
    public function toArray($notifiable)
    {
        return $this->alertData;
    }
}