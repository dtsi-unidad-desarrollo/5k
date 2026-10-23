<?php

namespace Tests\Feature;

use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParticipantRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_registration_page(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('5K Caminata y Carrera');
        $response->assertSee('Formulario de Registro');
    }

    public function test_participant_registration_generates_correct_5k_bib_format(): void
    {
        // First participant
        $response1 = $this->postJson('/registro', [
            'first_name' => 'Jesus',
            'last_name' => 'Silva',
            'national_id_type' => 'V',
            'national_id' => '12345678',
            'phone' => '0414-1112233',
        ]);

        $response1->assertStatus(200);
        $response1->assertJson([
            'success' => true,
            'participant' => [
                'bib_number' => '5K-0001',
                'national_id' => 'V-12345678',
            ],
        ]);

        // Second participant
        $response2 = $this->postJson('/registro', [
            'first_name' => 'Andrea',
            'last_name' => 'Mendoza',
            'national_id_type' => 'V',
            'national_id' => '28999888',
            'phone' => '0412-4445566',
        ]);

        $response2->assertStatus(200);
        $response2->assertJson([
            'success' => true,
            'participant' => [
                'bib_number' => '5K-0002',
            ],
        ]);
    }

    public function test_duplicate_cedula_returns_existing_assigned_bib(): void
    {
        $p = Participant::create([
            'first_name' => 'Pedro',
            'last_name' => 'Pérez',
            'phone' => '0424-5556677',
            'national_id' => 'V-18555666',
            'national_id_clean' => '18555666',
            'bib_number' => '5K-0001',
            'bib_sequence' => 1,
        ]);

        // Try registering again with the exact same cedula
        $response = $this->postJson('/registro', [
            'first_name' => 'Pedro',
            'last_name' => 'Pérez',
            'national_id_type' => 'V',
            'national_id' => '18555666',
            'phone' => '0424-5556677',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'already_registered' => true,
            'participant' => [
                'bib_number' => '5K-0001',
            ],
        ]);
    }

    public function test_lookup_by_cedula_works(): void
    {
        Participant::create([
            'first_name' => 'Elena',
            'last_name' => 'Torres',
            'phone' => '0416-7778899',
            'national_id' => 'V-22333444',
            'national_id_clean' => '22333444',
            'bib_number' => '5K-0042',
            'bib_sequence' => 42,
        ]);

        // Search with raw numbers
        $response = $this->getJson('/consultar?cedula=22333444');
        $response->assertStatus(200);
        $response->assertJson([
            'found' => true,
            'participant' => [
                'bib_number' => '5K-0042',
                'first_name' => 'Elena',
            ],
        ]);
    }

    public function test_can_view_dorsal_ticket(): void
    {
        $p = Participant::create([
            'first_name' => 'Jose',
            'last_name' => 'Suárez',
            'phone' => '0412-1231231',
            'national_id' => 'V-19888777',
            'national_id_clean' => '19888777',
            'bib_number' => '5K-0005',
            'bib_sequence' => 5,
        ]);

        $response = $this->get('/dorsal/' . $p->bib_number);
        $response->assertStatus(200);
        $response->assertSee('5K-0005');
        $response->assertSee('Jose Suárez');
    }

    public function test_first_name_with_numbers_is_rejected(): void
    {
        $response = $this->postJson('/registro', [
            'first_name' => 'Carlos123',
            'last_name' => 'Pérez',
            'national_id_type' => 'V',
            'national_id' => '12345678',
            'phone' => '0414-1112233',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['first_name']);
    }

    public function test_last_name_with_numbers_is_rejected(): void
    {
        $response = $this->postJson('/registro', [
            'first_name' => 'Carlos',
            'last_name' => 'Pérez456',
            'national_id_type' => 'V',
            'national_id' => '12345678',
            'phone' => '0414-1112233',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['last_name']);
    }

    public function test_phone_with_letters_is_rejected(): void
    {
        $response = $this->postJson('/registro', [
            'first_name' => 'Carlos',
            'last_name' => 'Pérez',
            'national_id_type' => 'V',
            'national_id' => '12345678',
            'phone' => '0414-TELEFONO',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone']);
    }

    public function test_cedula_exceeding_9_digits_is_rejected(): void
    {
        $response = $this->postJson('/registro', [
            'first_name' => 'Carlos',
            'last_name' => 'Pérez',
            'national_id_type' => 'V',
            'national_id' => '1234567890', // 10 digits
            'phone' => '0414-1112233',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['national_id']);
    }

    public function test_cedula_with_letters_is_rejected(): void
    {
        $response = $this->postJson('/registro', [
            'first_name' => 'Carlos',
            'last_name' => 'Pérez',
            'national_id_type' => 'V',
            'national_id' => '24ABC123',
            'phone' => '0414-1112233',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['national_id']);
    }
}
