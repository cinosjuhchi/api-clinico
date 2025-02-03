<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportBugMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reportBug;
    public $subject;
    public $message;

    /**
     * Create a new message instance.
     *
     * @param $reportBug
     * @param $subject
     * @param $message
     */
    public function __construct($reportBug, $subject, $message)
    {
        $this->reportBug = $reportBug;
        $this->subject = $subject;
        $this->message = $message;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
                    ->view('emails.report_bug')
                    ->with([
                        'reportBug' => $this->reportBug,
                        'emailMessage' => $this->message,
                    ]);
    }
}
