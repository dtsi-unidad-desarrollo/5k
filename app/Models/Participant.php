<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'national_id',
        'national_id_clean',
        'bib_number',
        'bib_sequence',
    ];

    /**
     * Get full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Clean national id by removing spaces, dots, hyphens, and non-alphanumeric chars.
     */
    public static function cleanNationalId(?string $id): string
    {
        if (!$id) {
            return '';
        }
        // Extract digits
        $digits = preg_replace('/\D/', '', $id);
        return $digits ?? '';
    }

    /**
     * Format national id with nationality prefix (V- or E-).
     */
    public static function formatNationalId(string $rawId, string $defaultPrefix = 'V'): string
    {
        $clean = trim($rawId);
        // Check if starts with V, E, J, G
        if (preg_match('/^([VEJGvejg])[\s\-\.]*(\d+)$/i', $clean, $matches)) {
            $prefix = strtoupper($matches[1]);
            $number = $matches[2];
            return "{$prefix}-{$number}";
        }

        $digits = preg_replace('/\D/', '', $clean);
        $prefix = strtoupper($defaultPrefix ?: 'V');
        return "{$prefix}-{$digits}";
    }

    /**
     * Find participant by any national id format (raw digits or with prefix).
     */
    public static function findByAnyNationalId(string $search): ?self
    {
        $search = trim($search);
        $cleanDigits = self::cleanNationalId($search);

        if (empty($cleanDigits)) {
            return null;
        }

        return self::where('national_id_clean', $cleanDigits)
            ->orWhere('national_id', 'LIKE', '%' . $cleanDigits)
            ->first();
    }

    /**
     * Generate the next bib sequence and formatted bib number (5K-0001, 5K-0002, etc.).
     * Must be called inside a DB transaction with lock.
     */
    public static function getNextBib(): array
    {
        $maxSeq = self::lockForUpdate()->max('bib_sequence') ?? 0;
        $nextSeq = $maxSeq + 1;
        $bibNumber = sprintf('5K-%04d', $nextSeq);

        return [
            'sequence' => $nextSeq,
            'bib_number' => $bibNumber,
        ];
    }
}
