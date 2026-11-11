<?php

namespace Database\Seeders;

use App\Models\Litter;
use App\Models\MedicalLog;
use App\Models\Medication;
use App\Models\Pig;
use Illuminate\Database\Seeder;

class MedicalLogSeeder extends Seeder
{
    public function run(): void
    {
        if (MedicalLog::query()->exists()) {
            return;
        }

        $pigs = Pig::query()->pluck('id', 'tag_number');
        $meds = Medication::query()->pluck('id', 'name');

        $nursingLitter = Litter::query()
            ->whereHas('farrowing.breeding', fn ($query) => $query->where('sow_id', $pigs['SOW-005'] ?? 0))
            ->first();

        $weanedLitter = Litter::query()
            ->whereHas('farrowing.breeding', fn ($query) => $query->where('sow_id', $pigs['SOW-006'] ?? 0))
            ->first();

        // Iron injection already given to the nursing litter (one-shot, completed).
        if ($nursingLitter) {
            MedicalLog::create([
                'treatable_type' => Litter::class,
                'treatable_id' => $nursingLitter->id,
                'medication_id' => $meds['Iron Dextran'] ?? null,
                'type' => MedicalLog::TYPE_VITAMIN,
                'date_administered' => today()->subDays(22)->toDateString(),
                'dosage' => '2 mL',
                'route' => 'im',
                'administered_by' => 'Farm owner',
                'completed_at' => now()->subDays(22),
                'notes' => 'Routine piglet anaemia prevention.',
            ]);
        }

        // Dewormer booster due in 14 days on the weaned litter.
        if ($weanedLitter) {
            MedicalLog::create([
                'treatable_type' => Litter::class,
                'treatable_id' => $weanedLitter->id,
                'medication_id' => $meds['Ivermectin'] ?? null,
                'type' => MedicalLog::TYPE_DEWORMER,
                'date_administered' => today()->subDays(7)->toDateString(),
                'dosage' => '1 mL',
                'route' => 'sc',
                'administered_by' => 'Farm owner',
                'next_due_date' => today()->addDays(14)->toDateString(),
            ]);
        }

        // Booster due in 3 days.
        MedicalLog::create([
            'treatable_type' => Pig::class,
            'treatable_id' => $pigs['SOW-001'],
            'medication_id' => $meds['Parvovirus Vaccine'] ?? null,
            'type' => MedicalLog::TYPE_VACCINE,
            'date_administered' => today()->subDays(18)->toDateString(),
            'dosage' => '2 mL',
            'route' => 'im',
            'administered_by' => 'Farm owner',
            'next_due_date' => today()->addDays(3)->toDateString(),
            'notes' => 'Annual parvovirus booster before next mating.',
        ]);

        // Overdue booster (5 days late).
        MedicalLog::create([
            'treatable_type' => Pig::class,
            'treatable_id' => $pigs['SOW-002'],
            'medication_id' => $meds['Erysipelas Vaccine'] ?? null,
            'type' => MedicalLog::TYPE_VACCINE,
            'date_administered' => today()->subDays(35)->toDateString(),
            'dosage' => '2 mL',
            'route' => 'im',
            'administered_by' => 'Farm owner',
            'next_due_date' => today()->subDays(5)->toDateString(),
            'notes' => 'Erysipelas booster — overdue, administer this week.',
        ]);

        // Booster due in 6 days on the boar.
        MedicalLog::create([
            'treatable_type' => Pig::class,
            'treatable_id' => $pigs['BOAR-001'],
            'medication_id' => $meds['Vitamin B-Complex'] ?? null,
            'type' => MedicalLog::TYPE_VITAMIN,
            'date_administered' => today()->subDays(9)->toDateString(),
            'dosage' => '5 mL',
            'route' => 'im',
            'administered_by' => 'Farm owner',
            'next_due_date' => today()->addDays(6)->toDateString(),
        ]);

        // Scheduled future administration (planned, not yet given).
        MedicalLog::create([
            'treatable_type' => Pig::class,
            'treatable_id' => $pigs['SOW-004'],
            'medication_id' => $meds['Parvovirus Vaccine'] ?? null,
            'type' => MedicalLog::TYPE_VACCINE,
            'date_administered' => today()->addDays(5)->toDateString(),
            'dosage' => '2 mL',
            'route' => 'im',
            'administered_by' => 'Farm owner',
            'notes' => 'Scheduled before breeding.',
        ]);
    }
}
