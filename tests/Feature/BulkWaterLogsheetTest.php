<?php

namespace Tests\Feature;

use App\Models\PublicAreaMeterReading;
use App\Models\User;
use App\Services\ExcelReaderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BulkWaterLogsheetTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_access_bulk_water_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('public-area.bulk.water'));
        $response->assertStatus(200);
        $response->assertSee('Bulk Input Manual Water Meter');
        $response->assertSee('Unduh Template Excel');
    }

    public function test_non_superadmin_cannot_access_bulk_water_page(): void
    {
        $teknisi = User::factory()->create(['role' => 'teknisi']);
        $user = User::factory()->create(['role' => 'user']);
        $gm = User::factory()->create(['role' => 'gm']);

        $this->actingAs($teknisi)->get(route('public-area.bulk.water'))->assertStatus(403);
        $this->actingAs($user)->get(route('public-area.bulk.water'))->assertStatus(403);
        $this->actingAs($gm)->get(route('public-area.bulk.water'))->assertStatus(403);
    }

    public function test_guest_is_redirected_from_bulk_water_page(): void
    {
        $response = $this->get(route('public-area.bulk.water'));
        $response->assertRedirect(route('login'));
    }

    public function test_superadmin_can_download_bulk_water_template(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('public-area.bulk.water.template', [
            'year'  => 2026,
            'month' => 2,
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->assertStringStartsWith("PK\x03\x04", $response->getContent());
    }

    public function test_superadmin_can_upload_excel_xlsx_and_import_data_automatically_verified(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Generate binary XLSX with sample data matching user's Image 2
        $reader = new ExcelReaderService();
        $tempPath = tempnam(sys_get_temp_dir(), 'test_upload_') . '.xlsx';

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
            'Water meter Cooling Tower 12A',
            'Water Meter Toilet Sky Lobby 11 & Musholla 12',
            'Water meter Pantry office',
            '2/1/2026',
            '2/2/2026',
            '2/3/2026',
        ];
        $sstXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="'.count($sharedStrings).'" uniqueCount="'.count($sharedStrings).'">';
        foreach ($sharedStrings as $str) {
            $sstXml .= '<si><t>' . htmlspecialchars($str, ENT_XML1) . '</t></si>';
        }
        $sstXml .= '</sst>';
        $zip->addFromString('xl/sharedStrings.xml', $sstXml);

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>
            <row r="1"><c r="A1" t="s"><v>0</v></c><c r="B1" t="s"><v>1</v></c><c r="C1" t="s"><v>2</v></c><c r="D1" t="s"><v>3</v></c></row>
            <row r="2"><c r="A2" t="s"><v>4</v></c><c r="B2"><v>10271.856</v></c><c r="C2"><v>21005.815</v></c><c r="D2"><v>24116.326</v></c></row>
            <row r="3"><c r="A3" t="s"><v>5</v></c><c r="B3"><v>10271.860</v></c><c r="C3"><v>21008.040</v></c><c r="D3"><v>24131.760</v></c></row>
            <row r="4"><c r="A4" t="s"><v>6</v></c><c r="B4"><v>10274.960</v></c><c r="C4"><v>21012.681</v></c><c r="D4"><v>24149.870</v></c></row>
        </sheetData></worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        $uploadedFile = new UploadedFile($tempPath, 'Water_Meter_Feb_2026.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($admin)->post(route('public-area.bulk.water.store'), [
            'file' => $uploadedFile,
        ]);

        @unlink($tempPath);

        $response->assertRedirect(route('public-area.water.index', [
            'start_date' => '2026-02-01',
            'end_date'   => '2026-02-03',
        ]));

        $response->assertSessionHas('status');

        // Check database records (3 dates x 3 meters = 9 records)
        $this->assertEquals(9, PublicAreaMeterReading::where('category', 'water')->count());

        $ctReading = PublicAreaMeterReading::where('meter_key', 'cooling_tower')
            ->whereDate('tanggal', '2026-02-01')
            ->first();

        $this->assertNotNull($ctReading);
        $this->assertEquals(10271.856, $ctReading->stand_meter);
        $this->assertNotNull($ctReading->verified_at);
        $this->assertEquals($admin->id, $ctReading->verified_by);

        $skyReading = PublicAreaMeterReading::where('meter_key', 'toilet_sky_lobby')
            ->whereDate('tanggal', '2026-02-03')
            ->first();

        $this->assertNotNull($skyReading);
        $this->assertEquals(21012.681, $skyReading->stand_meter);
    }

    public function test_bulk_input_tolerates_empty_and_abnormal_cells(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // CSV with some empty cells and decreased stands
        $csvContent = "Tanggal,Water meter Cooling Tower 12A,Water Meter Toilet Sky Lobby 11 & Musholla 12,Water meter Pantry office\n";
        $csvContent .= "2/1/2026,10271.856,,24116.326\n"; // Column C is empty
        $csvContent .= "2/2/2026,,21008.040,\n";        // Column B & D are empty
        $csvContent .= "2/3/2026,10250.000,21005.000,24100.000\n"; // Lower stands (simulating abnormal / needs manual correction later)

        $tempCsv = tempnam(sys_get_temp_dir(), 'test_csv_') . '.csv';
        file_put_contents($tempCsv, $csvContent);

        $uploadedFile = new UploadedFile($tempCsv, 'Water_Test.csv', 'text/csv', null, true);

        $response = $this->actingAs($admin)->post(route('public-area.bulk.water.store'), [
            'file' => $uploadedFile,
        ]);

        @unlink($tempCsv);

        $response->assertRedirect();
        $response->assertSessionHas('status');

        // Total saved: Row 1 (2) + Row 2 (1) + Row 3 (3) = 6 records
        $this->assertEquals(6, PublicAreaMeterReading::where('category', 'water')->count());

        // Verified that row 1 Column B & D are saved even though C was empty
        $r1CT = PublicAreaMeterReading::whereDate('tanggal', '2026-02-01')->where('meter_key', 'cooling_tower')->first();
        $this->assertNotNull($r1CT);
        $this->assertEquals(10271.856, $r1CT->stand_meter);

        $r1PO = PublicAreaMeterReading::whereDate('tanggal', '2026-02-01')->where('meter_key', 'pantry_office')->first();
        $this->assertNotNull($r1PO);
        $this->assertEquals(24116.326, $r1PO->stand_meter);

        $r1Sky = PublicAreaMeterReading::whereDate('tanggal', '2026-02-01')->where('meter_key', 'toilet_sky_lobby')->first();
        $this->assertNull($r1Sky);

        // Verified abnormal stands are preserved for manual correction later
        $r3CT = PublicAreaMeterReading::whereDate('tanggal', '2026-02-03')->where('meter_key', 'cooling_tower')->first();
        $this->assertNotNull($r3CT);
        $this->assertEquals(10250.000, $r3CT->stand_meter);
    }
}
