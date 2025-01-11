<?php

namespace App\Enums;

enum BreweryType: string
{
    case Micro = 'micro';
    case Nano = 'nano';
    case Regional = 'regional';
    case Brewpub = 'brewpub';
    case Large = 'large';
    case Planning = 'planning';
    case Bar = 'bar';
    case Contract = 'contract';
    case Proprietor = 'proprietor';
    case Closed = 'closed';
    case Unknown = 'unknown';
}
