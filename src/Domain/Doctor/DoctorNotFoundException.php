<?php
declare(strict_types=1);

namespace App\Domain\Doctor;

use App\Domain\DomainException\DomainRecordNotFoundException;

class DoctorNotFoundException extends DomainRecordNotFoundException
{
    public $message = 'The doctor you requested does not exist.';
}
