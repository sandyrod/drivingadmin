<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    private function data(array $overrides = []): array
    {
        return array_merge([
            'name' => 'PRINCIPIANTE package 20 Lessons', 'description' => 'Para principiantes',
            'lessons' => 20, 'price' => 899.99, 'is_active' => true,
        ], $overrides);
    }

    public function test_package_is_created_and_updated(): void
    {
        $id = $this->postJson('/packages', $this->data())
            ->assertOk()
            ->assertJsonPath('package.lessons', 20)
            ->assertJsonPath('package.price', 899.99)
            ->assertJsonPath('package.students_count', 0)
            ->json('package.id');

        $this->putJson("/packages/{$id}", $this->data(['price' => 950, 'lessons' => 18]))
            ->assertOk()
            ->assertJsonPath('package.price', 950)
            ->assertJsonPath('package.lessons', 18);
    }

    public function test_validation(): void
    {
        $this->postJson('/packages', $this->data())->assertOk();

        $this->postJson('/packages', $this->data())->assertJsonValidationErrors('name');                     // duplicated
        $this->postJson('/packages', $this->data(['name' => 'X', 'price' => 0]))->assertJsonValidationErrors('price'); // active needs a price
        $this->postJson('/packages', $this->data(['name' => 'Y', 'lessons' => -1]))->assertJsonValidationErrors('lessons');

        // Inactive with no price yet is allowed
        $this->postJson('/packages', $this->data(['name' => 'Z', 'price' => 0, 'is_active' => false]))->assertOk();
    }

    public function test_activation_requires_a_price(): void
    {
        $package = Package::where('name', 'Single Lesson')->first(); // seeded inactive at $0

        $this->putJson("/packages/{$package->id}/active", ['is_active' => true])->assertStatus(422);

        $this->putJson("/packages/{$package->id}", $this->data(['name' => 'Single Lesson', 'lessons' => 1, 'price' => 75, 'is_active' => false]))->assertOk();
        $this->putJson("/packages/{$package->id}/active", ['is_active' => true])->assertOk()->assertJsonPath('package.is_active', true);
        $this->putJson("/packages/{$package->id}/active", ['is_active' => false])->assertOk()->assertJsonPath('package.is_active', false);
    }

    public function test_only_unused_packages_can_be_deleted(): void
    {
        $unused = Package::create(['name' => 'Unused', 'lessons' => 1, 'price' => 10, 'is_active' => true]);
        $used = Package::create(['name' => 'Used', 'lessons' => 1, 'price' => 10, 'is_active' => true]);

        $student = new Student([
            'first_name' => 'Ana', 'last_name' => 'T', 'address' => 'x', 'city' => 'NY', 'state' => 'NY',
            'zip_code' => '1', 'phone' => '1', 'transmission' => 'Automático', 'paid_amount' => 0,
        ]);
        $student->purchasePackage($used);
        $student->save();

        $this->deleteJson("/packages/{$used->id}")->assertStatus(422);
        $this->deleteJson("/packages/{$unused->id}")->assertOk();
        $this->assertDatabaseMissing('packages', ['id' => $unused->id]);
    }

    public function test_guests_cannot_manage_packages(): void
    {
        auth()->logout();
        $this->postJson('/packages', $this->data())->assertUnauthorized();
    }
}
