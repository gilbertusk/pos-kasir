<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Support\SalesReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::factory()->owner()->create(['name' => 'Bu Tini']);
        $this->travelTo(now()->setDate(2026, 10, 15)->setTime(12, 0));
    }

    private function sell(string $date, int $total, int $cost, string $status = 'lunas', string $method = 'tunai'): Transaction
    {
        static $sequence = 0;
        $sequence++;

        $this->travelTo(now()->parse("{$date} 10:00"));
        $product = Product::factory()->create(['name' => 'Nasi Goreng']);

        $transaction = Transaction::create([
            'transaction_number' => 'TRX-EXP-' . $sequence,
            'user_id' => $this->owner->id,
            'subtotal' => $total,
            'total' => $total,
            'paid_amount' => $total,
            'payment_method' => $method,
            'status' => $status,
            'cancel_reason' => $status === 'batal' ? 'Salah input' : null,
        ]);

        TransactionItem::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 1,
            'price' => $total,
            'buy_price' => $cost,
            'subtotal' => $total,
        ]);

        $this->travelTo(now()->setDate(2026, 10, 15)->setTime(12, 0));

        return $transaction;
    }

    private function expense(string $date, int $amount, string $category = 'listrik', ?string $description = null): Expense
    {
        return Expense::create([
            'user_id' => $this->owner->id,
            'date' => $date,
            'category' => $category,
            'description' => $description,
            'amount' => $amount,
        ]);
    }

    private function export(array $query): TestResponse
    {
        return $this->actingAs($this->owner)->get('/reports/export?' . http_build_query($query));
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function csvRows(TestResponse $response): array
    {
        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content, 'CSV needs a UTF-8 BOM for Excel');

        $lines = preg_split('/\r?\n/', trim(substr($content, 3)));

        return array_map(fn ($line) => str_getcsv($line, ';'), $lines);
    }

    public function test_daily_report_rows_add_up_to_the_report_summary(): void
    {
        $this->sell('2026-10-01', 20000, 12000);
        $this->sell('2026-10-01', 10000, 6000);
        $this->sell('2026-10-03', 15000, 9000);
        $this->sell('2026-10-03', 50000, 30000, 'batal');
        $this->expense('2026-10-02', 7000);

        $report = new SalesReport('2026-10-01', '2026-10-03');
        $days = $report->daily();

        $this->assertCount(3, $days, 'Every day in the range is listed, also days without sales');
        $this->assertSame(['2026-10-01', '2026-10-02', '2026-10-03'], array_column($days, 'date'));
        $this->assertEquals([2, 0, 1], array_column($days, 'transactions'));
        $this->assertEquals([30000, 0, 15000], array_column($days, 'revenue'));
        $this->assertEquals([18000, 0, 9000], array_column($days, 'cogs'));
        $this->assertEquals([0, 7000, 0], array_column($days, 'expenses'));
        $this->assertEquals([12000, -7000, 6000], array_column($days, 'net_profit'));

        $summary = Transaction::profitSummary('2026-10-01', '2026-10-03');
        $this->assertEquals($summary['total_revenue'], array_sum(array_column($days, 'revenue')));
        $this->assertEquals($summary['total_cogs'], array_sum(array_column($days, 'cogs')));
        $this->assertEquals($summary['net_profit'], array_sum(array_column($days, 'net_profit')));
    }

    public function test_daily_csv_has_one_row_per_day_and_a_total(): void
    {
        $this->sell('2026-10-01', 20000, 12000);
        $this->sell('2026-10-02', 10000, 6000);
        $this->expense('2026-10-02', 3000);

        $response = $this->export(['format' => 'csv', 'report' => 'harian', 'start_date' => '2026-10-01', 'end_date' => '2026-10-02'])
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertDownload('laporan-harian_2026-10-01_2026-10-02.csv');

        $rows = $this->csvRows($response);

        $this->assertSame(['Tanggal', 'Jumlah transaksi', 'Pendapatan', 'Modal barang terjual', 'Laba kotor', 'Pengeluaran', 'Laba bersih'], $rows[0]);
        $this->assertSame(['01/10/2026', '1', '20000', '12000', '8000', '0', '8000'], $rows[1]);
        $this->assertSame(['02/10/2026', '1', '10000', '6000', '4000', '3000', '1000'], $rows[2]);
        $this->assertSame(['TOTAL', '2', '30000', '18000', '12000', '3000', '9000'], $rows[3]);
    }

    public function test_transaction_csv_lists_every_sale_with_its_status(): void
    {
        $paid = $this->sell('2026-10-01', 20000, 12000, 'lunas', 'qris');
        $cancelled = $this->sell('2026-10-01', 5000, 3000, 'batal');
        $this->sell('2026-09-30', 99000, 1000);

        $rows = $this->csvRows(
            $this->export(['format' => 'csv', 'report' => 'transaksi', 'start_date' => '2026-10-01', 'end_date' => '2026-10-01'])->assertOk()
        );

        $this->assertSame('Nomor transaksi', $rows[0][0]);
        $this->assertCount(3, $rows, 'Header plus two sales; the sale outside the range is left out');

        $header = $rows[0];
        $byNumber = collect(array_slice($rows, 1))->keyBy(0)->map(fn ($row) => array_combine($header, $row));

        $this->assertSame('Bu Tini', $byNumber[$paid->transaction_number]['Kasir']);
        $this->assertSame('QRIS', $byNumber[$paid->transaction_number]['Metode']);
        $this->assertSame('20000', $byNumber[$paid->transaction_number]['Total']);
        $this->assertSame('12000', $byNumber[$paid->transaction_number]['Modal']);
        $this->assertSame('8000', $byNumber[$paid->transaction_number]['Laba kotor']);
        $this->assertSame('Lunas', $byNumber[$paid->transaction_number]['Status']);
        $this->assertSame('Batal', $byNumber[$cancelled->transaction_number]['Status']);
        $this->assertSame('Salah input', $byNumber[$cancelled->transaction_number]['Alasan batal']);
    }

    public function test_expense_csv_lists_expenses_in_the_range(): void
    {
        $this->expense('2026-10-01', 150000, 'listrik', 'Token listrik');
        $this->expense('2026-10-05', 1000000, 'gaji', 'Gaji Oktober');
        $this->expense('2026-11-01', 5000, 'sewa', 'Di luar periode');

        $rows = $this->csvRows(
            $this->export(['format' => 'csv', 'report' => 'pengeluaran', 'start_date' => '2026-10-01', 'end_date' => '2026-10-31'])->assertOk()
        );

        $this->assertSame(['Tanggal', 'Kategori', 'Deskripsi', 'Nominal', 'Dicatat oleh'], $rows[0]);
        $this->assertSame(['01/10/2026', 'Listrik', 'Token listrik', '150000', 'Bu Tini'], $rows[1]);
        $this->assertSame(['05/10/2026', 'Gaji', 'Gaji Oktober', '1000000', 'Bu Tini'], $rows[2]);
        $this->assertSame(['TOTAL', '', '', '1150000', ''], $rows[3]);
    }

    public function test_csv_cells_can_not_run_as_spreadsheet_formulas(): void
    {
        $this->expense('2026-10-01', 1000, 'perlengkapan', '=HYPERLINK("http://evil.test","klik")');

        $rows = $this->csvRows(
            $this->export(['format' => 'csv', 'report' => 'pengeluaran', 'start_date' => '2026-10-01', 'end_date' => '2026-10-01'])->assertOk()
        );

        $this->assertSame("'=HYPERLINK(\"http://evil.test\",\"klik\")", $rows[1][2]);
    }

    public function test_pdf_export_downloads_a_pdf(): void
    {
        Setting::setValue('store_name', 'Warung Bu Tini');
        $this->sell('2026-10-01', 20000, 12000);
        $this->expense('2026-10-01', 3000);

        $response = $this->export(['format' => 'pdf', 'start_date' => '2026-10-01', 'end_date' => '2026-10-07'])
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('laporan_2026-10-01_2026-10-07.pdf');

        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_pdf_view_shows_store_period_and_totals(): void
    {
        Setting::setValue('store_name', 'Warung Bu Tini');
        $this->sell('2026-10-01', 20000, 12000, 'lunas', 'qris');
        $this->expense('2026-10-01', 3000, 'listrik');

        $html = (new SalesReport('2026-10-01', '2026-10-07'))->toPdfView()->render();

        $this->assertStringContainsString('Warung Bu Tini', $html);
        $this->assertStringContainsString('01/10/2026 – 07/10/2026', $html);
        $this->assertStringContainsString('Rp 20.000', $html);
        $this->assertStringContainsString('Rp 8.000', $html);
        $this->assertStringContainsString('Rp 5.000', $html);
        $this->assertStringContainsString('Listrik', $html);
        $this->assertStringContainsString('QRIS', $html);
    }

    public function test_export_uses_the_last_seven_days_by_default(): void
    {
        $this->export(['format' => 'csv', 'report' => 'harian'])
            ->assertOk()
            ->assertDownload('laporan-harian_2026-10-09_2026-10-15.csv');
    }

    public function test_invalid_export_requests_are_rejected(): void
    {
        $this->export(['format' => 'xls'])->assertSessionHasErrors('format');
        $this->export(['format' => 'csv', 'report' => 'gaji'])->assertSessionHasErrors('report');
        $this->export(['format' => 'csv', 'report' => 'harian', 'start_date' => '2026-10-10', 'end_date' => '2026-10-01'])->assertSessionHasErrors('end_date');
        $this->export(['format' => 'csv', 'report' => 'harian', 'start_date' => '2024-01-01', 'end_date' => '2026-10-01'])->assertSessionHasErrors('end_date');
    }

    public function test_kasir_can_not_export_reports(): void
    {
        $kasir = User::factory()->create();

        $this->actingAs($kasir)->get('/reports/export?format=csv')->assertForbidden();
    }

    public function test_report_page_offers_the_export_links(): void
    {
        $this->actingAs($this->owner)->get('/reports')
            ->assertOk()
            ->assertSee(route('reports.export'), false)
            ->assertSee('Ekspor');
    }

    public function test_report_page_starts_on_the_store_date_not_utc(): void
    {
        // 06:00 in Jakarta is still the previous day in UTC.
        $this->travelTo(now()->parse('2026-10-15 06:00'));

        $this->actingAs($this->owner)->get('/reports')
            ->assertOk()
            ->assertViewHas('defaultStart', '2026-10-09')
            ->assertViewHas('defaultEnd', '2026-10-15');
    }
}
