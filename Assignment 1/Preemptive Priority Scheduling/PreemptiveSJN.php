<?php

// Class representing a process
class Process
{
    public $pid, $arrival, $burst, $priority, $remaining, $completion, $inQueue;

    // Constructor to initialize the process with its ID, arrival time, burst time, and priority
    public function __construct($pid, $arrival, $burst, $priority)
    {
        $this->pid = $pid; // Process ID (e.g., P1, P2)
        $this->arrival = $arrival; // Time when the process arrives
        $this->burst = $burst; // Time required for the process to complete execution
        $this->priority = $priority; // The priority of the process (lower number means higher priority)
        $this->remaining = $burst; // Remaining burst time, initially equal to the total burst time
        $this->completion = null; // Completion time of the process (to be filled when completed)
        $this->inQueue = false; // Whether the process is currently in the ready queue
    }
}

// Function for Preemptive Priority Scheduling (also known as Priority Scheduling with preemption)
function preemptivePriorityScheduling($processes)
{
    // Sort the processes by their arrival time
    usort($processes, function ($a, $b) {
        return $a->arrival - $b->arrival;
    });

    $time = 0; // Start the time from 0
    $completed = 0; // Number of completed processes
    $heap = new SplPriorityQueue(); // A priority queue (min-heap) for storing processes based on their priority

    // Main scheduling loop - runs until all processes are completed
    while ($completed < count($processes)) {
        // Check for any processes that have arrived by the current
        // time and add them to the priority queue
        foreach ($processes as $p) {
            if ($p->arrival <= $time && $p->remaining > 0 && !$p->inQueue) {
                $heap->insert($p,
                    -$p->priority); // Insert process with priority (min-heap, so lower priority number means higher priority)
                $p->inQueue = true; // Mark the process as in the queue
            }
        }

        // If there are processes in the queue, select the one with the highest priority (lowest number)
        if (!$heap->isEmpty()) {
            $current = $heap->extract(); // Extract the process with the highest priority
            $current->remaining--; // Reduce the remaining burst time by 1 (execute the process for 1 time unit)
            $time++; // Increment the current time

            // If the process is finished (remaining burst time is 0), set its completion time
            if ($current->remaining == 0) {
                $current->completion = $time; // Process completed
                $completed++; // Increment the completed processes count
            } else {
                $heap->insert($current, -$current->priority); // If not finished, reinsert it back into the queue
            }
        } else {
            $time++; // If no process is ready to execute, just move the time forward
        }
    }

    // Return the updated processes with their completion times
    return $processes;
}

// Function for Non-Preemptive Shortest Job Next (SJN) Scheduling
function nonPreemptiveSJN($processes)
{
    // Sort the processes by their arrival time
    usort($processes, function ($a, $b) {
        return $a->arrival - $b->arrival;
    });

    $time = 0; // Start the time from 0
    $completed = 0; // Number of completed processes

    // Main scheduling loop - runs until all processes are completed
    while ($completed < count($processes)) {
        // Get the list of available processes that have arrived by the current time and haven't completed yet
        $available = array_filter($processes, function ($p) use ($time) {
            return $p->arrival <= $time && $p->completion === null; // Process must have arrived and not completed
        });

        // Sort the available processes by their burst time (shortest job first)
        usort($available, function ($a, $b) {
            return $a->burst - $b->burst;
        });

        // If there are any available processes, select the one with the shortest burst time
        if (count($available) > 0) {
            $current = array_shift($available); // Select the process with the shortest burst time
            $time += $current->burst; // Add the burst time to the current time
            $current->completion = $time; // Set the completion time for the selected process
            $completed++; // Increment the number of completed processes
        } else {
            $time++; // If no process is available, move the time forward
        }
    }

    // Return the updated processes with their completion times
    return $processes;
}

// Define some example processes with id, arrival time, burst time, and priority
$processes = [
    new Process(1, 0, 5, 2), // Process 1 arrives at time 0, requires 5 units of time, priority 2
    new Process(2, 2, 3, 1), // Process 2 arrives at time 2, requires 3 units of time, priority 1
    new Process(3, 4, 1, 3)  // Process 3 arrives at time 4, requires 1 unit of time, priority 3
];

// Run and display results for Preemptive Priority Scheduling
echo "Preemptive Priority Scheduling:\n";
$result1 = preemptivePriorityScheduling($processes);
foreach ($result1 as $p) {
    echo "P{$p->pid}: Completion Time: {$p->completion}\n"; // Display completion time for each process
}

// Reset the processes to their initial state before running Non-Preemptive SJN
foreach ($processes as $p) {
    $p->remaining = $p->burst;
    $p->completion = null;
    $p->inQueue = false;
}

echo "\n";
echo "\n";

// Run and display results for Non-Preemptive SJN
echo "Non-Preemptive SJN:\n";
$result2 = nonPreemptiveSJN($processes);
foreach ($result2 as $p) {
    echo "P{$p->pid}: Completion Time: {$p->completion}\n"; // Display completion time for each process
}
