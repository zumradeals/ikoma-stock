<?php

namespace App\Enums;

use App\Enums\Concerns\EnumValues;

enum ReminderChannel: string
{
    use EnumValues;

    case WHATSAPP = 'WHATSAPP';
    case CALL = 'CALL';
    case SMS = 'SMS';
    case EMAIL = 'EMAIL';
    case VISIT = 'VISIT';
}
