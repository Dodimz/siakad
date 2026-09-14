<?php

namespace Tests\Feature\AcademicAdvisor;

use App\Models\Dosen;
use App\Models\Fakultas;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\KrsDetail;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Nilai;
use App\Models\Prodi;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Services\AcademicAdvisor\AdvisorContextBuilder;
use App\Services\AcademicAdvisor\AdvisorGuards;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicAdvisorTest extends TestCase
{
    use RefreshDatabase;

    protected AdvisorContextBuilder $contextBuilder;

    protected AdvisorGuards $guards;

    protected Mahasiswa $mahasiswa;

    protected TahunAkademik $activeTahunAkademik;

    protected Dosen $dosen;

    protected Prodi $prodi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->contextBuilder = app(AdvisorContextBuilder::class);
        $this->guards = app(AdvisorGuards::class);

        // Create test data
        $this->setupTestData();
    }

    protected function setupTestData(): void
    {
        // Create fakultas and prodi
        $fakultas = Fakultas::create([
            'nama' => 'Fakultas Teknik',
            'kode' => 'FT',
        ]);

        $this->prodi = Prodi::create([
            'fakultas_id' => $fakultas->id,
            'nama' => 'Sistem Informasi',
            'kode' => 'SI',
            'jenjang' => 'S1',
        ]);

        // Create dosen
        $dosenUser = User::create([
            'name' => 'Dr. Test Dosen',
            'email' => 'dosen@test.com',
            'password' => bcrypt('password'),
            'role' => 'dosen',
        ]);

        $this->dosen = Dosen::create([
            'user_id' => $dosenUser->id,
            'nidn' => '1234567890',
            'nama' => 'Dr. Test Dosen',
            'prodi_id' => $this->prodi->id,
        ]);

        // Create user and mahasiswa
        $user = User::create([
            'name' => 'Test Mahasiswa',
            'email' => 'test@mahasiswa.com',
            'password' => bcrypt('password'),
            'role' => 'mahasiswa',
        ]);

        $this->mahasiswa = Mahasiswa::create([
            'user_id' => $user->id,
            'nim' => '2303113649',
            'nama' => 'Test Mahasiswa',
            'prodi_id' => $this->prodi->id,
            'angkatan' => 2023,
            'status' => 'aktif',
            'dosen_wali_id' => $this->dosen->id,
        ]);

        // Create active tahun akademik
        $this->activeTahunAkademik = TahunAkademik::create([
            'tahun' => '2024/2025',
            'semester' => 'Ganjil',
            'is_active' => true,
            'tanggal_mulai' => now()->subMonths(2),
            'tanggal_selesai' => now()->addMonths(4),
        ]);

        // Create curriculum courses including Big Data
        $this->createCurriculumCourses();

        // Create some completed courses (LULUS) - total 87 SKS
        $this->createCompletedCourses(87);
    }

    protected function createCurriculumCourses(): void
    {
        // Create Big Data course in semester 7 (available in curriculum)
        MataKuliah::create([
            'kode_mk' => 'SI701',
            'nama_mk' => 'Big Data',
            'sks' => 3,
            'semester' => 7,
            'prodi_id' => $this->prodi->id,
            'is_wajib' => false,
        ]);

        // Create some other curriculum courses
        MataKuliah::create([
            'kode_mk' => 'SI702',
            'nama_mk' => 'Data Mining',
            'sks' => 3,
            'semester' => 7,
            'prodi_id' => $this->prodi->id,
            'is_wajib' => false,
        ]);

        MataKuliah::create([
            'kode_mk' => 'SI801',
            'nama_mk' => 'Skripsi',
            'sks' => 6,
            'semester' => 8,
            'prodi_id' => $this->prodi->id,
            'is_wajib' => true,
        ]);
    }

    protected function createCompletedCourses(int $targetSks): void
    {
        $sksCreated = 0;
        $semesterNum = 1;
        $courseNum = 1;

        while ($sksCreated < $targetSks) {
            $sks = min(3, $targetSks - $sksCreated);

            $mk = MataKuliah::create([
                'kode_mk' => 'MK'.str_pad($courseNum, 3, '0', STR_PAD_LEFT),
                'nama_mk' => 'Mata Kuliah '.$courseNum,
                'sks' => $sks,
                'semester' => $semesterNum,
                'prodi_id' => $this->prodi->id,
                'is_wajib' => true,
            ]);

            $period = intdiv($courseNum - 1, 2);
            $year = 2008 + $period;

            $tahunAkademik = TahunAkademik::create([
                'tahun' => $year.'/'.($year + 1),
                'semester' => $courseNum % 2 == 1 ? 'Ganjil' : 'Genap',
                'is_active' => false,
                'tanggal_mulai' => now()->subYear(),
                'tanggal_selesai' => now()->subMonths(6),
            ]);

            $krs = Krs::create([
                'mahasiswa_id' => $this->mahasiswa->id,
                'tahun_akademik_id' => $tahunAkademik->id,
                'status' => 'approved',
                'total_sks' => $sks,
            ]);

            $kelas = Kelas::create([
                'mata_kuliah_id' => $mk->id,
                'dosen_id' => $this->dosen->id,
                'nama_kelas' => 'Kelas A',
                'tahun_akademik_id' => $tahunAkademik->id,
                'kapasitas' => 40,
                'terisi' => 1,
            ]);

            KrsDetail::create([
                'krs_id' => $krs->id,
                'kelas_id' => $kelas->id,
            ]);

            Nilai::create([
                'mahasiswa_id' => $this->mahasiswa->id,
                'kelas_id' => $kelas->id,
                'mata_kuliah_id' => $mk->id,
                'nilai_angka' => 85.5,
                'nilai_huruf' => 'A',
                'bobot' => 4.0,
                'status' => 'LULUS',
            ]);

            $sksCreated += $sks;
            $courseNum++;

            if ($courseNum % 6 == 0) {
                $semesterNum++;
            }
        }
    }

    /**
     * Test 1: it_returns_total_sks_and_progress_using_144_rule
     * Input: total lulus 87, rule 144
     * Expected: progress ≈ 60% (57 SKS remaining)
     */
    public function test_it_returns_total_sks_and_progress_using_144_rule(): void
    {
        $context = $this->contextBuilder->build($this->mahasiswa);
        $progress = $this->contextBuilder->calculateGraduationProgress($context);

        // Expected: 87 SKS lulus, 144 target, 57 remaining, ~60.4% progress
        $this->assertEquals(87, $progress['sks_lulus']);
        $this->assertEquals(144, $progress['sks_target']);
        $this->assertEquals(57, $progress['sks_remaining']);
        $this->assertEqualsWithDelta(60.42, $progress['progress_percent'], 0.5);

        // Verify prodi rules loaded correctly
        $this->assertEquals(144, $context['prodi_rules']['graduation_total_sks']);
    }

    /**
     * Test 2: it_denies_thesis_if_sks_less_than_144
     * Expected: "TIDAK" + "kurang 57 SKS"
     */
    public function test_it_denies_thesis_if_sks_less_than_144(): void
    {
        $context = $this->contextBuilder->build($this->mahasiswa);
        $progress = $this->contextBuilder->calculateGraduationProgress($context);

        // With 87 SKS, should NOT be eligible for thesis (requires 144)
        $this->assertFalse($progress['eligible_thesis']);
        $this->assertEquals(57, $progress['sks_remaining']);

        // Verify thesis min sks rule
        $this->assertEquals(144, $context['prodi_rules']['thesis_min_sks']);
    }

    /**
     * Test 3: it_finds_big_data_from_curriculum_semester_7
     * Expected: Big Data semester 7, status "TERSEDIA_DI_KURIKULUM"
     */
    public function test_it_finds_big_data_from_curriculum_semester_7(): void
    {
        $context = $this->contextBuilder->build($this->mahasiswa);

        // Verify Big Data exists in database
        $bigDataFromDb = MataKuliah::where('nama_mk', 'Big Data')->first();
        $this->assertNotNull($bigDataFromDb, 'Big Data should exist in database');

        // Search for Big Data in course statuses
        $bigDataCourse = $this->contextBuilder->findCourseByName($context, 'Big Data');

        $this->assertNotNull($bigDataCourse, 'Big Data should be found in context');
        $this->assertStringContainsString('Big Data', $bigDataCourse['nama']);
        $this->assertEquals(7, $bigDataCourse['semester']);
        $this->assertEquals('TERSEDIA_DI_KURIKULUM', $bigDataCourse['status']);

        // Also verify it's in curriculum array
        $this->assertArrayHasKey('curriculum', $context);

        $semester7 = collect($context['curriculum'])->firstWhere('semester', 7);
        $this->assertNotNull($semester7, 'Semester 7 should exist in curriculum');

        $bigDataInCurriculum = collect($semester7['mata_kuliah'])
            ->first(fn ($mk) => str_contains($mk['nama'], 'Big Data'));
        $this->assertNotNull($bigDataInCurriculum, 'Big Data should be in semester 7 curriculum');
        $this->assertEquals(3, $bigDataInCurriculum['sks']);
    }

    /**
     * Test 4: it_does_not_flag_attendance_low_if_attendance_data_missing
     * When attendance_data_available=false
     * Expected: mention "data presensi belum tersedia", not "presensi rendah"
     */
    public function test_it_does_not_flag_attendance_low_if_attendance_data_missing(): void
    {
        // Create an enrolled course in active semester (no meeting data)
        $enrolledMk = MataKuliah::create([
            'kode_mk' => 'ATTEND01',
            'nama_mk' => 'Course Without Attendance',
            'sks' => 3,
            'semester' => 5,
            'prodi_id' => $this->prodi->id,
            'is_wajib' => true,
        ]);

        $enrolledKelas = Kelas::create([
            'mata_kuliah_id' => $enrolledMk->id,
            'dosen_id' => $this->dosen->id,
            'nama_kelas' => 'Kelas No Attendance',
            'tahun_akademik_id' => $this->activeTahunAkademik->id,
            'kapasitas' => 40,
            'terisi' => 1,
        ]);

        $activeKrs = Krs::create([
            'mahasiswa_id' => $this->mahasiswa->id,
            'tahun_akademik_id' => $this->activeTahunAkademik->id,
            'status' => 'approved',
            'total_sks' => 3,
        ]);

        KrsDetail::create([
            'krs_id' => $activeKrs->id,
            'kelas_id' => $enrolledKelas->id,
        ]);

        $context = $this->contextBuilder->build($this->mahasiswa);

        // Since we haven't created any pertemuan, attendance should be unavailable
        $this->assertFalse($context['attendance']['data_available']);
        $this->assertTrue($context['attendance']['all_zero_or_null']);
        $this->assertNotNull($context['attendance']['warning']);

        // Test guard with output that mentions low attendance
        $badOutput = 'Berdasarkan data, presensi rendah dan perlu diperbaiki.';
        $guardResult = $this->guards->attendanceGuard($context, $badOutput);

        $this->assertEquals(AdvisorGuards::GUARD_FAIL, $guardResult['status']);
        $this->assertNotNull($guardResult['issue']);
        $this->assertStringContainsString('belum tersedia', $guardResult['recommended_response']);
    }

    /**
     * Test 5: it_rejects_generic_assumption_phrases
     * If output contains "biasanya/umumnya/tergantung"
     * Expected: retry or sanitized output
     */
    public function test_it_rejects_generic_assumption_phrases(): void
    {
        // Test each forbidden phrase
        $forbiddenOutputs = [
            'Biasanya SKS kelulusan adalah 120 SKS.',
            'Umumnya mahasiswa semester 5 sudah bisa mengambil skripsi.',
            'Hal ini tergantung kebijakan prodi masing-masing.',
            'Pada umumnya mata kuliah Big Data ada di semester atas.',
        ];

        foreach ($forbiddenOutputs as $output) {
            $result = $this->guards->preventGenericAssumptions($output);

            $this->assertEquals(
                AdvisorGuards::GUARD_RETRY,
                $result['status'],
                "Expected GUARD_RETRY for output: {$output}"
            );
            $this->assertNotEmpty($result['violations']);
            $this->assertNotNull($result['retry_prompt']);
            $this->assertNotNull($result['sanitized_output']);
        }

        // Test good output (should pass)
        $goodOutput = 'Berdasarkan aturan Prodi Sistem Informasi UNRI, total SKS kelulusan adalah 144 SKS.';
        $result = $this->guards->preventGenericAssumptions($goodOutput);

        $this->assertEquals(AdvisorGuards::GUARD_PASS, $result['status']);
        $this->assertEmpty($result['violations']);
    }

    /**
     * Test 6: it_distinguishes_completed_vs_enrolled_vs_available
     * Completed from KHS, enrolled from KRS, available from curriculum.
     */
    public function test_it_distinguishes_completed_vs_enrolled_vs_available(): void
    {
        // Create an enrolled course (in active KRS, no grade yet)
        $enrolledMk = MataKuliah::create([
            'kode_mk' => 'ENROLLED01',
            'nama_mk' => 'Enrolled Course',
            'sks' => 3,
            'semester' => 5,
            'prodi_id' => $this->prodi->id,
            'is_wajib' => true,
        ]);

        $enrolledKelas = Kelas::create([
            'mata_kuliah_id' => $enrolledMk->id,
            'dosen_id' => $this->dosen->id,
            'nama_kelas' => 'Kelas Enrolled',
            'tahun_akademik_id' => $this->activeTahunAkademik->id,
            'kapasitas' => 40,
            'terisi' => 1,
        ]);

        $activeKrs = Krs::create([
            'mahasiswa_id' => $this->mahasiswa->id,
            'tahun_akademik_id' => $this->activeTahunAkademik->id,
            'status' => 'approved',
            'total_sks' => 3,
        ]);

        KrsDetail::create([
            'krs_id' => $activeKrs->id,
            'kelas_id' => $enrolledKelas->id,
        ]);

        // Build context and check statuses
        $context = $this->contextBuilder->build($this->mahasiswa);

        $statuses = collect($context['course_statuses']);

        // Check LULUS courses (from our setUp)
        $completedCourses = $statuses->where('status', 'LULUS');
        $this->assertGreaterThan(0, $completedCourses->count(), 'Should have completed courses');

        // Check SEDANG_DIAMBIL course
        $enrolledCourse = $statuses->firstWhere('kode', 'ENROLLED01');
        $this->assertNotNull($enrolledCourse, 'Enrolled course should exist in statuses');
        $this->assertEquals('SEDANG_DIAMBIL', $enrolledCourse['status']);

        // Check TERSEDIA_DI_KURIKULUM (courses not yet taken)
        // Big Data should be available since we created it but haven't enrolled
        $availableCourses = $statuses->where('status', 'TERSEDIA_DI_KURIKULUM');
        $this->assertGreaterThan(0, $availableCourses->count(), 'Should have available courses from curriculum');

        // Specifically check Big Data is available (not completed, not enrolled)
        $bigData = $statuses->first(fn ($c) => str_contains($c['nama'], 'Big Data'));
        $this->assertNotNull($bigData, 'Big Data should exist in course statuses');
        $this->assertEquals('TERSEDIA_DI_KURIKULUM', $bigData['status']);
        $this->assertEquals(7, $bigData['semester']);
    }

    /**
     * Additional test: Guards validation works correctly
     */
    public function test_guards_assert_rules_present(): void
    {
        $context = $this->contextBuilder->build($this->mahasiswa);

        // Should not throw exception with valid context
        $this->guards->assertRulesPresent($context);
        $this->guards->validateContext($context);

        // Should throw with invalid context
        $this->expectException(\InvalidArgumentException::class);
        $this->guards->assertRulesPresent([
            'prodi_rules' => ['graduation_total_sks' => 0],
        ]);
    }

    /**
     * Additional test: Full guard pipeline works
     */
    public function test_full_guard_pipeline(): void
    {
        $context = $this->contextBuilder->build($this->mahasiswa);

        // Good output should pass
        $goodOutput = 'Anda telah menyelesaikan 87 dari 144 SKS (60.4%). Tersisa 57 SKS untuk lulus.';
        $result = $this->guards->runPostGuards($context, $goodOutput);

        $this->assertTrue($result['passed']);
        $this->assertEmpty($result['issues']);

        // Bad output with assumption should fail
        $badOutput = 'Biasanya mahasiswa membutuhkan sekitar 120-140 SKS untuk lulus.';
        $result = $this->guards->runPostGuards($context, $badOutput);

        $this->assertFalse($result['passed']);
        $this->assertNotEmpty($result['issues']);
        $this->assertTrue($result['should_retry']);
    }
}
