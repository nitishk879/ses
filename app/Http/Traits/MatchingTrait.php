<?php

namespace App\Http\Traits;

use App\Enums\LangEnum;
use App\Enums\WorkLocationEnum;
use App\Models\Project;
use App\Models\Talent;

trait MatchingTrait
{
    /**
     * Main method to calculate match percentage between a project and a talent.
     *
     * @param Project $project
     * @param Talent $talent
     * @return int|float
     */
    public function calculateMatch(Project $project, Talent $talent): int|float
    {
        $totalWeight = 0;

        // Define criteria weights
        $weights = $this->getCriteriaWeights();

        // Calculate match score for each criterion
        $totalWeight += $this->calculateSkillMatch($project, $talent) * $weights['skills'];
        $totalWeight += $this->calculateExperienceMatch($project, $talent) * $weights['experience'];
        $totalWeight += $this->calculateLocationMatch($project, $talent) * $weights['location'];
        $totalWeight += $this->calculateSalaryMatch($project, $talent) * $weights['salary'];
        $totalWeight += $this->calculateLanguageMatch($project, $talent) * $weights['language'];
        $totalWeight += $this->calculateHybridMatch($project, $talent) * $weights['hybrid'];
        $totalWeight += $this->calculateDomainMatch($project, $talent) * $weights['domain'];

        // Return as a percentage, ensuring score is capped at 100
        return max(intval($totalWeight), 0);
    }

    /**
     * Get weights for different criteria.
     *
     * @return array
     */
    private function getCriteriaWeights(): array
    {
        return [
            'skills' => 0.4,
            'experience' => 0.1,
            'location' => 0.1,
            'salary' => 0.1,
            'hybrid' => 0.1,
            'language' => 0.1,
            'domain' => 0.1,
        ];
    }

    /**
     * Skills match - sub-categories belongsToMany
     */
    private function calculateSkillMatch(Project $project, Talent $talent): float
    {
        $requiredSkills = $project->subCategories->pluck('id')->toArray();
        $talentSkills = $talent->subCategories->pluck('id')->toArray();

        return $this->safeDivide(count(array_intersect($requiredSkills, $talentSkills)), count($requiredSkills));
    }

    /**
     * Experience match
     */
    private function calculateExperienceMatch(Project $project, Talent $talent): float
    {
        if (!isset($project->experience)) {
            return 0;
        }

        $experienceRange = json_decode($project->experience, true);
        $minExperience = min($experienceRange);
        $maxExperience = max($experienceRange);
        $talentExperience = $talent->experience;

        if ($talentExperience >= $minExperience && $talentExperience <= $maxExperience) {
            return 1; // Full match
        } elseif ($talentExperience < $minExperience) {
            return $this->safeDivide($talentExperience, $minExperience); // Partial match
        } else {
            return $this->safeDivide($maxExperience, $talentExperience); // Talent exceeds max experience
        }
    }

    /**
     * Location match
     */
    private function calculateLocationMatch(Project $project, Talent $talent): float
    {
        $requiredLocations = $project->locations->pluck('id')->toArray();
        $talentLocations = $talent->locations->pluck('id')->toArray();

        return $this->safeDivide(count(array_intersect($requiredLocations, $talentLocations)), count($requiredLocations));
    }

    /**
     * Salary match
     */
    private function calculateSalaryMatch(Project $project, Talent $talent): float
    {
        return $this->calculateCostMatch(
            $project->minimum_price,
            $project->maximum_price,
            $talent->min_monthly_price,
            $talent->max_monthly_price
        );
    }

    /**
     * Language match
     */
    private function calculateLanguageMatch(Project $project, Talent $talent): float
    {
        if (is_array($project->languages)) {
            $talentLanguages = LangEnum::toArray($talent->user->language);
            return $this->safeDivide(
                count(array_intersect($project->languages, $talentLanguages)),
                count($project->languages)
            );
        }
        return 0;
    }

    /**
     * Hybrid work environment match
     */
    private function calculateHybridMatch(Project $project, Talent $talent): float
    {
        if (isset($project->features)) {
            $requiredWorking = $project->features->pluck('id')->toArray();
            $talentWorking = WorkLocationEnum::toArray($talent->work_location_prefer);

            return $this->safeDivide(count(array_intersect($requiredWorking, $talentWorking)), count($requiredWorking));
        }
        return 0;
    }

    /**
     * Domain match
     */
    private function calculateDomainMatch(Project $project, Talent $talent): float
    {
        $projectDomain = $project->industries()->pluck('industriables.id')->toArray();
        $talentDomain = $talent->industries()->pluck('industriables.id')->toArray();

        return $this->safeDivide(count(array_intersect($projectDomain, $talentDomain)), count($projectDomain));
    }

    /**
     * Ensure division is safe and prevent division by zero.
     */
    private function safeDivide($numerator, $denominator): float|int
    {
        return $denominator > 0 ? $numerator / $denominator : 0;
    }

    /**
     * Calculate cost match (salary)
     */
    public function calculateCostMatch($projectMinBudget, $projectMaxBudget, $talentMinCost, $talentMaxCost): float|int
    {
        if ($talentMinCost <= $projectMaxBudget && $talentMaxCost >= $projectMinBudget) {
            $overlapMin = max($projectMinBudget, $talentMinCost);
            $overlapMax = min($projectMaxBudget, $talentMaxCost);
            return $this->safeDivide($overlapMax - $overlapMin, $projectMaxBudget - $projectMinBudget) * 100;
        }

        if ($talentMinCost > $projectMaxBudget) {
            $excess = $talentMinCost - $projectMaxBudget;
            return max(0, 100 - $this->safeDivide($excess, $projectMaxBudget) * 100);
        }

        if ($talentMaxCost < $projectMinBudget) {
            $shortfall = $projectMinBudget - $talentMaxCost;
            return max(0, 100 - $this->safeDivide($shortfall, $projectMinBudget) * 100);
        }

        return 0;
    }
}
