<?php

namespace App\Console\Commands;

use App\Models\SubCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Export the SES skill taxonomy for the AI parsing service.
 *
 * The parser has to return `sub_category_id` values that SES already stores,
 * otherwise its output cannot be joined back to projects and talents. Rather
 * than giving the Python service database credentials, SES publishes the
 * taxonomy as a small JSON file that the service loads at boot.
 *
 * Run this after adding or renaming sub-categories, then POST
 * /v1/admin/catalog/reload on the AI service to pick it up without a restart.
 */
class ExportSkillCatalog extends Command
{
    protected $signature = 'ses:export-skill-catalog
                            {--path= : Destination file (defaults to config value)}';

    protected $description = 'Export sub_categories as JSON for the AI parsing service';

    public function handle(): int
    {
        $path = $this->option('path') ?: config('services.ai_parser.catalog_path');

        if (! $path) {
            $this->error('No destination path. Pass --path or set AI_PARSER_CATALOG_PATH.');

            return self::FAILURE;
        }

        $subCategories = SubCategory::query()
            ->orderBy('id')
            ->get(['id', 'title'])
            ->map(fn (SubCategory $row) => [
                'id' => (int) $row->id,
                'title' => (string) $row->title,
            ])
            ->all();

        if ($subCategories === []) {
            // Writing an empty catalog would silently make every skill
            // unmapped on the parser side, so fail loudly instead.
            $this->error('sub_categories is empty — refusing to write an empty catalog.');

            return self::FAILURE;
        }

        $payload = [
            'generated_at' => now()->toIso8601String(),
            'sub_categories' => $subCategories,
            // Aliases the parser should treat as the same skill. The service
            // ships sensible defaults; anything business-specific belongs
            // here so recruiters can extend it without a code change.
            'aliases' => config('services.ai_parser.aliases', []),
        ];

        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info(sprintf('Wrote %d sub-categories to %s', count($subCategories), $path));

        return self::SUCCESS;
    }
}
