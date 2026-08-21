<?php
namespace App\Services\Master;

use App\Models\Datacenter\Dokumen;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DokumenService {

    // Maximum file sizes per type (in bytes)
    private const MAX_FILE_SIZES = [
        'pdf' => 4 * 1024 * 1024,   // 2MB for PDFs
        'xlsx' => 2 * 1024 * 1024,  // 2MB for Excel
        'xls' => 2 * 1024 * 1024,   // 2MB for Excel
    ];

    // Allowed MIME types with their corresponding extensions
    private const ALLOWED_MIME_TYPES = [
        'application/pdf' => ['pdf'],
        'application/vnd.ms-excel' => ['xls'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
    ];

    // Dangerous file signatures to detect
    private const DANGEROUS_SIGNATURES = [
        'MZ',           // PE executable
        'PK',           // ZIP-based (could be malicious if not expected)
        '%!PS',         // PostScript
        'GIF87a',       // GIF (if not expected)
        'GIF89a',       // GIF (if not expected)
        "\xFF\xD8\xFF", // JPEG (if not expected)
        "\x89PNG",      // PNG (if not expected)
    ];

    /**
     * Enhanced data validation with security measures
     */
    public function validateData($data) {
        // Sanitize input data
        $data = $this->sanitizeInput($data);

        $validator = Validator::make($data, [
            'judul'       => 'required|string|max:255|regex:/^[a-zA-Z0-9\s\-_\.]+$/',
            'perihal'     => 'required|string|max:1000',
            'masa_berlaku' => 'required|date|after_or_equal:today|before:' . now()->addYears(10)->format('Y-m-d'),
            'tahun'       => 'required|integer|min:2000|max:' . (date('Y') + 5),
            'file_dokumen'=> 'required|file|mimes:pdf,xls,xlsx|max:2048', // Max 4MB
        ], [
            'judul.regex'               => 'Judul hanya boleh mengandung huruf, angka, spasi, tanda hubung, underscore, dan titik.',
            'judul.max'                 => 'Judul maksimal 255 karakter.',
            'perihal.max'               => 'Perihal maksimal 1000 karakter.',
            'masa_berlaku.before'       => 'Masa berlaku tidak boleh lebih dari 10 tahun ke depan.',
            'file_dokumen.mimes'        => 'Format file harus berupa PDF, XLS, dan XLSX.',
            'file_dokumen.max'          => 'Ukuran file maksimal adalah 4MB.',
            'file_dokumen.required'     => 'File dokumen harus diunggah.',
            'file_dokumen.file'         => 'File dokumen tidak valid.',
            'tahun.required'            => 'Tahun dokumen harus diisi.',
            'tahun.min'                 => 'Tahun harus minimal 2000.',
            'tahun.max'                 => 'Tahun tidak boleh lebih dari 5 tahun ke depan.',
            'judul.required'            => 'Judul dokumen harus diisi.',
            'perihal.required'          => 'Perihal dokumen harus diisi.',
            'masa_berlaku.required'     => 'Masa berlaku dokumen harus diisi.',
            'masa_berlaku.after_or_equal' => 'Masa berlaku harus tanggal hari ini atau yang akan datang.',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first(), 422);
        }

        return $validator->validated();
    }

    /**
     * Sanitize input data to prevent various attacks
     */
    private function sanitizeInput(array $data): array {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Remove null bytes
                $value = str_replace(chr(0), '', $value);

                // Remove control characters except tab, newline, carriage return
                $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);

                // Trim whitespace
                $value = trim($value);

                // For specific fields, apply additional sanitization
                switch ($key) {
                    case 'judul':
                        // Remove HTML tags and special characters for title
                        $value = strip_tags($value);
                        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                        break;
                    case 'perihal':
                        // Allow some formatting for description but escape HTML
                        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                        break;
                }

                $data[$key] = $value;
            }
        }

        return $data;
    }
    /**
     * Enhanced file validation with comprehensive security checks
     */
    public function validateAndGenerateDocProperties($doc) {
        if (!$doc || !$doc->isValid()) {
            throw new \InvalidArgumentException("File tidak valid atau rusak.", 400);
        }

        $originalName = $doc->getClientOriginalName();
        $extension = strtolower($doc->getClientOriginalExtension());
        $fileSize = $doc->getSize();
        $mimeType = $doc->getMimeType();

        // Generate secure filename
        $id = Str::random(16);
        $timestamp = time();
        $cleanName = $this->sanitizeFileName(pathinfo($originalName, PATHINFO_FILENAME));
        $fileName = sprintf('%s-%s-%s-%s.%s', $id, $timestamp, $cleanName, Str::random(8), $extension);

        // Enhanced security validations
        $this->validateFileStructure($originalName, $extension, $mimeType, $fileSize);
        $this->validateFileContent($doc, $extension, $mimeType);
        $this->scanForMaliciousContent($doc);

        return [
            'originalName' => $originalName,
            'fileName'     => $fileName,
            'extension'    => $extension,
            'fileSize'     => $fileSize,
            'mimeType'     => $mimeType,
        ];
    }

    /**
     * Validate file structure and basic properties
     */
    private function validateFileStructure(string $originalName, string $extension, string $mimeType, int $fileSize): void {
        // Check if extension is allowed
        if (!array_key_exists($mimeType, self::ALLOWED_MIME_TYPES)) {
            throw new \InvalidArgumentException("Tipe MIME tidak diizinkan: {$mimeType}", 415);
        }

        // Verify extension matches MIME type
        if (!in_array($extension, self::ALLOWED_MIME_TYPES[$mimeType])) {
            throw new \InvalidArgumentException("Ekstensi file tidak sesuai dengan tipe MIME.", 415);
        }

        // Check file size based on type
        $maxSize = self::MAX_FILE_SIZES[$extension] ?? 2 * 1024 * 1024; // Default 2MB
        if ($fileSize > $maxSize) {
            $maxSizeMB = round($maxSize / (1024 * 1024), 1);
            throw new \InvalidArgumentException("Ukuran file melebihi batas {$maxSizeMB}MB untuk tipe {$extension}.", 413);
        }

        // Check for dangerous filename patterns
        $this->validateFileName($originalName);
    }

    /**
     * Validate filename for security issues
     */
    private function validateFileName(string $filename): void {
        // Remove null bytes
        $filename = str_replace(chr(0), '', $filename);

        // Check for path traversal attempts
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            throw new \InvalidArgumentException("Nama file mengandung karakter berbahaya.", 400);
        }

        // Check for dangerous extensions (double extension attacks)
        if (preg_match('/\.(php|phtml|phar|exe|sh|pl|py|cgi|asp|jsp|html|js|svg|ini|log|bak|cmd|bat|com|scr|vbs|jar)(\.|$)/i', $filename)) {
            throw new \InvalidArgumentException("Nama file mengandung ekstensi berbahaya.", 415);
        }

        // Check for Unicode direction override characters (spoofing attacks)
        if (preg_match('/[\x{202A}-\x{202E}\x{2066}-\x{2069}]/u', $filename)) {
            throw new \InvalidArgumentException("Nama file mengandung karakter Unicode berbahaya.", 400);
        }

        // Check filename length
        if (strlen($filename) > 255) {
            throw new \InvalidArgumentException("Nama file terlalu panjang.", 400);
        }

        // Check for reserved names (Windows)
        $reservedNames = ['CON', 'PRN', 'AUX', 'NUL', 'COM1', 'COM2', 'COM3', 'COM4', 'COM5', 'COM6', 'COM7', 'COM8', 'COM9', 'LPT1', 'LPT2', 'LPT3', 'LPT4', 'LPT5', 'LPT6', 'LPT7', 'LPT8', 'LPT9'];
        $baseName = strtoupper(pathinfo($filename, PATHINFO_FILENAME));
        if (in_array($baseName, $reservedNames)) {
            throw new \InvalidArgumentException("Nama file menggunakan nama yang direservasi sistem.", 400);
        }
    }

    /**
     * Sanitize filename for safe storage
     */
    private function sanitizeFileName(string $filename): string {
        // Remove or replace dangerous characters
        $filename = preg_replace('/[^\w\s\-\.]/', '', $filename);
        $filename = preg_replace('/\s+/', '-', $filename);
        $filename = trim($filename, '-');

        // Ensure filename is not empty
        if (empty($filename)) {
            $filename = 'document';
        }

        // Limit length
        return substr($filename, 0, 50);
    }

    /**
     * Validate file content and structure
     */
    private function validateFileContent($doc, string $extension, string $mimeType): void {
        $filePath = $doc->getPathname();

        // Read file header for magic number validation
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            throw new \InvalidArgumentException("Tidak dapat membaca file.", 400);
        }

        $header = fread($handle, 1024); // Read first 1KB
        fclose($handle);

        // Validate file signature
        $this->validateFileSignature($header, $extension, $mimeType);

        // Type-specific validations
        switch ($extension) {
            case 'pdf':
                $this->validatePDFStructure($filePath, $header);
                break;
            case 'xlsx':
                $this->validateOfficeStructure($filePath);
                break;
            case 'xls':
                $this->validateLegacyOfficeStructure($header);
                break;
        }
    }

    /**
     * Validate file signature (magic numbers)
     */
    private function validateFileSignature(string $header, string $extension, string $mimeType): void {
        $signatures = [
            'pdf' => ['%PDF-'],
            'xlsx' => ['PK'],
            'xls' => ["\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1"],
        ];

        if (!isset($signatures[$extension])) {
            throw new \InvalidArgumentException("Tipe file tidak didukung.", 415);
        }

        $validSignature = false;
        foreach ($signatures[$extension] as $signature) {
            if (substr($header, 0, strlen($signature)) === $signature) {
                $validSignature = true;
                break;
            }
        }

        if (!$validSignature) {
            throw new \InvalidArgumentException("Struktur file tidak valid atau file rusak.", 415);
        }
    }

    /**
     * Enhanced PDF validation
     */
    private function validatePDFStructure(string $filePath, string $header): void {
        // Check PDF header
        if (substr($header, 0, 5) !== '%PDF-') {
            throw new \InvalidArgumentException("File PDF tidak valid.", 415);
        }

        // Read entire file for security scanning
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new \InvalidArgumentException("Tidak dapat membaca file PDF.", 400);
        }

        // Check for embedded JavaScript (security risk)
        if (preg_match('/(\/JavaScript|\/JS\s*<<|\/OpenAction|\/Launch|\/SubmitForm|\/ImportData)/i', $content)) {
            throw new \InvalidArgumentException("PDF mengandung konten berbahaya (JavaScript atau aksi otomatis).", 415);
        }

        // Check for suspicious PDF objects
        if (preg_match('/(\/GoToR|\/GoToE|\/Movie|\/Sound|\/3D|\/RichMedia)/i', $content)) {
            throw new \InvalidArgumentException("PDF mengandung objek yang berpotensi berbahaya.", 415);
        }

        // Check for embedded files
        if (preg_match('/\/EmbeddedFile/i', $content)) {
            throw new \InvalidArgumentException("PDF mengandung file tertanam yang tidak diizinkan.", 415);
        }

        // Basic PDF structure validation
        if (!preg_match('/%%EOF\s*$/', $content)) {
            throw new \InvalidArgumentException("Struktur PDF tidak lengkap.", 415);
        }
    }

    /**
     * Validate Office document structure (XLSX, DOCX)
     */
    private function validateOfficeStructure(string $filePath): void {
        $zip = new \ZipArchive();
        $result = $zip->open($filePath);

        if ($result !== true) {
            throw new \InvalidArgumentException("Struktur dokumen Office tidak valid.", 415);
        }

        // Check for required files
        $requiredFiles = ['[Content_Types].xml', '_rels/.rels'];
        foreach ($requiredFiles as $file) {
            if ($zip->locateName($file) === false) {
                $zip->close();
                throw new \InvalidArgumentException("Struktur dokumen Office tidak lengkap.", 415);
            }
        }

        // Scan for potentially dangerous content
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);

            // Check for dangerous file types in archive
            if (preg_match('/\.(exe|dll|bat|cmd|com|scr|vbs|js|jar|php)$/i', $filename)) {
                $zip->close();
                throw new \InvalidArgumentException("Dokumen mengandung file berbahaya: {$filename}", 415);
            }

            // Check for macro-related files (potential security risk)
            if (preg_match('/vbaProject\.bin|macros/i', $filename)) {
                $zip->close();
                throw new \InvalidArgumentException("Dokumen mengandung makro yang tidak diizinkan.", 415);
            }
        }

        $zip->close();
    }

    /**
     * Validate legacy Office documents (XLS, DOC)
     */
    private function validateLegacyOfficeStructure(string $header): void {
        // OLE2 signature validation
        $ole2Signature = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";
        if (substr($header, 0, 8) !== $ole2Signature) {
            throw new \InvalidArgumentException("Struktur dokumen Office lama tidak valid.", 415);
        }
    }

    /**
     * Scan for malicious content
     */
    private function scanForMaliciousContent($doc): void {
        $filePath = $doc->getPathname();
        $content = file_get_contents($filePath);

        if ($content === false) {
            throw new \InvalidArgumentException("Tidak dapat membaca file untuk pemindaian keamanan.", 400);
        }

        // Check for known malicious signatures
        foreach (self::DANGEROUS_SIGNATURES as $signature) {
            if (strpos($content, $signature) === 0) {
                Log::warning('Malicious file signature detected', [
                    'signature' => bin2hex($signature),
                    'user_id' => Auth::id(),
                    'filename' => $doc->getClientOriginalName()
                ]);
                throw new \InvalidArgumentException("File mengandung konten berbahaya.", 415);
            }
        }

        // Check for suspicious patterns
        $suspiciousPatterns = [
            '/eval\s*\(/i',          // JavaScript eval
            '/document\.write/i',     // Document manipulation
            '/window\.location/i',    // Redirection
            '/<script/i',            // Script tags
            '/on\w+\s*=/i',          // Event handlers
            '/javascript:/i',        // JavaScript protocol
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                Log::warning('Suspicious content pattern detected', [
                    'pattern' => $pattern,
                    'user_id' => Auth::id(),
                    'filename' => $doc->getClientOriginalName()
                ]);
                throw new \InvalidArgumentException("File mengandung konten yang mencurigakan.", 415);
            }
        }

        // File size vs content ratio check (compressed bombs)
        $compressionRatio = strlen($content) / $doc->getSize();
        if ($compressionRatio > 100) { // If uncompressed is 100x larger than compressed
            throw new \InvalidArgumentException("File memiliki rasio kompresi yang mencurigakan.", 415);
        }

        // Enhanced security scans
        $this->performAdvancedSecurityScan($doc, $content);
    }

    /**
     * Perform advanced security scanning
     */
    private function performAdvancedSecurityScan($doc, string $content): void {
        $filePath = $doc->getPathname();
        $extension = strtolower($doc->getClientOriginalExtension());

        // Check file entropy (high entropy may indicate encryption/obfuscation)
        $this->checkFileEntropy($content, $extension);

        // Check for polyglot files (files that are valid in multiple formats)
        $this->checkPolyglotFile($filePath, $extension);

        // Check file metadata for suspicious content
        $this->checkFileMetadata($filePath, $extension);

        // Log the security scan
        Log::info('Advanced security scan completed', [
            'user_id' => Auth::id(),
            'filename' => $doc->getClientOriginalName(),
            'size' => $doc->getSize(),
            'mime_type' => $doc->getMimeType()
        ]);
    }

    /**
     * Check file entropy to detect potential obfuscation
     */
    private function checkFileEntropy(string $content, string $extension): void {
        // Skip entropy check for compressed formats that naturally have high entropy
        if (in_array($extension, ['xlsx'])) {
            return;
        }

        // Calculate Shannon entropy
        $counts = array_count_values(str_split($content));
        $length = strlen($content);
        $entropy = 0;

        foreach ($counts as $count) {
            $probability = $count / $length;
            $entropy -= $probability * log($probability, 2);
        }

        // High entropy threshold (suspicious for non-compressed files)
        $threshold = $extension === 'pdf' ? 7.9 : 6.5;

        if ($entropy > $threshold) {
            Log::warning('High entropy file detected', [
                'entropy' => $entropy,
                'threshold' => $threshold,
                'extension' => $extension,
                'user_id' => Auth::id()
            ]);
            throw new \InvalidArgumentException("File menunjukkan karakteristik yang mencurigakan (entropy tinggi)", 415);
        }
    }

    /**
     * Check for polyglot files
     */
    private function checkPolyglotFile(string $filePath, string $extension): void {
        $handle = fopen($filePath, 'rb');
        if (!$handle)
            throw new \InvalidArgumentException("Tidak dapat membaca file untuk analisis polyglot", 400);

        // Baca header (4KB pertama)
        $header = fread($handle, 4096);

        // Baca footer (2KB terakhir)
        $footer = '';
        $fileSize = filesize($filePath);
        if ($fileSize > 2048) {
            fseek($handle, -2048, SEEK_END);
            $footer = fread($handle, 2048);
        }
        fclose($handle);

        // Signature dasar
        $signatures = [
            'pdf'   => ['sig' => '%PDF-', 'offset' => 0],
            'jpg'   => ['sig' => "\xFF\xD8\xFF", 'offset' => 0],
            'png'   => ['sig' => "\x89PNG", 'offset' => 0],
            'gif87a'=> ['sig' => 'GIF87a', 'offset' => 0],
            'gif89a'=> ['sig' => 'GIF89a', 'offset' => 0],
            'zip'   => ['sig' => 'PK', 'offset' => 0],
            'exe'   => ['sig' => 'MZ', 'offset' => 0],
            'html'  => ['sig' => '<!DOCTYPE', 'offset' => null],
        ];

        $detectedTypes = [];

        // 🔍 Cek signature utama
        foreach ($signatures as $type => $def) {
            $sig = $def['sig'];
            $offset = $def['offset'];

            if ($offset === 0) {
                if (substr($header, 0, strlen($sig)) === $sig) {
                    $detectedTypes[] = $type;
                }
            } else {
                if (strpos($header, $sig) !== false || strpos($footer, $sig) !== false) {
                    $detectedTypes[] = $type;
                }
            }
        }

        // 🔍 Tambahan deteksi DOCX (DOCX = ZIP + harus punya file khas Word)
        if (in_array('zip', $detectedTypes, true)) {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) === true) {
                // Kalau file ZIP punya file khas Word
                if ($zip->locateName('word/document.xml') !== false &&
                    $zip->locateName('[Content_Types].xml') !== false) {
                    $detectedTypes[] = 'docx';
                }
                $zip->close();
            }
        }

        $detectedTypes = array_unique($detectedTypes);

        // 🚨 Kalau lebih dari satu tipe → polyglot mencurigakan
        if (count($detectedTypes) > 1) {
            Log::warning('Polyglot file detected', [
                'detected_types' => $detectedTypes,
                'expected_type' => $extension,
                'user_id' => Auth::id(),
                'file' => $filePath,
            ]);
            throw new \InvalidArgumentException("File menunjukkan karakteristik polyglot yang mencurigakan", 415);
        }
    }


    /**
     * Check file metadata for suspicious content
     */
    private function checkFileMetadata(string $filePath, string $extension): void {
        if ($extension === 'pdf') {
            $this->checkPDFMetadata($filePath);
        }
    }

    /**
     * Check PDF metadata for security issues
     */
    private function checkPDFMetadata(string $filePath): void {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return;
        }

        // Check for suspicious metadata
        $suspiciousPatterns = [
            '/\/Creator\s*\([^)]*script[^)]*\)/i',
            '/\/Producer\s*\([^)]*hack[^)]*\)/i',
            '/\/Title\s*\([^)]*<script[^)]*\)/i',
            '/\/Author\s*\([^)]*malware[^)]*\)/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                Log::warning('Suspicious PDF metadata detected', [
                    'pattern' => $pattern,
                    'user_id' => Auth::id()
                ]);
                throw new \InvalidArgumentException("PDF mengandung metadata yang mencurigakan", 415);
            }
        }
    }

    /**
     * Quarantine and verify uploaded file
     */
    public function quarantineAndVerifyFile($file, array $doc, string $finalPath): string {
        // Store in quarantine first
        $quarantinePath = storage_path('app/private/quarantine/');
        $tempPath = $file->storeAs($quarantinePath, $doc["fileName"]);

        if (!$tempPath) {
            throw new \InvalidArgumentException("Gagal mengunggah dokumen ke quarantine", 500);
        }

        // Perform final security verification on stored file
        $this->verifyStoredFile($tempPath);

        // Move from quarantine to final location
        if (!Storage::move($tempPath, $finalPath)) {
            Storage::delete($tempPath); // Cleanup quarantine
            throw new \InvalidArgumentException("Gagal memindahkan file ke lokasi final", 500);
        }

        if (!Storage::exists($finalPath)) {
            throw new \InvalidArgumentException("Verifikasi penyimpanan file gagal", 500);
        }

        return $finalPath;
    }

    /**
     * Verify stored file integrity and security
     */
    private function verifyStoredFile(string $storedPath): void {
        if (!Storage::exists($storedPath)) {
            throw new \InvalidArgumentException("File tidak ditemukan setelah penyimpanan", 500);
        }

        $fullPath = Storage::path($storedPath);

        // Verify file still has expected properties
        if (!is_readable($fullPath)) {
            Storage::delete($storedPath);
            throw new \InvalidArgumentException("File tidak dapat dibaca setelah penyimpanan", 500);
        }

        // Check file size hasn't changed (corruption check)
        $currentSize = filesize($fullPath);
        if ($currentSize === false || $currentSize === 0) {
            Storage::delete($storedPath);
            throw new \InvalidArgumentException("File rusak setelah penyimpanan", 500);
        }

        // Re-verify file signature on stored file
        $handle = fopen($fullPath, 'rb');
        if (!$handle) {
            Storage::delete($storedPath);
            throw new \InvalidArgumentException("Tidak dapat verifikasi file yang disimpan", 500);
        }

        $header = fread($handle, 256);
        fclose($handle);

        // Basic signature check
        $extension = pathinfo(basename($storedPath), PATHINFO_EXTENSION);
        if ($extension === 'pdf' && substr($header, 0, 5) !== '%PDF-') {
            Storage::delete($storedPath);
            throw new \InvalidArgumentException("File signature berubah setelah penyimpanan", 500);
        }
    }

    /**
     * Cleanup quarantine files
     */
    public function cleanupQuarantineFile(string $fileName): void {
        $quarantinePath = storage_path('app/private/quarantine/' . $fileName);
        if (Storage::exists($quarantinePath)) {
            Storage::delete($quarantinePath);
            Log::info('Cleaned up quarantine file', [
                'path' => $quarantinePath,
                'user_id' => Auth::id()
            ]);
        }
    }
    /**
     * Create document record with enhanced security
     */
    public function createData($data) {
        // Additional data sanitization
        $sanitizedData = [
            'judul' => htmlspecialchars($data['judul'], ENT_QUOTES, 'UTF-8'),
            'perihal' => htmlspecialchars($data['perihal'], ENT_QUOTES, 'UTF-8'),
            'masa_berlaku' => $data['masa_berlaku'],
            'tahun' => (int) $data['tahun'],
            'tipe_file' => strtolower($data['tipe_file']),
            'ukuran_file' => (int) $data['ukuran_file'],
            'file' => basename($data['file']), // Ensure only filename, no path
            'uploaded_by' => $data['uploaded_by'] ?? Auth::id(),
            'uploaded_at' => $data['uploaded_at'] ?? now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return Dokumen::create($sanitizedData);
    }

    /**
     * Get documents with security filtering
     */
    public function getData() {
        return Dokumen::where("is_deleted", "false")
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get document by ID with enhanced security
     */
    public function getDokumenById(int $id) {
        $data = Dokumen::where("id", $id)
            ->where("is_deleted", "false")
            ->first();

        if (!$data)
            throw new \InvalidArgumentException("Dokumen tidak ditemukan.", 404);

        // Secure filename handling
        $fileName = basename($data->file); // Prevent directory traversal
        $filePath = "privatee/master/" . $fileName;

        if (!Storage::exists($filePath)) {
            Log::error('File not found in storage', [
                'document_id' => $id,
                'file_path' => $filePath,
                'user_id' => Auth::id()
            ]);
            throw new \Exception("File dokumen tidak ditemukan di storage.", 404);
        }

        // Get full file path for serving
        $fullPath = storage_path('app/' . $filePath);

        // Verify file is within allowed directory
        $realPath = realpath($fullPath);
        $allowedPath = realpath(storage_path('app/privatee/master/'));

        if (!$realPath || !Str::startsWith($realPath, $allowedPath)) {
            Log::warning('Directory traversal attempt detected in getDokumenById', [
                'document_id' => $id,
                'requested_path' => $fullPath,
                'real_path' => $realPath,
                'allowed_path' => $allowedPath,
                'user_id' => Auth::id()
            ]);
            throw new \Exception("Akses file ditolak.", 403);
        }

        // Security headers for file serving
        $headers = [
            'Content-Type' => mime_content_type($realPath) ?: 'application/octet-stream',
            'Content-Security-Policy' => "default-src 'none'; object-src 'none'; script-src 'none';",
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];

        return response()->file($realPath, $headers);
    }

    /**
     * Get document data with security validation
     */
    public function getDokumenData(int $id) {
        return Dokumen::where("id", $id)
            ->where("is_deleted", "false")
            ->first();
    }
}
