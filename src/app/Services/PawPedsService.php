<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\RequestException;

class PawPedsService
{
    protected $baseUrl = 'https://pawpeds.com';
    protected $maxRetries = 3;
    protected $timeout = 15; // seconds
    protected $cacheTime = 3600; // 1 hour cache
    protected $maxRegNumberLength = 50;
    protected $maxNameLength = 100;

    /**
     * Fetch cat data from PawPeds database with comprehensive error handling
     *
     * @param string $regNumber Registration number to search
     * @param string $breed Breed code (e.g., 'nfo' for Norwegian Forest Cat)
     * @return array|null
     * @throws \InvalidArgumentException
     */
    public function fetchCatData(string $regNumber, string $breed = 'nfo')
    {
        $startTime = microtime(true);

        try {
            // Validate inputs
            $this->validateRegistrationNumber($regNumber);
            $this->validateBreedCode($breed);

            // Check cache first
            $cacheKey = "pawpeds_{$breed}_{$regNumber}";
            if (Cache::has($cacheKey)) {
                Log::info('PawPeds cache hit', ['regNumber' => $regNumber]);
                return Cache::get($cacheKey);
            }

            Log::info('PawPeds fetch started', [
                'regNumber' => $regNumber,
                'breed' => $breed
            ]);

            // Fetch with retry logic
            $response = $this->fetchWithRetry($regNumber, $breed);

            if (!$response) {
                Log::warning('PawPeds fetch returned no response', [
                    'regNumber' => $regNumber,
                    'breed' => $breed
                ]);
                return null;
            }

            if (!$response->successful()) {
                Log::warning('PawPeds fetch failed', [
                    'status' => $response->status(),
                    'regNumber' => $regNumber,
                    'breed' => $breed,
                    'reason' => $response->reason()
                ]);
                return null;
            }

            $body = $response->body();
            if (empty($body)) {
                Log::warning('PawPeds returned empty body', [
                    'regNumber' => $regNumber
                ]);
                return null;
            }

            // Parse the HTML
            $data = $this->parsePawPedsHtml($body);

            if (empty($data)) {
                Log::info('PawPeds parsing resulted in empty data', [
                    'regNumber' => $regNumber
                ]);
                return null;
            }

            // Validate extracted data
            $data = $this->validateAndSanitizeData($data);

            // Cache the successful result
            Cache::put($cacheKey, $data, $this->cacheTime);

            $duration = round((microtime(true) - $startTime) * 1000, 2);
            Log::info('PawPeds fetch successful', [
                'regNumber' => $regNumber,
                'fieldsExtracted' => count($data),
                'durationMs' => $duration
            ]);

            return $data;

        } catch (\InvalidArgumentException $e) {
            Log::error('PawPeds invalid input', [
                'error' => $e->getMessage(),
                'regNumber' => $regNumber,
                'breed' => $breed
            ]);
            throw $e;

        } catch (RequestException $e) {
            Log::error('PawPeds HTTP request exception', [
                'error' => $e->getMessage(),
                'regNumber' => $regNumber,
                'statusCode' => $e->response ? $e->response->status() : null
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('PawPeds fetch unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'regNumber' => $regNumber,
                'breed' => $breed
            ]);
            return null;
        }
    }

    /**
     * Validate registration number
     *
     * @param string $regNumber
     * @throws \InvalidArgumentException
     */
    protected function validateRegistrationNumber(string $regNumber)
    {
        if (empty($regNumber)) {
            throw new \InvalidArgumentException('Registration number cannot be empty');
        }

        if (strlen($regNumber) > $this->maxRegNumberLength) {
            throw new \InvalidArgumentException("Registration number too long (max {$this->maxRegNumberLength} chars)");
        }

        // Check for potentially malicious input
        if (preg_match('/[<>"\']/', $regNumber)) {
            throw new \InvalidArgumentException('Registration number contains invalid characters');
        }

        // Additional validation: registration numbers are typically alphanumeric
        if (!preg_match('/^[A-Z0-9\s\-\/]+$/i', $regNumber)) {
            throw new \InvalidArgumentException('Registration number format invalid');
        }
    }

    /**
     * Validate breed code
     *
     * @param string $breed
     * @throws \InvalidArgumentException
     */
    protected function validateBreedCode(string $breed)
    {
        if (empty($breed)) {
            throw new \InvalidArgumentException('Breed code cannot be empty');
        }

        // Breed codes are typically 2-3 letter codes
        if (!preg_match('/^[a-z]{2,5}$/i', $breed)) {
            throw new \InvalidArgumentException('Invalid breed code format');
        }
    }

    /**
     * Fetch with retry logic and exponential backoff
     *
     * @param string $regNumber
     * @param string $breed
     * @return \Illuminate\Http\Client\Response|null
     */
    protected function fetchWithRetry(string $regNumber, string $breed)
    {
        $delays = [0, 1000, 2000, 4000]; // milliseconds
        $lastException = null;

        for ($attempt = 0; $attempt <= $this->maxRetries; $attempt++) {
            try {
                if ($attempt > 0) {
                    Log::info("PawPeds retry attempt {$attempt}/{$this->maxRetries}", [
                        'regNumber' => $regNumber
                    ]);

                    // Wait before retry
                    usleep($delays[$attempt] * 1000); // Convert to microseconds
                }

                // PawPeds URL pattern: https://pawpeds.com/db/?a=p&id=REGNO&g=4&p=breed&date=newest
                $url = "{$this->baseUrl}/db/";

                $response = Http::timeout($this->timeout)
                    ->retry(1, 100) // Laravel's built-in retry
                    ->withHeaders([
                        'User-Agent' => 'FindACat/1.0 (+https://yoursite.com)',
                        'Accept' => 'text/html,application/xhtml+xml',
                        'Accept-Language' => 'en-US,en;q=0.9'
                    ])
                    ->get($url, [
                        'a' => 'p',
                        'id' => $regNumber,
                        'g' => '4', // 4 generations
                        'p' => $breed,
                        'date' => 'newest'
                    ]);

                // If we got here, request succeeded
                return $response;

            } catch (RequestException $e) {
                $lastException = $e;

                // Don't retry on client errors (4xx)
                if ($e->response && $e->response->status() >= 400 && $e->response->status() < 500) {
                    Log::warning('PawPeds client error, not retrying', [
                        'status' => $e->response->status(),
                        'regNumber' => $regNumber
                    ]);
                    throw $e;
                }

                if ($attempt < $this->maxRetries) {
                    Log::warning("PawPeds request failed, will retry", [
                        'attempt' => $attempt + 1,
                        'maxRetries' => $this->maxRetries,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }

            } catch (\Exception $e) {
                $lastException = $e;

                if ($attempt < $this->maxRetries) {
                    Log::warning("PawPeds unexpected error, will retry", [
                        'attempt' => $attempt + 1,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }
        }

        // All retries failed
        Log::error('PawPeds all retries exhausted', [
            'regNumber' => $regNumber,
            'lastError' => $lastException ? $lastException->getMessage() : 'Unknown'
        ]);

        return null;
    }

    /**
     * Parse PawPeds HTML response to extract cat data with enhanced error handling
     *
     * @param string $html
     * @return array
     */
    protected function parsePawPedsHtml(string $html)
    {
        try {
            if (empty($html)) {
                Log::warning('Empty HTML provided for parsing');
                return [];
            }

            $data = [
                'full_name' => null,
                'ems_color' => null,
                'breed' => null,
                'dob' => null,
                'gender' => null,
                'sire_name' => null,
                'sire_reg' => null,
                'dam_name' => null,
                'dam_reg' => null,
                'titles_before_name' => null,
                'titles_after_name' => null,
                'breeder' => null,
                'owner' => null,
            ];

            // Check if page indicates "not found"
            if (stripos($html, 'not found') !== false || stripos($html, 'no results') !== false) {
                Log::info('PawPeds indicated no results found');
                return [];
            }

            // Create DOMDocument to parse HTML with error suppression
            $dom = new \DOMDocument();
            $oldErrorLevel = error_reporting(0);

            try {
                $loadSuccess = $dom->loadHTML(
                    mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'),
                    LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING
                );

                if (!$loadSuccess) {
                    Log::warning('Failed to load HTML into DOMDocument');
                    return [];
                }

                $xpath = new \DOMXPath($dom);

            } catch (\Exception $e) {
                Log::error('DOM parsing error', [
                    'error' => $e->getMessage(),
                    'htmlLength' => strlen($html)
                ]);
                return [];
            } finally {
                error_reporting($oldErrorLevel);
            }

            // Extract cat name (usually in a header or prominent div)
            try {
                $nameNodes = $xpath->query("//div[@class='cat-name']|//h1|//h2");
                if ($nameNodes && $nameNodes->length > 0) {
                    $fullText = trim($nameNodes->item(0)->textContent);
                    if (!empty($fullText)) {
                        $data['full_name'] = $this->extractCatName($fullText);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Error extracting cat name', ['error' => $e->getMessage()]);
            }

            // Extract EMS color code (usually after name)
            try {
                $emsPattern = '/\[([A-Z]{3}\s*\d*[a-z]*)\]/';
                if (preg_match($emsPattern, $html, $matches)) {
                    $data['ems_color'] = $this->sanitizeString(trim($matches[1]));
                }
            } catch (\Exception $e) {
                Log::warning('Error extracting EMS color', ['error' => $e->getMessage()]);
            }

            // Extract date of birth (look for date patterns)
            try {
                $dobPattern = '/(?:born|dob|birth)[:\s]*(\d{4}[-\/]\d{2}[-\/]\d{2}|\d{2}[-\/]\d{2}[-\/]\d{4})/i';
                if (preg_match($dobPattern, $html, $matches)) {
                    $data['dob'] = $this->normalizeDate($matches[1]);
                }
            } catch (\Exception $e) {
                Log::warning('Error extracting DOB', ['error' => $e->getMessage()]);
            }

            // Extract gender from context
            try {
                if (preg_match('/\b(male|female|M|F)\b/i', $html, $matches)) {
                    $gender = strtolower($matches[1]);
                    $data['gender'] = (in_array($gender, ['male', 'm'])) ? 1 : 2;
                }
            } catch (\Exception $e) {
                Log::warning('Error extracting gender', ['error' => $e->getMessage()]);
            }

            // Extract sire (father) information
            try {
                $sirePattern = '/(?:sire|father)[:\s]*([^\n<]+?)(?:\[([^\]]+)\])?/i';
                if (preg_match($sirePattern, $html, $matches)) {
                    $data['sire_name'] = $this->sanitizeString(trim($matches[1]));
                    if (isset($matches[2])) {
                        $data['sire_reg'] = $this->sanitizeString(trim($matches[2]));
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Error extracting sire info', ['error' => $e->getMessage()]);
            }

            // Extract dam (mother) information
            try {
                $damPattern = '/(?:dam|mother)[:\s]*([^\n<]+?)(?:\[([^\]]+)\])?/i';
                if (preg_match($damPattern, $html, $matches)) {
                    $data['dam_name'] = $this->sanitizeString(trim($matches[1]));
                    if (isset($matches[2])) {
                        $data['dam_reg'] = $this->sanitizeString(trim($matches[2]));
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Error extracting dam info', ['error' => $e->getMessage()]);
            }

            // Extract breeder information
            try {
                $breederPattern = '/(?:breeder)[:\s]*([^\n<]+)/i';
                if (preg_match($breederPattern, $html, $matches)) {
                    $data['breeder'] = $this->sanitizeString(trim($matches[1]));
                }
            } catch (\Exception $e) {
                Log::warning('Error extracting breeder', ['error' => $e->getMessage()]);
            }

            // Extract owner information
            try {
                $ownerPattern = '/(?:owner)[:\s]*([^\n<]+)/i';
                if (preg_match($ownerPattern, $html, $matches)) {
                    $data['owner'] = $this->sanitizeString(trim($matches[1]));
                }
            } catch (\Exception $e) {
                Log::warning('Error extracting owner', ['error' => $e->getMessage()]);
            }

            // Extract titles
            try {
                $titlePattern = '/^(CH|GC|IC|EC|INT|DK|SE|NO|FI|DM|DSM|GIP|GIC|GP|SGC|RW|BW|NW|CW)\s+/';
                if (!empty($data['full_name']) && preg_match($titlePattern, $data['full_name'], $matches)) {
                    $data['titles_before_name'] = $this->sanitizeString(trim($matches[1]));
                    $data['full_name'] = $this->sanitizeString(trim(preg_replace($titlePattern, '', $data['full_name'])));
                }
            } catch (\Exception $e) {
                Log::warning('Error extracting titles', ['error' => $e->getMessage()]);
            }

            // Remove null values and return
            return array_filter($data, function($value) {
                return $value !== null && $value !== '';
            });

        } catch (\Exception $e) {
            Log::error('Critical error in HTML parsing', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Validate and sanitize extracted data
     *
     * @param array $data
     * @return array
     */
    protected function validateAndSanitizeData(array $data)
    {
        try {
            $sanitized = [];

            foreach ($data as $key => $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                // Type-specific validation and sanitization
                switch ($key) {
                    case 'full_name':
                    case 'sire_name':
                    case 'dam_name':
                    case 'breeder':
                    case 'owner':
                        // Sanitize and validate name fields
                        $clean = $this->sanitizeString($value);
                        if (strlen($clean) <= $this->maxNameLength) {
                            $sanitized[$key] = $clean;
                        } else {
                            Log::warning("Field {$key} too long, truncating", [
                                'original_length' => strlen($clean)
                            ]);
                            $sanitized[$key] = substr($clean, 0, $this->maxNameLength);
                        }
                        break;

                    case 'dob':
                        // Validate date format
                        if ($this->isValidDate($value)) {
                            $sanitized[$key] = $value;
                        } else {
                            Log::warning('Invalid date format', ['value' => $value]);
                        }
                        break;

                    case 'gender':
                        // Validate gender (1 or 2)
                        if (in_array($value, [1, 2], true)) {
                            $sanitized[$key] = $value;
                        } else {
                            Log::warning('Invalid gender value', ['value' => $value]);
                        }
                        break;

                    default:
                        // Default sanitization for other fields
                        $sanitized[$key] = $this->sanitizeString($value);
                        break;
                }
            }

            return $sanitized;

        } catch (\Exception $e) {
            Log::error('Error validating data', ['error' => $e->getMessage()]);
            return $data; // Return original data if validation fails
        }
    }

    /**
     * Sanitize string input to prevent XSS and injection
     *
     * @param string $input
     * @return string
     */
    protected function sanitizeString($input)
    {
        if (!is_string($input)) {
            return '';
        }

        // Remove any HTML tags
        $clean = strip_tags($input);

        // Remove any null bytes
        $clean = str_replace("\0", '', $clean);

        // Trim whitespace
        $clean = trim($clean);

        // Remove excessive whitespace
        $clean = preg_replace('/\s+/', ' ', $clean);

        return $clean;
    }

    /**
     * Validate date string
     *
     * @param string $date
     * @return bool
     */
    protected function isValidDate($date)
    {
        if (!is_string($date)) {
            return false;
        }

        try {
            $parsed = \Carbon\Carbon::parse($date);
            // Check if date is reasonable (not in future, not too old)
            $now = \Carbon\Carbon::now();
            $oldestReasonable = \Carbon\Carbon::now()->subYears(50);

            return $parsed->lessThanOrEqualTo($now) && $parsed->greaterThanOrEqualTo($oldestReasonable);

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Extract clean cat name from text
     *
     * @param string $text
     * @return string
     */
    protected function extractCatName(string $text)
    {
        try {
            // Remove common prefixes/suffixes
            $text = preg_replace('/\s*\[.*?\]\s*/', '', $text);
            $text = preg_replace('/\s*\(.*?\)\s*/', '', $text);
            return $this->sanitizeString($text);
        } catch (\Exception $e) {
            Log::warning('Error extracting cat name', ['error' => $e->getMessage()]);
            return $this->sanitizeString($text);
        }
    }

    /**
     * Normalize date to Y-m-d format
     *
     * @param string $date
     * @return string|null
     */
    protected function normalizeDate(string $date)
    {
        try {
            if (empty($date)) {
                return null;
            }

            $parsed = \Carbon\Carbon::parse($date);

            // Validate date is reasonable
            if (!$this->isValidDate($date)) {
                Log::warning('Date out of reasonable range', ['date' => $date]);
                return null;
            }

            return $parsed->format('Y-m-d');

        } catch (\Exception $e) {
            Log::warning('Date normalization failed', [
                'date' => $date,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Search for cats by name with enhanced error handling
     *
     * @param string $name
     * @param string $breed
     * @return array
     * @throws \InvalidArgumentException
     */
    public function searchByName(string $name, string $breed = 'nfo')
    {
        try {
            // Validate inputs
            if (empty($name)) {
                throw new \InvalidArgumentException('Search name cannot be empty');
            }

            if (strlen($name) > $this->maxNameLength) {
                throw new \InvalidArgumentException("Search name too long (max {$this->maxNameLength} chars)");
            }

            $this->validateBreedCode($breed);

            $name = $this->sanitizeString($name);

            Log::info('PawPeds search started', [
                'name' => $name,
                'breed' => $breed
            ]);

            $url = "{$this->baseUrl}/db/";

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'User-Agent' => 'FindACat/1.0',
                    'Accept' => 'text/html'
                ])
                ->get($url, [
                    'a' => 's',
                    'f' => $name,
                    'p' => $breed,
                ]);

            if ($response->successful()) {
                $results = $this->parseSearchResults($response->body());
                Log::info('PawPeds search completed', [
                    'name' => $name,
                    'resultsCount' => count($results)
                ]);
                return $results;
            }

            Log::warning('PawPeds search failed', [
                'status' => $response->status(),
                'name' => $name
            ]);

            return [];

        } catch (\InvalidArgumentException $e) {
            Log::error('PawPeds search invalid input', [
                'error' => $e->getMessage(),
                'name' => $name ?? 'unknown'
            ]);
            throw $e;

        } catch (\Exception $e) {
            Log::error('PawPeds search error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'name' => $name ?? 'unknown'
            ]);
            return [];
        }
    }

    /**
     * Parse search results with error handling
     *
     * @param string $html
     * @return array
     */
    protected function parseSearchResults(string $html)
    {
        try {
            if (empty($html)) {
                return [];
            }

            $results = [];
            $dom = new \DOMDocument();

            // Suppress warnings during HTML parsing
            $oldErrorLevel = error_reporting(0);

            try {
                $loadSuccess = $dom->loadHTML(
                    mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'),
                    LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING
                );

                if (!$loadSuccess) {
                    Log::warning('Failed to parse search results HTML');
                    return [];
                }

                $xpath = new \DOMXPath($dom);

                // Extract search result links
                $links = $xpath->query("//a[contains(@href, '?a=p&id=')]");

                if (!$links || $links->length === 0) {
                    Log::info('No search results found in HTML');
                    return [];
                }

                foreach ($links as $link) {
                    try {
                        $href = $link->getAttribute('href');
                        if (preg_match('/id=([^&]+)/', $href, $matches)) {
                            $regNumber = $this->sanitizeString($matches[1]);
                            $name = $this->sanitizeString(trim($link->textContent));

                            if (!empty($regNumber) && !empty($name)) {
                                $results[] = [
                                    'reg_number' => $regNumber,
                                    'name' => $name
                                ];
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning('Error parsing search result link', [
                            'error' => $e->getMessage()
                        ]);
                        continue;
                    }
                }

            } catch (\Exception $e) {
                Log::error('Error in search results DOM parsing', [
                    'error' => $e->getMessage()
                ]);
                return [];
            } finally {
                error_reporting($oldErrorLevel);
            }

            return $results;

        } catch (\Exception $e) {
            Log::error('Critical error parsing search results', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Clear cache for a specific registration number
     *
     * @param string $regNumber
     * @param string $breed
     */
    public function clearCache(string $regNumber, string $breed = 'nfo')
    {
        try {
            $cacheKey = "pawpeds_{$breed}_{$regNumber}";
            Cache::forget($cacheKey);
            Log::info('PawPeds cache cleared', ['regNumber' => $regNumber]);
        } catch (\Exception $e) {
            Log::warning('Failed to clear cache', [
                'error' => $e->getMessage(),
                'regNumber' => $regNumber
            ]);
        }
    }
}
