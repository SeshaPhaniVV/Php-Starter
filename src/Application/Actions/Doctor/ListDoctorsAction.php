<?php
declare(strict_types=1);

namespace App\Application\Actions\Doctor;

use App\Domain\Doctor\DoctorRepository;
use DateInterval;
use DateTime;
use Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Database\Capsule\Manager as DbManager;
use Psr\Log\LoggerInterface;
use Redis;

class ListDoctorsAction extends DoctorAction
{
    protected $queue = [];

    protected $doctor_patient_map = [];
    protected $patients = 0;
    /**
     * @var Redis
     */
    private $redis;

    /** @var DbManager */
    private $db;

    public function __construct(LoggerInterface $logger, DoctorRepository $doctorRepository, DbManager $db)
    {
        parent::__construct($logger, $doctorRepository);
        $this->patients  = [];

        $this->db = $db;

        $res = $db->table('test-table')->get();

        print_r($res);

        $this->redis = new Redis();
        $this->redis->connect('redis', 6379);
        $doctors = $this->redis->get('doctors');
        $patients_count = $this->redis->get('patients');

        if (is_bool($patients_count)) {
            $this->redis->set('patients', 0);
        }

        $this->patients = (int) $this->redis->get('patients');

        if(!is_bool($doctors)) $this->doctor_patient_map = json_decode($doctors, true);

        if (empty($this->doctor_patient_map)) {
            for ($i = 0; $i < 10; $i++) {
                $this->doctor_patient_map[$i] = [];
            }
            $this->redis->set('doctors', json_encode($this->doctor_patient_map));
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function action(): Response
    {
        $patientId = $this->generateNextPatientId();

        $doctor_id = $this->getNextFreeDoctorId();

        if (!is_null($doctor_id) && !is_null($this->doctor_patient_map[$doctor_id])) {
            $now = new DateTime();
            $next_free_time = $now->add(new DateInterval('PT' . 30 . 'M'));
            $this->doctor_patient_map[$doctor_id][] = ['patient_id' => $patientId, 'end_time' => $next_free_time];
            $this->updateRedis();
            return $this->respondWithData(['wait_time' => 0]);
        }

        $doctor_id = $this->getDoctorForPatient($patientId);
        $last_visit = last($this->doctor_patient_map[$doctor_id]);
        $expected_doctor_free_time = $last_visit['end_time'];

        $next_free_time = (new DateTime($expected_doctor_free_time['date']))->add(new DateInterval('PT' . 30 . 'M'));
        $this->doctor_patient_map[$doctor_id][] = ['patient_id' => $patientId, 'end_time' => $next_free_time];
        $this->updateRedis();

        $now = new DateTime();
        $diff = $now->diff((new DateTime($expected_doctor_free_time['date'])));

        $hours   = $diff->format('%h');
        $minutes = $diff->format('%i');
        $final =  ($hours * 60) + $minutes;

        return $this->respondWithData(['wait_time' => $final]);
    }

    private function updateRedis() {
        $this->redis->set('doctors', json_encode($this->doctor_patient_map));
        $this->redis->set('patients', $this->patients + 1);
    }

    private function getNextFreeDoctorId() {
        $i =0;
        do {
          $doctor = $this->doctor_patient_map[$i];
          $i++;
          if ($i >= sizeof($this->doctor_patient_map)) break;
        } while (!empty($doctor));

        return empty($doctor) ? $i - 1 : null;
    }

    private function getDoctorForPatient(int $patientId)
    {
        return $patientId % 10;
    }

    private function generateNextPatientId() {
        return $this->patients;
    }
}
