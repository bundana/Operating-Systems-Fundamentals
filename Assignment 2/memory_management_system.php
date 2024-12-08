<?php

/* Represents the Memory Management System
 * What It Does:

 * Simulates a memory system where pages are swapped into memory (frames) using LRU and MFU algorithms when frames are full.
 * Key Metrics:

 * Page Faults: Number of times a required page was not in memory.
 * Hit Ratio: Proportion of requests successfully found in memory.
 * Output:

 * Timeline: Shows the state of memory after each step.
 * Page Faults: Total number of page faults.
 * Hit Ratio: Success rate of page requests in memory.*/
class MemoryManagementSystem {
    private $frames; // Stores the pages currently in memory (frames).
    private $frameCount; // Number of available memory frames.
    private $referenceSequence; // The sequence of page references.
    private $pageFrequency; // Tracks how often each page has been accessed.
    private $timeline; // Tracks the state of frames after each step.
    private $pageFaults; // Counts the number of page faults.

    // Constructor initializes the system with a frame count and reference sequence.
    public function __construct($frameCount, $referenceSequence) {
        $this->frames = []; // Start with an empty set of frames.
        $this->frameCount = $frameCount; // Set the total number of frames.
        $this->referenceSequence = $referenceSequence; // Set the sequence of page references.
        $this->pageFrequency = []; // Initialize page access frequency.
        $this->timeline = []; // Initialize an empty timeline.
        $this->pageFaults = 0; // Start with zero page faults.
    }

    // Checks if a page is already in memory (frames).
    private function isPageInFrames($page) {
        return in_array($page, $this->frames);
    }

    // Removes the least recently used (LRU) page from memory.
    private function removePageUsingLRU() {
        $leastRecentlyUsed = array_shift($this->frames); // Remove the first page in the frame.
        unset($this->pageFrequency[$leastRecentlyUsed]); // Remove its frequency tracking.
    }

    // Removes the most frequently used (MFU) page from memory.
    private function removePageUsingMFU() {
        // Find the page with the highest access frequency.
        $mostFrequentPage = array_keys($this->pageFrequency, max($this->pageFrequency))[0];
        // Locate and remove it from the frames.
        $index = array_search($mostFrequentPage, $this->frames);
        array_splice($this->frames, $index, 1); // Remove the page from memory.
        unset($this->pageFrequency[$mostFrequentPage]); // Remove its frequency tracking.
    }

    // Determines which replacement algorithm to use: LRU or MFU.
    private function decideReplacementAlgorithm() {
        // Use LRU if the frame count is even; otherwise, use MFU.
        return count($this->frames) % 2 === 0 ? "LRU" : "MFU";
    }

    // Updates the access frequency of a page.
    private function updatePageFrequency($page) {
        if (!isset($this->pageFrequency[$page])) {
            $this->pageFrequency[$page] = 0; // Initialize frequency if not already set.
        }
        $this->pageFrequency[$page]++; // Increment the frequency of the page.
    }

    // Simulates the page replacement process for the reference sequence.
    public function simulate() {
        foreach ($this->referenceSequence as $page) {
            $this->timeline[] = $this->frames; // Record the current state of memory.

            // Check if the page is already in memory.
            if ($this->isPageInFrames($page)) {
                $this->updatePageFrequency($page); // Update its frequency.
                continue; // No page fault; move to the next page.
            }

            // Page fault occurs if the page is not in memory.
            $this->pageFaults++;

            // If memory is full, use the replacement algorithm.
            if (count($this->frames) >= $this->frameCount) {
                $algorithm = $this->decideReplacementAlgorithm();
                if ($algorithm === "LRU") {
                    $this->removePageUsingLRU();
                } else {
                    $this->removePageUsingMFU();
                }
            }

            // Add the new page to memory and update its frequency.
            $this->frames[] = $page;
            $this->updatePageFrequency($page);
        }
        $this->timeline[] = $this->frames; // Record the final state of memory.
    }

    // Returns the total number of page faults.
    public function getPageFaults() {
        return $this->pageFaults;
    }

    // Calculates the hit ratio (successfully found pages vs total references).
    public function getHitRatio() {
        $totalReferences = count($this->referenceSequence);
        $hits = $totalReferences - $this->pageFaults;
        return $hits / $totalReferences;
    }

    // Returns the timeline of memory states for each step.
    public function getTimeline() {
        return $this->timeline;
    }
}

// Example setup: define a sequence of page references and the number of memory frames.
$referenceSequence = [2, 3, 1, 5, 2, 4, 1, 3, 5, 2]; // Pages requested in order.
$frameCount = 4; // Memory has 3 frames available.

$memorySystem = new MemoryManagementSystem($frameCount, $referenceSequence); // Create the system.
$memorySystem->simulate(); // Simulate the page replacement process.

// Output results
echo "Page Replacement Timeline:\n";
foreach ($memorySystem->getTimeline() as $step => $frames) {
    echo "Step $step: [" . implode(", ", $frames) . "]\n"; // Display memory state at each step.
}

echo "\nTotal Page Faults: " . $memorySystem->getPageFaults() . "\n"; // Total faults encountered.
echo "Hit Ratio: " . round($memorySystem->getHitRatio(), 2) . "\n"; // Percentage of successful page hits.

