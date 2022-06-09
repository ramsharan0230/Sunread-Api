<?php

namespace Modules\Sales\Repositories;

use Illuminate\Validation\Rule;
use Modules\Sales\Entities\OrderComment;
use Modules\Core\Repositories\BaseRepository;

class OrderCommentRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new OrderComment();
        $this->model_key = "OrderComment";
        $this->rules = [
            "comment" => "required",
            "order_id" => "required|exists:orders,id",
            "is_customer_notified" => "sometimes|boolean",
            "is_visible_on_storefornt" => "sometimes|boolean",
            "status_flag" => ["sometimes", Rule::in(OrderComment::$status_flags)],
        ];
    }

}
