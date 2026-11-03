<?php

namespace PlacetoPay\PlacetoPaySdk\Enums;

enum TransactionAction: string
{
    case CHECKOUT = 'checkout';
    case REAUTHORIZATION = 'reauthorization';
    case REVERSE = 'reverse';
    case CONFIRM = 'confirm';
    case CANCEL = 'cancel';
}
