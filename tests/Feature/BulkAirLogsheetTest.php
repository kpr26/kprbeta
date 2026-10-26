<?php

namespace Tests\Feature;

use App\Models\MeterReading;
use App\Models\User;
use App\Services\ExcelReaderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BulkAirLogsheetTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_access_bulk_air_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('logsheet.bulk.air'));
        $response->assertStatus(200);
        $response->assertSee('Bulk Input Manual Water Meter');
        $response->assertSee('Unduh Template Excel');
        $response->assertSee('Main Water meter Transfer Pump  Lt B1');
    }

    public function test_non_superadmin_cannot_access_bulk_air_page(): void
    {
        $teknisi = User::factory()->create(['role' => 'teknisi']);
        $user = User::factory()->create(['role' => 'user']);
        $gm = User::factory()->create(['role' => 'gm']);

        $this->actingAs($teknisi)->get(route('logsheet.bulk.air'))->assertStatus(403);
        $this->actingAs($user)->get(route('logsheet.bulk.air'))->assertStatus(403);
        $this->actingAs($gm)->get(route('logsheet.bulk.air'))->assertStatus(403);
    }

    public function test_guest_is_redirected_from_bulk_air_page(): void
    {
        $response = $this->get(route('logsheet.bulk.air'));
        $response->assertRedirect(route('login'));
    }

    public function test_superadmin_can_download_bulk_air_template(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('logsheet.bulk.air.template', [
            'year'  => 2026,
            'month' => 5,
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringStartsWith("PK\x03\x04", $response->getContent());
    }

    public function test_superadmin_can_upload_excel_xlsx_and_import_water_readings_automatically_verified(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Generate binary XLSX matching user's Image 2:
        // Col A: Tanggal, Col B: Main Water meter Transfer Pump  Lt B1
        $tempPath = tempnam(sys_get_temp_dir(), 'test_bulk_air_') . '.xlsx';

        $zip = new \ZipArchive();
        $zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStringItem+xml"/></Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
        $zip->addFromString('_rels/.rels', $rels);
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/></Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);
        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheets><sheet name="Sheet1" sheetId="1" r:id="rId1" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/></sheets></workbook>';
        $zip->addFromString('xl/workbook.xml', $workbook);

        $sharedStrings = [
            'Tanggal',
            'Main Water meter Transfer Pump  Lt B1',
            '5/1/2026',
            '5/2/2026',
            '5/3/2026',
        ];
        $sstXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="'.count($sharedStrings).'" uniqueCount="'.count($sharedStrings).'">';
        foreach ($sharedStrings as $str) {
            $sstXml .= '<si><t>' . htmlspecialchars($str, ENT_XML1) . '</t></si>';
        }
        $sstXml .= '</sst>';
        $zip->addFromString('xl/sharedStrings.xml', $sstXml);

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>
            <row r="1"><c r="A1" t="s"><v>0</v></c><c r="B1" t="s"><v>1</v></c></row>
            <row r="2"><c r="A2" t="s"><v>2</v></c><c r="B2"><v>260907.8</v></c></row>
            <row r="3"><c r="A3" t="s"><v>3</v></c><c r="B3"><v>261026.9</v></c></row>
            <row r="4"><c r="A4" t="s"><v>4</v></c><c r="B4"><v>261114.2</v></c></row>
        </sheetData></worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        $uploadedFile = new UploadedFile($tempPath, 'Water_Meter_Mei_2026.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($admin)->post(route('logsheet.bulk.air.store'), [
            'file' => $uploadedFile,
        ]);

        @unlink($tempPath);

        $response->assertRedirect(route('logsheet.index', [
            'periode' => '2026-05',
        ]));

        $response->assertSessionHas('success');
        $response->assertSessionHas('status');

        // Check database records (3 records created)
        $this->assertEquals(3, MeterReading::count());

        $reading1 = MeterReading::whereDate('tanggal', '2026-05-01')->first();
        $this->assertNotNull($reading1);
        $this->assertEquals(260907.8, $reading1->stand_air);
        $this->assertNotNull($reading1->verified_at);
        $this->assertEquals($admin->id, $reading1->verified_by);

        $reading2 = MeterReading::whereDate('tanggal', '2026-05-02')->first();
        $this->assertNotNull($reading2);
        $this->assertEquals(261026.9, $reading2->stand_air);

        $reading3 = MeterReading::whereDate('tanggal', '2026-05-03')->first();
        $this->assertNotNull($reading3);
        $this->assertEquals(261114.2, $reading3->stand_air);
    }

    public function test_bulk_air_tolerates_empty_and_abnormal_cells(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // CSV with an empty cell and a decreased stand (abnormal)
        $csvContent = "Tanggal,Main Water meter Transfer Pump  Lt B1\n";
        $csvContent .= "5/1/2026,260907.8\n";
        $csvContent .= "5/2/2026,\n"; // Empty stand
        $csvContent .= "5/3/2026,250000.0\n"; // Decreased stand (abnormal, will be corrected manually later)

        $tempCsv = tempnam(sys_get_temp_dir(), 'test_air_csv_') . '.csv';
        file_put_contents($tempCsv, $csvContent);

        $uploadedFile = new UploadedFile($tempCsv, 'Water_Test.csv', 'text/csv', null, true);

        $response = $this->actingAs($admin)->post(route('logsheet.bulk.air.store'), [
            'file' => $uploadedFile,
        ]);

        @unlink($tempCsv);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $response->assertSessionHas('status');

        // Only 2 dates with non-empty values should be saved/updated
        $this->assertEquals(2, MeterReading::count());

        $r1 = MeterReading::whereDate('tanggal', '2026-05-01')->first();
        $this->assertNotNull($r1);
        $this->assertEquals(260907.8, $r1->stand_air);

        $r2 = MeterReading::whereDate('tanggal', '2026-05-02')->first();
        $this->assertNull($r2);

        $r3 = MeterReading::whereDate('tanggal', '2026-05-03')->first();
        $this->assertNotNull($r3);
        $this->assertEquals(250000.0, $r3->stand_air);
    }

    public function test_bulk_air_preserves_existing_electricity_data_on_same_dates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Pre-create electricity reading on 2026-05-01
        $existing = MeterReading::create([
            'tanggal'     => '2026-05-01',
            'stand_bp'    => 12345.67,
            'stand_lwbp'  => 500.0,
            'stand_wbp'   => 200.0,
            'stand_total' => 15000.0,
            'user_id'     => $admin->id,
            'verified_at' => now(),
            'verified_by' => $admin->id,
        ]);

        $csvContent = "Tanggal,Main Water meter Transfer Pump  Lt B1\n";
        $csvContent .= "5/1/2026,260907.8\n";

        $tempCsv = tempnam(sys_get_temp_dir(), 'test_preserve_') . '.csv';
        file_put_contents($tempCsv, $csvContent);

        $uploadedFile = new UploadedFile($tempCsv, 'Water_Preserve.csv', 'text/csv', null, true);

        $response = $this->actingAs($admin)->post(route('logsheet.bulk.air.store'), [
            'file' => $uploadedFile,
        ]);

        @unlink($tempCsv);

        $response->assertRedirect();

        // Should still be only 1 record on 2026-05-01
        $this->assertEquals(1, MeterReading::count());

        $reading = MeterReading::whereDate('tanggal', '2026-05-01')->first();
        $this->assertNotNull($reading);
        $this->assertEquals(12345.67, $reading->stand_bp);
        $this->assertEquals(500.0, $reading->stand_lwbp);
        $this->assertEquals(200.0, $reading->stand_wbp);
        $this->assertEquals(15000.0, $reading->stand_total);
        $this->assertEquals(260907.8, $reading->stand_air);
        $this->assertNotNull($reading->verified_at);
        $this->assertEquals($admin->id, $reading->verified_by);
    }
}
