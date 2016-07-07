<?php

namespace App\Http\Controllers;

use App\Event;
use App\StripeEventParser;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class WebhookHandlerController extends Controller
{
    public function handle(Request $request)
    {
        $rawBody = $request->getContent();
        $postedEvent = json_decode($rawBody);
        $eventType = $postedEvent->type;
        $eventDate = isset($postedEvent->created) ? Carbon::createFromTimestamp($postedEvent->created) : null;

        try {
            // Create event asap to attempt to block duplicate requests
            $event = new Event();
            $event->event_id = $postedEvent->id;
            $event->payload = $rawBody;
            $event->event_type = $eventType;
            $event->event_at = $eventDate;
            $event->save();

            $parser = StripeEventParser::parse($postedEvent);
            $event->customer_id = $parser->getCustomerId();

            // Save off any additional event changes
            $event->save();

        } catch (QueryException $e) {
            // Event may already exist. Check error message to see if duplicate. If not, rethrow error
            if (!str_contains($e->getMessage(), 'Duplicate entry')) {
                throw $e;
            }
        }

    }
}