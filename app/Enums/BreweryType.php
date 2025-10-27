<?php

namespace App\Enums;

enum BreweryType: string
{
    case Bar = 'bar';
    case Beergarden = 'beergarden';
    case Brewpub = 'brewpub';
    case Cidery = 'cidery';
    case Closed = 'closed';
    case Contract = 'contract';
    case Large = 'large';
    case Micro = 'micro';
    case Nano = 'nano';
    case Planning = 'planning';
    case Proprietor = 'proprietor';
    case Regional = 'regional';
    case Taproom = 'taproom';
    case Unknown = 'unknown';

    // prob should be removed from the dataset...
    case Location = 'location';
}
