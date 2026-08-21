<?php

namespace App\Helper;

/**
 * Centralized Date Format Configuration
 * 
 * This class provides reusable and dynamic date format configurations
 * for menu access control. Easy to update for future changes.
 */
class DateFormatConfig
{
    /**
     * Date format type: 'day', 'month-day', 'full'
     * Change this to switch between different formats
     */
    const FORMAT_TYPE = 'day'; // Options: 'day', 'month-day', 'full'

    /**
     * Get validation regex pattern based on format type
     * 
     * @return string Regex pattern
     */
    public static function getValidationPattern(): string
    {
        return match (self::FORMAT_TYPE) {
            'day' => '^(0[1-9]|[12][0-9]|3[01])$',
            'month-day' => '^(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$',
            'full' => '^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$',
            default => '^(0[1-9]|[12][0-9]|3[01])$',
        };
    }

    /**
     * Get validation error message
     * 
     * @param string $field Field name (openAt or closeAt)
     * @return string Error message
     */
    public static function getValidationMessage(string $field): string
    {
        $messages = [
            'day' => [
                'openAt' => 'Format tanggal buka harus DD (contoh: 01 untuk tanggal 1, 15 untuk tanggal 15)',
                'closeAt' => 'Format tanggal tutup harus DD (contoh: 31 untuk tanggal 31)',
            ],
            'month-day' => [
                'openAt' => 'Format tanggal buka harus MM-DD (contoh: 01-15 untuk 15 Januari)',
                'closeAt' => 'Format tanggal tutup harus MM-DD (contoh: 12-31 untuk 31 Desember)',
            ],
            'full' => [
                'openAt' => 'Format tanggal buka harus YYYY-MM-DD (contoh: 2025-01-15)',
                'closeAt' => 'Format tanggal tutup harus YYYY-MM-DD (contoh: 2025-12-31)',
            ],
        ];

        return $messages[self::FORMAT_TYPE][$field] ?? 'Format tanggal tidak valid';
    }

    /**
     * Get placeholder text for input fields
     * 
     * @param string $field Field name (openAt or closeAt)
     * @return string Placeholder text
     */
    public static function getPlaceholder(string $field): string
    {
        $placeholders = [
            'day' => [
                'openAt' => 'Tanggal (contoh: 01)',
                'closeAt' => 'Tanggal (contoh: 31)',
            ],
            'month-day' => [
                'openAt' => 'Bulan-Tanggal (contoh: 01-15)',
                'closeAt' => 'Bulan-Tanggal (contoh: 12-31)',
            ],
            'full' => [
                'openAt' => 'Tahun-Bulan-Tanggal (contoh: 2025-01-15)',
                'closeAt' => 'Tahun-Bulan-Tanggal (contoh: 2025-12-31)',
            ],
        ];

        return $placeholders[self::FORMAT_TYPE][$field] ?? '';
    }

    /**
     * Get HTML pattern attribute value
     * 
     * @return string Pattern for HTML input
     */
    public static function getHtmlPattern(): string
    {
        return match (self::FORMAT_TYPE) {
            'day' => '(0[1-9]|[12][0-9]|3[01])',
            'month-day' => '(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])',
            'full' => '\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])',
            default => '(0[1-9]|[12][0-9]|3[01])',
        };
    }

    /**
     * Get max length for input field
     * 
     * @return int Max length
     */
    public static function getMaxLength(): int
    {
        return match (self::FORMAT_TYPE) {
            'day' => 2,
            'month-day' => 5,
            'full' => 10,
            default => 2,
        };
    }

    /**
     * Get format example for display
     * 
     * @return string Format example
     */
    public static function getFormatExample(): string
    {
        return match (self::FORMAT_TYPE) {
            'day' => 'DD (contoh: 15)',
            'month-day' => 'MM-DD (contoh: 01-15)',
            'full' => 'YYYY-MM-DD (contoh: 2025-01-15)',
            default => 'DD',
        };
    }

    /**
     * Validate date format
     * 
     * @param string|null $date Date string to validate
     * @return bool True if valid
     */
    public static function isValidFormat(?string $date): bool
    {
        if (empty($date)) {
            return true; // Nullable
        }

        $pattern = '/'. self::getValidationPattern() . '/';
        return preg_match($pattern, $date) === 1;
    }

    /**
     * Compare two dates
     * Returns true if closeAt >= openAt
     * 
     * @param string|null $openAt Open date
     * @param string|null $closeAt Close date
     * @return bool True if valid order
     */
    public static function compareDates(?string $openAt, ?string $closeAt): bool
    {
        if (empty($openAt) || empty($closeAt)) {
            return true;
        }

        return match (self::FORMAT_TYPE) {
            'day' => (int)$closeAt >= (int)$openAt,
            'month-day' => self::compareMonthDay($openAt, $closeAt),
            'full' => strtotime($closeAt) >= strtotime($openAt),
            default => (int)$closeAt >= (int)$openAt,
        };
    }

    /**
     * Compare MM-DD format dates
     * 
     * @param string $openAt
     * @param string $closeAt
     * @return bool
     */
    private static function compareMonthDay(string $openAt, string $closeAt): bool
    {
        [$openMonth, $openDay] = explode('-', $openAt);
        [$closeMonth, $closeDay] = explode('-', $closeAt);

        $openTimestamp = mktime(0, 0, 0, (int)$openMonth, (int)$openDay, 2000);
        $closeTimestamp = mktime(0, 0, 0, (int)$closeMonth, (int)$closeDay, 2000);

        return $closeTimestamp >= $openTimestamp;
    }

    /**
     * Get JavaScript configuration
     * Returns configuration as JSON for frontend
     * 
     * @return string JSON configuration
     */
    public static function getJsConfig(): string
    {
        return json_encode([
            'formatType' => self::FORMAT_TYPE,
            'pattern' => self::getValidationPattern(),
            'htmlPattern' => self::getHtmlPattern(),
            'maxLength' => self::getMaxLength(),
            'formatExample' => self::getFormatExample(),
            'placeholders' => [
                'openAt' => self::getPlaceholder('openAt'),
                'closeAt' => self::getPlaceholder('closeAt'),
            ],
        ]);
    }
}
