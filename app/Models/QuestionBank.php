<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionBank extends Model
{
    protected $fillable = [
        'user_id',
        'department',
        'course_code',
        'course_name',
        'title',
        'difficulty',
        'question_heading',
        'sub_questions',
        'tags',
        'year_semester',
        'file_path',
        'status',
    ];

    protected $casts = [
        'file_path' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Parse raw Cloudinary URL and generate a signed API download link to bypass delivery restrictions.
     */
    public static function getCloudinaryDownloadUrl(?string $url): string
    {
        if (empty($url)) {
            return '';
        }

        if (!str_contains($url, 'cloudinary.com/')) {
            return $url;
        }

        $path = parse_url($url, PHP_URL_PATH);
        $parts = explode('/', trim($path, '/'));
        
        if (count($parts) < 5) {
            return $url;
        }

        $cloudName = $parts[0];
        $resourceType = $parts[1]; // raw, image
        $uploadType = $parts[2];   // upload, private
        
        $versionIndex = 3;
        while ($versionIndex < count($parts) && !preg_match('/^v\d+$/', $parts[$versionIndex])) {
            $versionIndex++;
        }

        $publicIdParts = array_slice($parts, $versionIndex + 1);
        $fullPublicIdWithExt = implode('/', $publicIdParts);
        
        $ext = pathinfo($fullPublicIdWithExt, PATHINFO_EXTENSION);
        $publicId = ($resourceType === 'raw') ? $fullPublicIdWithExt : pathinfo($fullPublicIdWithExt, PATHINFO_FILENAME);
        if ($resourceType !== 'raw' && count($publicIdParts) > 1) {
            $folderPath = implode('/', array_slice($publicIdParts, 0, -1));
            $publicId = $folderPath . '/' . $publicId;
        }

        try {
            return cloudinary()->uploadApi()->privateDownloadUrl($publicId, $ext ?: 'pdf', [
                'resource_type' => $resourceType,
                'type'          => $uploadType,
                'expires_at'    => time() + 3600 // URL valid for 1 hour
            ]);
        } catch (\Exception $e) {
            \Log::warning('[CloudinaryHelper] URL signing failed: ' . $e->getMessage());
            return $url;
        }
    }

    /**
     * Get signed versions of all file paths.
     */
    public function getSignedFilePathsAttribute(): array
    {
        $files = $this->file_path;
        if (empty($files)) {
            return [];
        }
        if (is_string($files)) {
            $files = json_decode($files, true) ?? [];
        }
        return array_map(function ($url) {
            return self::getCloudinaryDownloadUrl($url);
        }, (array) $files);
    }
}
