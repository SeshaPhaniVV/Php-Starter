<?php
declare(strict_types=1);

namespace App\Application\Actions\Doctor;

use App\Application\Actions\Action;
use App\Domain\Doctor\DoctorRepository;
use Psr\Log\LoggerInterface;

abstract class DoctorAction extends Action
{
    /**
     * @var DoctorRepository
     */
    protected $doctorRepository;

    /**
     * @param LoggerInterface $logger
     * @param DoctorRepository $doctorRepository
     */
    public function __construct(LoggerInterface $logger, DoctorRepository $doctorRepository)
    {
        parent::__construct($logger);
        $this->doctorRepository = $doctorRepository;
    }
}
