<?php

namespace Modules\Notification\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Modules\Notification\Emails\NotificationMail;
use Modules\Notification\Facades\NotificationLog;
use Modules\Notification\Traits\EmailNotification;
use Modules\Product\Repositories\StoreFront\ProductRepository;
use Modules\Sales\Repositories\OrderAddressRepository;
use Modules\Sales\Repositories\OrderCommentRepository;
use Modules\Sales\Repositories\StoreFront\OrderItemRepository;
use Modules\Sales\Repositories\StoreFront\OrderRepository;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, EmailNotification;

    public string $append_data;
    public object $entity;
    public string $event;
    public $orderRepository;
    public $orderItemRepository;
    public $productRepository;
    public $orderAddressRepository;
    public $orderCommentRepository;

    public function __construct(
        object $entity,
        string $event,
        string $append_data = ""
    ) {
        $this->event = $event;
        $this->entity = $entity;
        $this->append_data = $append_data;
        $this->orderRepository = new OrderRepository();
        $this->orderItemRepository = new OrderItemRepository();
        $this->productRepository = new ProductRepository();
        $this->orderAddressRepository = new OrderAddressRepository();
        $this->orderCommentRepository = new OrderCommentRepository();
    }

    /**
     * send email through follwing events
     */
    public function handle(): void
    {
        /** get data from various content of email templates */
        $data = $this->getData($this->entity, $this->event, $this->append_data);

        /** Send Email  */
        Mail::to($data->to_email)->send(new NotificationMail($data->content, $data->subject, $data->sender_name, $data->sender_email, $data->style));

        /** save email notification logs */
        $logs = [
            "name" => $this->event,
            "subject" => $data->subject,
            "html_content" => $data->content,
            "recipient_email_address" => $data->to_email,
            "email_template_id" => $data->template_id,
            "email_template_code" => $this->event,
        ];

        if( count(Mail::failures()) > 0 ) {
            NotificationLog::log($logs, false);
        }
        else {
            NotificationLog::log($logs, true);
        }
    }
}
