<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctor;

use App\Domain\Doctor\Doctor;
use App\Domain\Doctor\DoctorNotFoundException;
use App\Domain\Doctor\DoctorRepository;

class InMemoryDoctorRepository implements DoctorRepository
{
    /**
     * @var Doctor[]
     */
    private $doctors;

    /**
     * InMemoryDoctorRepository constructor.
     *
     * @param array|null $doctors
     */
    public function __construct(array $doctors = null)
    {
        $this->doctors = $doctors ?? [
                1 => new Doctor(1, 'bill.gates', 'Bill', 'Gates'),
                2 => new Doctor(2, 'steve.jobs', 'Steve', 'Jobs'),
                3 => new Doctor(3, 'mark.zuckerberg', 'Mark', 'Zuckerberg'),
                4 => new Doctor(4, 'evan.spiegel', 'Evan', 'Spiegel'),
                5 => new Doctor(5, 'jack.dorsey', 'Jack', 'Dorsey'),
            ];
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return array_values($this->doctors);
    }

    /**
     * {@inheritdoc}
     */
    public function findDoctorOfId(int $id): Doctor
    {
        if (!isset($this->doctors[$id])) {
            throw new DoctorNotFoundException();
        }

        return $this->doctors[$id];
    }
}
