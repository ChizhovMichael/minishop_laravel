<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Http\Controllers\NavigationController;

class ContactEmail extends Mailable
{
    use Queueable, SerializesModels;
    use NavigationController;


    public $contact;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($contact)
    {
        //
        $this->contact = $contact;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        $contacts = collect($this->contacts());

        return $this->from($contacts->get('mailMain')->value, 'Telezapchasti.ru')
                    ->subject('Форма обратной связи со страницы Контакты Telezapchati')
                    ->view('emails.contact');
    }
}
