<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Ana', 'last_name' => 'Pérez', 'birth_date' => '2000-01-01', 'gender' => 'F',
            'address' => '1 Main St', 'city' => 'Queens', 'state' => 'NY', 'zip_code' => '11372',
            'phone' => '555-0100', 'email' => 'ana@test.com',
            'package' => 'Basic 5', 'transmission' => 'Automático', 'paid_amount' => 80,
            'license_number' => 'LIC-1', 'expiration_date' => '2030-01-01', 'license_class' => 'D',
        ], $overrides);
    }

    public function test_enrollment_snapshots_the_package_and_keeps_it_on_edit(): void
    {
        $this->actingAs(User::factory()->create());
        $basic = Package::create(['name' => 'Basic 5', 'lessons' => 5, 'price' => 180, 'is_active' => true]);
        $plus = Package::create(['name' => 'Plus 12', 'lessons' => 12, 'price' => 400, 'is_active' => true]);

        // Price/debt come from the catalog, not from the browser
        $id = $this->postJson('/students', $this->payload(['plan_cost' => 1, 'debt_amount' => 1]))
            ->assertOk()
            ->assertJsonPath('student.package_id', $basic->id)
            ->assertJsonPath('student.package_lessons', 5)
            ->assertJsonPath('student.cost', 180)
            ->assertJsonPath('student.debt', 100)
            ->assertJsonPath('student.license.license_number', 'LIC-1')
            ->json('student.db_id');

        // Catalog price changes and the package is deactivated...
        $basic->update(['price' => 999, 'lessons' => 1, 'is_active' => false]);

        // ...editing the student with the same package keeps what was bought
        $this->putJson("/students/{$id}", $this->payload(['paid_amount' => 100, 'license_number' => null, 'expiration_date' => null, 'license_class' => null]))
            ->assertOk()
            ->assertJsonPath('student.cost', 180)
            ->assertJsonPath('student.package_lessons', 5)
            ->assertJsonPath('student.debt', 80)
            ->assertJsonPath('student.license', null);

        // Switching to another package is a new purchase at today's conditions
        $this->putJson("/students/{$id}", $this->payload(['package' => 'Plus 12', 'paid_amount' => 100, 'license_number' => null, 'expiration_date' => null, 'license_class' => null]))
            ->assertOk()
            ->assertJsonPath('student.package_id', $plus->id)
            ->assertJsonPath('student.package_lessons', 12)
            ->assertJsonPath('student.cost', 400)
            ->assertJsonPath('student.debt', 300);
    }

    public function test_inactive_or_unknown_package_cannot_be_purchased(): void
    {
        $this->actingAs(User::factory()->create());
        Package::create(['name' => 'Old', 'lessons' => 5, 'price' => 100, 'is_active' => false]);

        $this->postJson('/students', $this->payload(['package' => 'Old']))->assertJsonValidationErrors('package');
        $this->postJson('/students', $this->payload(['package' => 'Nope']))->assertJsonValidationErrors('package');
    }
}
