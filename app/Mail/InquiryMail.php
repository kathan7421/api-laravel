<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $inquiry;
    public $companies;

    public function __construct($inquiry, $companies)
    {
        $this->inquiry = $inquiry;
        $this->companies = $companies;
    }

    public function build()
    {
        return $this->view('emails.inquiry')
                    ->with([
                        'inquiry' => $this->inquiry,
                        'companies' => $this->companies
                    ]);
    }
}
