<?php

namespace App\Imports;

use App\Models\Diagnosis;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class DiagnosesImport extends DefaultValueBinder implements
ToCollection,
WithChunkReading,
WithBatchInserts,
WithCustomValueBinder
{
    private $batchSize = 500;
    private $processed = 0;
    private $totalRows = 0;
    private $startTime;

    public function __construct()
    {
        set_time_limit(7200);
        ini_set('memory_limit', '1024M');
        $this->startTime = microtime(true);
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        $this->totalRows = $rows->count();
        Log::info("Starting import of {$this->totalRows} rows");

        $chunks = $rows->chunk($this->batchSize);
        $chunkCount = 0;

        foreach ($chunks as $chunk) {
            $chunkCount++;
            try {
                DB::transaction(function () use ($chunk, $chunkCount) {
                    $data = [];

                    foreach ($chunk as $row) {
                        $data[] = $this->formatRow($row);
                    }

                    if (!empty($data)) {
                        foreach (array_chunk($data, 100) as $batch) {
                            Diagnosis::insert($batch);
                            $this->processed += count($batch);

                            $progress = ($this->processed / $this->totalRows) * 100;
                            $elapsedTime = microtime(true) - $this->startTime;
                            $estimatedTotalTime = ($elapsedTime / $this->processed) * $this->totalRows;
                            $remainingTime = $estimatedTotalTime - $elapsedTime;

                            Log::info(sprintf(
                                "Progress: %.2f%% (%d/%d) | Est. remaining time: %.2f minutes | Memory usage: %.2f MB",
                                $progress,
                                $this->processed,
                                $this->totalRows,
                                $remainingTime / 60,
                                memory_get_usage(true) / 1024 / 1024
                            ));
                        }
                    }

                    unset($data);
                    gc_collect_cycles();

                }, 5);

            } catch (\Exception $e) {
                Log::error("Error processing chunk {$chunkCount}: " . $e->getMessage());
                sleep(2);
                continue;
            }
        }

        $totalTime = (microtime(true) - $this->startTime) / 60;
        Log::info("Import completed. Total records processed: {$this->processed} in {$totalTime} minutes");
    }

    /**
     * Format row data with error handling and nullable fields
     */
    private function formatRow($row): array
    {
        try {
            // Now accessing numeric indexes since we're not using headers
            // 0 = Column A (code)
            // 1 = Column B (description - merged B & C)
            // 3 = Column D (nf_excel)
            return [
                'code' => isset($row[0]) ? substr(trim($row[0]), 0, 255) : null,
                'description' => isset($row[1]) ? substr(trim($row[1]), 0, 65535) : null,
                'nf_excel' => isset($row[3]) ? substr(trim($row[3]), 0, 255) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        } catch (\Exception $e) {
            Log::error("Error formatting row: " . json_encode($row) . " Error: " . $e->getMessage());
            return [
                'code' => null,
                'description' => null,
                'nf_excel' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
    }

    public function chunkSize(): int
    {
        return $this->batchSize;
    }

    public function batchSize(): int
    {
        return $this->batchSize;
    }

    public function bindValue($cell, $value)
    {
        if (is_string($value)) {
            $value = substr($value, 0, 65535);
        }
        return parent::bindValue($cell, $value);
    }
}
