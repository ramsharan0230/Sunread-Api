<?php

namespace Modules\Customer\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewCustomerNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The customer instance.
     */
    public $customer;

    /**
     * The password variable
     * @var string
     */
    public $password;

    /**
     * Create a new message instance.
     * @param $customer
     * @param $password
     */
    public function __construct($customer, $password)
    {
        $this->customer = $customer;
        $this->password = $password;
    }


    //TODO::use trans for emails
    public function build()
    {
        return $this->from(getenv('MAIL_FROM_ADDRESS') ,getenv('MAIL_FROM_ADDRESS'))
            ->to($this->customer->email)
            ->subject("New Customer Registration")
            ->markdown('customer::emails.new-customer')
            ->with(['customer' => $this->customer, 'password' => $this->password]);
    }
}
