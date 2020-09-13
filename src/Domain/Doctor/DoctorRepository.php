<?php
declare(strict_types=1);

namespace App\Domain\Doctor;

interface DoctorRepository
{
    /**
     * @return Doctor[]
     */
    public function findAll(): array;

    /**
     * @param int $id
     * @return Doctor
     * @throws DoctorNotFoundException
     */
    public function findDoctorOfId(int $id): Doctor;
}
