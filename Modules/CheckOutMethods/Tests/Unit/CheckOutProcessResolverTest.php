<?php

namespace Modules\CheckOutMethods\Tests\Unit;

use Illuminate\Support\Arr;
use Modules\CheckOutMethods\Services\CheckOutProcessResolver;
use Modules\Core\Tests\BaseUnitTestCase;

class CheckOutProcessResolverTest extends BaseUnitTestCase
{
    protected string $class;
    protected object $checkout_process_resolver;
    protected array $class_attributes;
    protected object $request;

    public function setUp(): void
    {
        parent::setUp();
        $this->class = CheckOutProcessResolver::class;
        $this->class_attributes = [
            "request",
            "method_data",
            "custom_checkout_handler"
        ];
        $request = $this->createRequest();
        $this->checkout_process_resolver = new CheckOutProcessResolver($request);
    }

    public function test_class_attributes_should_be_initilized(): void
    {
        foreach ($this->class_attributes as $attribute) $this->assertClassHasAttribute($attribute, $this->class);
    }

    public function test_class_can_initilize_method_should_return_valid_data(): void
    {
        $resolver = $this->checkout_process_resolver;
        $value = Arr::random(["flat_rate", "free_shipping"]);
        $this->assertIsBool($resolver->can_initilize($value, "delivery_methods"));
    }

    public function test_class_get_checkout_methods_method_should_return_valid_data(): void
    {
        $resolver = $this->checkout_process_resolver;
        $data = $resolver->getCheckOutMethods();
        foreach (["delivery_methods", "payment_methods"] as $key) $this->assertArrayHasKey($key, $data);
    }
}
