<?php

namespace App;

class StripeEventParser
{
    private $stripeEvent;
    private $resource;
    private $customerId;

    public function __construct($stripeEvent)
    {
        $this->stripeEvent = $stripeEvent;
    }

    public function parseEvent()
    {
        $resource = $this->determineResource();
        $this->getCustomerIdFromResource($resource);
        return $this;
    }

    public static function parse($stripeEvent)
    {
        $parser = new self($stripeEvent);
        return $parser->parseEvent();
    }

    public function getCustomerId()
    {
        return $this->customerId;
    }

    private function determineResource()
    {

        $this->resource = $this->stripeEvent->data->object->object;
        return $this->resource;
    }

    private function getCustomerIdFromResource($resource)
    {
        switch ($resource) {
            case("subscription"):
            case("invoice"):
            case("charge"):
            case('card'):
            case('discount'):
            case('invoiceitem'):
            case('order'):
                $this->customerId = $this->stripeEvent->data->object->customer;
                break;
            case("customer"):
                $this->customerId = $this->stripeEvent->data->object->id;
                break;

            default:
                $this->customerId = null;
                break;
        }
        return $this->customerId;
    }
}