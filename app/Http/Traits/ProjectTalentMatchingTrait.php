<?php

namespace App\Http\Traits;

use App\Enums\LangEnum;
use App\Enums\WorkLocationEnum;
use App\Models\Project;
use App\Models\Talent;

trait ProjectTalentMatchingTrait
{
    /**
     * Calculate the match percentage between a project and a talent.
     *
     * @param Project $project
     * @param Talent $talent
     * @return array
     */
    public function calculateMatch(Project $project, Talent $talent): array
    {
        $totalWeight = 0;
        $matchScore = [];
        $totalScore = 100;

        // Criteria weights
        $weights = $this->getCriteriaWeights();

        // Calculate skill match
        $requiredSkills = $project->subCategories->pluck('id')->toArray();
        $talentSkills = $talent->subCategories->pluck('id')->toArray();
        $matchScore['skills'] = $this->calculateSkillMatch($requiredSkills, $talentSkills, $weights['skills']);

        // Calculate experience match
//        if (isset($project->experience)) {
//            $matchScore['experience'] = $this->calculateExperienceMatch($project->experience, $talent->experience, $weights['experience']);
//        }

        // Calculate location match
        $requiredLocations = $project->locations->pluck('id')->toArray();
        $talentLocations = $talent->locations->pluck('id')->toArray();
        $matchScore['location'] = $this->calculateLocationMatch($requiredLocations, $talentLocations, $weights['location']);

        // Calculate salary match
        $matchScore['salary'] = ($this->calculateCostMatch($project->minimum_price, $project->maximum_price, $talent->min_monthly_price, $talent->max_monthly_price) * $weights['salary'])/100;

        // Calculate language match
        if (is_array($project->languages) && count($project->languages) > 0) {
            $matchedLanguages = array_intersect($project->languages, $talent->user->languages);
            $matchScore['languages'] = $this->safeDivide(count($matchedLanguages), count($project->languages)) * $weights['language'];
        }

        // Calculate hybrid work preference match
        if (isset($project->work_location_prefer)) {
            $matchedWorking = array_intersect(
                $project->work_location_prefer,
                $talent->work_location_prefer
            );
            $matchScore['work_location'] = $this->safeDivide(count($matchedWorking), count($project->work_location_prefer)) * $weights['hybrid'];
        }

        // Calculate domain match
        $projectDomain = $project->industries()->pluck('industry_id')->toArray();
        $talentDomain = $talent->industries()->pluck('industry_id')->toArray();
        $matchScore['domain'] = $this->safeDivide(count(array_intersect($projectDomain, $talentDomain)), count($projectDomain)) * $weights['domain'];

        // Calculate total match score
        foreach ($matchScore as $score) {
            $totalWeight += $score;
        }

        // Return percentage
        return [
            'score' => max(round($totalWeight * $totalScore, 2), 0),
            'details' => $matchScore
        ];
    }

    /**
     * Get predefined weights for each matching criteria.
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
     * Calculate the experience match score.
     *
     * @param $projectExperience
     * @param int $talentExperience
     * @param float $weight
     * @return float
     */
    private function calculateExperienceMatch($projectExperience, int $talentExperience, float $weight): float
    {
        $minExperience = min(json_decode($projectExperience, true));
        $maxExperience = max(json_decode($projectExperience, true));

        if ($talentExperience >= $minExperience && $talentExperience <= $maxExperience) {
            return $weight; // Full match
        } elseif ($talentExperience < $minExperience) {
            return $this->safeDivide($talentExperience, $minExperience) * $weight; // Partial match
        } else {
            return $this->safeDivide($maxExperience, $talentExperience) * $weight; // Partial match
        }
    }

    /**
     * Calculate the skill match score.
     *
     * @param array $requiredSkills
     * @param array $talentSkills
     * @param float $weight
     * @return float
     */
    private function calculateSkillMatch(array $requiredSkills, array $talentSkills, float $weight): float
    {
        $matchedSkills = array_intersect($requiredSkills, $talentSkills);
        $matchPercentage = $this->safeDivide(count($matchedSkills), count($requiredSkills));
        return $matchPercentage * $weight;
    }

    /**
     * Calculate the cost match score.
     *
     * @param float $projectMinBudget
     * @param float $projectMaxBudget
     * @param float $talentMinCost
     * @param float $talentMaxCost
     * @return float|int
     */
    private function calculateCostMatch(float $projectMinBudget, float $projectMaxBudget, float $talentMinCost, float $talentMaxCost): float|int
    {
        if ($talentMinCost <= $projectMaxBudget && $talentMaxCost >= $projectMinBudget) {
            $overlapMin = max($projectMinBudget, $talentMinCost);
            $overlapMax = min($projectMaxBudget, $talentMaxCost);
            return $this->safeDivide($overlapMax - $overlapMin, $projectMaxBudget - $projectMinBudget) * 100;
        } elseif ($talentMinCost > $projectMaxBudget) {
            return max(0, 100 - (($talentMinCost - $projectMaxBudget) / $projectMaxBudget) * 100);
        } elseif ($talentMaxCost < $projectMinBudget) {
            return max(0, 100 - (($projectMinBudget - $talentMaxCost) / $projectMinBudget) * 100);
        }
        return 0;
    }

    /**
     * Calculate the location match score.
     *
     * @param array $requiredLocations
     * @param array $talentLocations
     * @param float $weight
     * @return float
     */
    private function calculateLocationMatch(array $requiredLocations, array $talentLocations, float $weight): float
    {
        if (empty($requiredLocations) || empty($talentLocations)) {
            return 0;
        }

        $totalMatchScore = 0;
        $maxScore = 0;

        foreach ($requiredLocations as $projIndex => $projectLocation) {
            foreach ($talentLocations as $talentIndex => $talentLocation) {
                if ($projectLocation == $talentLocation) {
                    $totalMatchScore += $this->getLocationWeight($projIndex, $talentIndex);
                    break;
                }
            }
            $maxScore += 100;
        }

        return $this->safeDivide($totalMatchScore, $maxScore) * $weight;
    }

    /**
     * Get the location match weight based on the index.
     *
     * @param int $projIndex
     * @param int $talentIndex
     * @return float
     */
    private function getLocationWeight(int $projIndex, int $talentIndex): float
    {
        return match ([$projIndex, $talentIndex]) {
            [0, 0] => 100,
            [0, 1], [1, 0] => 66,
            [0, 2], [2, 0] => 33,
            default => 0,
        };
    }

    /**
     * Safe division to avoid division by zero.
     *
     * @param mixed $numerator
     * @param mixed $denominator
     * @return float|int
     */
    private function safeDivide(mixed $numerator, mixed $denominator): float|int
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
            $matches[] = [
                'talent' => $talent,
                'match_percentage' => $this->calculateMatch($project, $talent),
            ];
        }

        usort($matches, fn($a, $b) => $b['match_percentage'] <=> $a['match_percentage']);

        return $matches;
    }
}
