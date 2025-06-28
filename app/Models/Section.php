<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Section extends Model
{
    use HasFactory;

    // section status values
    const STATUS_DRAFT = 'draft';                   // Created by integrator, not submitted yet
    const STATUS_READY = 'ready';                   // Created by integrator, submitted to review
    const STATUS_UNDER_REVIEW = 'under_review';     // Under review
    const STATUS_REJECTED = 'rejected';             // Rejected by reviewer
    const STATUS_VERIFIED = 'verified';             // Verified by reviewer
    const STATUS_PENDING_PROMPT = 'pending_prompt'; // Waiting for AI prompt work
    const STATUS_PROMPTED = 'prompted';             // Prompt engineer finished AI/Liquid integration
    const STATUS_PENDING_VALIDATION = 'pending_validation'; // Waiting for admin approval
    const STATUS_PROMPT_REJECTED = 'prompt_rejected'; // Waiting for admin approval
    const STATUS_APPROVED = 'approved';             // Approved by admin, ready to publish
    const STATUS_PUBLISHED = 'published';           // Live on the platform

    const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_READY,
        self::STATUS_UNDER_REVIEW,
        self::STATUS_REJECTED,
        self::STATUS_VERIFIED,
        self::STATUS_PENDING_PROMPT,
        self::STATUS_PROMPTED,
        self::STATUS_PENDING_VALIDATION,
        self::STATUS_PROMPT_REJECTED,
        self::STATUS_APPROVED,
        self::STATUS_PUBLISHED,
    ];

    const TYPES = [
        'HEADER','NAVBAR', 'HERO', 'BENEFITS_ICONS', 'BENEFITS_BLOCKS_IMAGES', 'BENEFITS_LONG_DESCRIPTION', 'FEATURED_BRANDS', 'PRODUCT_OFFER', 'UGC_VIDEOS', 'HOW_TO_USE', 'COMPARISON_TABLE', 'BEFORE_AFTER', 'REVIEWS','FAQ', 'GUARANTEE_SECTION', 'FOOTER'
    ];


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'user_id',
        'name',
        'status',
        'screenshot_path',
        'description',
        'position',
        'type',
    ];

    /**
     * Get the user that owns the section.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the variables for the section.
     */
    public function variables(): HasMany
    {
        return $this->hasMany(SectionVariable::class);
    }

    /**
     * Get the status logs for the section.
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(SectionStatusLog::class);
    }


    /**
     * Get the URL for the screenshot.
     * Note: This assumes you have a route setup to serve storage files
     * or have linked the storage directory.
     * If files are not public, this needs adjustment.
     */
    public function getScreenshotUrlAttribute(): ?string
    {
        if ($this->screenshot_path && Storage::disk('public')->exists($this->screenshot_path)) {
            return Storage::url($this->screenshot_path); // Works if using 'public' disk & linked storage
        }
        return 'https://placehold.co/600x400/e2e8f0/475569?text=No+Screenshot';
    }

    public function getAssetPath(string $type): ?string
    {
        if (!$this->id) {
            Log::warning("Attempted to get asset path for Section without an ID.");
            return null;
        }

        // Base path within the storage disk
        $basePath = "sections_assets/{$this->id}";

        // Determine filename based on type
        $filename = match ($type) {
        'html' => 'index.html',
            'css' => 'style.css',
            'js' => 'script.js',
            default => null, // Handle unknown types
        };

        if (!$filename) {
            // Log::debug("Invalid asset type '{$type}' requested for Section {$this->id}.");
            return null;
        }

        // Return the full relative path
        return "{$basePath}/{$filename}";
    }

    /**
     * Accessor to get the HTML content from the standard file path.
     * Allows using $section->html_content transparently.
     *
     * @return string|null The file content or null on error/missing file.
     */
    public function getHtmlContentAttribute(): ?string
    {
        $path = $this->getAssetPath('html'); // Get standard path: sections_assets/{id}/index.html
        $disk = Storage::disk('public');

        if ($path && $disk->exists($path)) {
            try {
                return $disk->get($path);
            } catch (\Exception $e) {
                Log::error("Error reading HTML file for section {$this->id} at path {$path}: " . $e->getMessage());
                return null; // Return null on read error
            }
        }
        // Log::debug("HTML file not found for section {$this->id} at path {$path}");
        return null; // Return null if path is invalid or file doesn't exist
    }

    /**
     * Accessor to get the CSS content from the standard file path.
     * Allows using $section->css_content transparently.
     *
     * @return string|null The file content or null on error/missing file.
     */
    public function getCssContentAttribute(): ?string
    {
        $path = $this->getAssetPath('css'); // Get standard path: sections_assets/{id}/style.css
        $disk = Storage::disk('public');

        if ($path && $disk->exists($path)) {
            try {
                return $disk->get($path);
            } catch (\Exception $e) {
                Log::error("Error reading CSS file for section {$this->id} at path {$path}: " . $e->getMessage());
            }
        }
        return null;
    }

    /**
     * Accessor to get the JS content from the standard file path.
     * Allows using $section->js_content transparently.
     *
     * @return string|null The file content or null on error/missing file.
     */
    public function getJsContentAttribute(): ?string
    {
        $path = $this->getAssetPath('js'); // Get standard path: sections_assets/{id}/script.js
        $disk = Storage::disk('public');

        if ($path && $disk->exists($path)) {
            try {
                return $disk->get($path);
            } catch (\Exception $e) {
                Log::error("Error reading JS file for section {$this->id} at path {$path}: " . $e->getMessage());
            }
        }
        return null;
    }



    protected static function booted()
    {
        static::updating(function ($section) {
            if ($section->isDirty('status')) {
                SectionStatusLog::create([
                    'section_id' => $section->id,
                    'changed_by' => auth()->id(),
                    'from_status' => $section->getOriginal('status'),
                    'to_status' => $section->status,
                ]);
            }
        });
    }


}
