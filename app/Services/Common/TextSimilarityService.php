<?php

namespace App\Services\Common;

class TextSimilarityService
{
    /**
     * Check if the given text is similar to any of the reference texts.
     *
     * @param string $inputText
     * @param array $referenceTexts
     * @param int $maxDistance (for Levenshtein, used in FuzzyMatch)
     * @param int $threshold (for similar_text)
     * @return bool
     */
    public function isSimilarToAny($inputText, array $referenceTexts, $maxDistance = 10, $threshold = 75)
    {
        foreach ($referenceTexts as $reference) {
            if ($this->isFuzzyMatch($inputText, [$reference], $maxDistance) ||
                $this->isSimilarText($inputText, $reference, $threshold)) {
                return true; // Match found
            }
        }

        return false; // No match found
    }

    /**
     * Check if the given text has a fuzzy match using Levenshtein distance.
     *
     * @param string $textToCheck
     * @param array $expectedTexts
     * @param int $maxDistance
     * @return bool
     */
    public function isFuzzyMatch($textToCheck, $expectedTexts, $maxDistance = 10)
    {
        foreach ($expectedTexts as $expected) {
            if (levenshtein(strtolower($textToCheck), strtolower($expected)) <= $maxDistance) {
                return true; // Match found
            }
        }
        return false; // No match found
    }

    /**
     * Check if two texts are similar using similar_text().
     *
     * @param string $textToCheck
     * @param string $expectedText
     * @param int $threshold
     * @return bool
     */
    public function isSimilarText($textToCheck, $expectedText, $threshold = 80)
    {
        similar_text(strtolower($textToCheck), strtolower($expectedText), $percent);
        return $percent >= $threshold; // Match found if above threshold
    }
}
