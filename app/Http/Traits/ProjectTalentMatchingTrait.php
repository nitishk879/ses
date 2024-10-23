<?php

namespace App\Http\Traits;

use App\Enums\LangEnum;
use App\Enums\WorkLocationEnum;
use App\Models\Project;
use App\Models\Talent;

trait ProjectTalentMatchingTrait
{
    /**
     * Calculate match percentage between a project and a talent.
     *
     * @param Project $project
     * @param Talent $talent
     * @return int|float
     */
    public function calculateMatch(Project $project, Talent $talent): int|float
    {
        $totalWeight = 0; // Total match score is 100%
        $matchScore = 100;

        // Define criteria weights (example: skills have the most weight)
        $weights = [
            'skills' => 0.4,
            'experience' => 0.1,
            'location' => 0.1,
            'salary' => 0.1,
            'hybrid' => 0.1,
            'language' => 0.1,
            'domain' => 0.1,
        ];

        /**
         * Skills match - sub-categories belongsToMany
         *
         */
        $requiredSkills = $project->subCategories->pluck('id')->toArray();
        $talentSkills = $talent->subCategories->pluck('id')->toArray();
        $skillNumerator = count(array_intersect($requiredSkills, $talentSkills));
        $skillDenominator = count($requiredSkills);
        $totalWeight += $this->safeDivide($skillNumerator, $skillDenominator) * $weights['skills'];

        /**
         * Experience required for project
         *
        */
        if (isset($project->experience)) {
            $expo = json_decode($project->experience, true);
            $minExperience = min($expo);
            $maxExperience = max($expo);
            $talentExperience = $talent->experience;

            // Check if talent's experience is within the project's range
            if ($talentExperience >= $minExperience && $talentExperience <= $maxExperience) {
                $experienceScore = 1; // Full match
            } elseif ($talentExperience < $minExperience) {
                $experienceScore = $this->safeDivide($talentExperience, $minExperience); // Partial match
            } else {
                $experienceScore = $this->safeDivide($maxExperience, $talentExperience); // Partial match if talent experience exceeds max range
            }

            // Add experience match score to total weight
            $totalWeight += $experienceScore * $weights['experience'];
        }

        /**
         * locations match - sub-categories belongsToMany
         */
        $requiredLocations = $project->locations->pluck('id')->toArray();
        $talentLocations = $talent->locations->pluck('id')->toArray();
        $locationNumerator = count(array_intersect($requiredLocations, $talentLocations)); // count(array_intersect($requiredLocations, $talentLocations))
        $locationDenominator = count($requiredLocations);
        $totalWeight += $this->safeDivide($locationNumerator, $locationDenominator) * $weights['location'];

        /**
         * let's calculate salary range
        */
        $salaryMatch = $this->calculateCostMatch($project->minimum_price, $project->maximum_price, $talent->min_monthly_price, $talent->max_monthly_price);
        $totalWeight += $salaryMatch * $weights['salary'];

        /**
         * Language Matching (Weight: 10%)
        */
//        $projectLanguages = json_decode($project->languages, true);
        if (is_array($project->languages) && count($project->languages) > 0) {
            // Get the talent's language abilities as an array using the enum
            $talentLanguages = LangEnum::toArray($talent->user->language);
            // Find how many required languages match with talent's abilities
            $matchedLanguages = array_intersect($project->languages, $talentLanguages);

            $totalWeight += $this->safeDivide(count($matchedLanguages), count($project->languages))* $weights['language']; // Return the match percentage
        }

        /**
         * Remote/hybrid
        */
        if (isset($project->features)) {
            $requiredWorking = $project->features->pluck('id')->toArray();
            $talentWorking = WorkLocationEnum::toArray($talent->work_location_prefer);
            $matchedWorking = array_intersect($requiredWorking, $talentWorking);
            $totalWeight += $this->safeDivide(count($matchedWorking), count($requiredWorking)) * $weights['hybrid'];
        }
        /**
         * Domain (Dev, QA, etc.) (Weight: 5%)
        */

        $projectDomain = $project->industries()->pluck('industriables.id')->toArray();
        $talentDomain = $talent->industries()->pluck('industriables.id')->toArray();
        $projectDomainNumerator = count(array_intersect($projectDomain, $talentDomain));
        $talentDomainDenominator = count($projectDomain);
        $totalWeight += $this->safeDivide($projectDomainNumerator, $talentDomainDenominator) * $weights['domain'];


        // Return as an integer percentage
        $score = intval($totalWeight * $matchScore);
        return max($score, 0);
    }

    /**
     * Let's make sure if denominator isn't 0
     *
     * @param $numerator
     * @param $denominator
     * @return float|int
     */
    private function safeDivide($numerator, $denominator): float|int
    {
        return $denominator > 0 ? $numerator / $denominator : 0;
    }

    /**
     * Get all talents with their match percentage for a specific project.
     *
     * @param Project $project
     * @return array
     */
    public function getTalentMatchesForProject(Project $project): array
    {
        $talents = Talent::all();
        $matches = [];

        foreach ($talents as $talent) {
            $matchPercentage = $this->calculateMatch($project, $talent);
            $matches[] = [
                'talent' => $talent,
                'match_percentage' => $matchPercentage,
            ];
        }

        // Sort talents by match percentage descending
        usort($matches, fn($a, $b) => $b['match_percentage'] <=> $a['match_percentage']);

        return $matches;
    }

    /**
     * Let's get cost calculated for the talent
     *
     * @param $projectMinBudget
     * @param $projectMaxBudget
     * @param $talentMinCost
     * @param $talentMaxCost
     * @return float|int
     */
    public function calculateCostMatch($projectMinBudget, $projectMaxBudget, $talentMinCost, $talentMaxCost): float|int
    {
        // Perfect overlap, 100% match
        if ($talentMinCost <= $projectMaxBudget && $talentMaxCost >= $projectMinBudget) {
            $overlapMin = max($projectMinBudget, $talentMinCost);
            $overlapMax = min($projectMaxBudget, $talentMaxCost);
            $totalRange = $projectMaxBudget - $projectMinBudget;
            $overlapRange = $overlapMax - $overlapMin;

            return ($overlapRange / $totalRange) * 100;
        }
        // Partial overlap: Score reduction for partial match based on proximity
        if ($talentMinCost > $projectMaxBudget) {
            // Talent's cost is higher than the budget, reduce score based on how far out of range
            $excess = $talentMinCost - $projectMaxBudget;
            return max(0, 100 - ($excess / $projectMaxBudget) * 100);
        }

        if ($talentMaxCost < $projectMinBudget) {
            // Talent's cost is lower than the budget, reduce score based on how far out of range
            $shortfall = $projectMinBudget - $talentMaxCost;
            return max(0, 100 - ($shortfall / $projectMinBudget) * 100);
        }

        return 0;
    }

    /**
     * Location match
     *
     * @param $projectLocation
     * @param $talentPreferredLocations
     * @return int
     */
    protected function getLocationScore($projectLocation, $talentPreferredLocations): int
    {
        if (in_array($projectLocation, $talentPreferredLocations)) {
            $index = array_search($projectLocation, $talentPreferredLocations);
            // Higher score if 1st preference matches, lower score for other preferences
            return match ($index) {
                0 => 100,
                1 => 75,
                2 => 50,
                default => 25,
            };
        }
        return 0;  // No matching location
    }
}
