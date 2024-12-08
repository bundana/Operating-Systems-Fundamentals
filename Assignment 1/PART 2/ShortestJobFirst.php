<?php

// Class to represent a process
class Process {
    public $id;               // ID of the process (e.g., P1, P2, etc.)
    public $arrival_time;     // Time when the process arrives in the system
    public $burst_time;       // Total time the process needs to complete
    public $remaining_burst_time;  // Time remaining for the process to finish
    public $priority;         // Priority of the process (lower values mean higher priority)
    public $waiting_time = 0; // Time spent waiting for execution
    public $turnaround_time = 0; // Total time from arrival to completion

    // Constructor to initialize a process with its id, arrival time, burst time, and priority
    public function __construct($id, $arrival_time, $burst_time, $priority) {
        $this->id = $id;
        $this->arrival_time = $arrival_time;
        $this->burst_time = $burst_time;
        $this->remaining_burst_time = $burst_time;
        $this->priority = $priority;
    }
}

// Class to represent an interval tree, which is used to manage processes by burst time
class IntervalTree {
    public $processes = [];  // Array to store the processes

    // Method to insert a process into the tree (sorted by burst time)
    public function insert(Process $process) {
        $this->processes[] = $process;  // Add process to the array
        usort($this->processes, function($a, $b) {
            return $a->burst_time - $b->burst_time;  // Sort by burst time (ascending)
        });
    }

    // Method to remove a process from the tree by its ID
    public function remove($process_id) {
        foreach ($this->processes as $key => $process) {
            if ($process->id === $process_id) {
                unset($this->processes[$key]);  // Remove process from array
                break;
            }
        }
        $this->processes = array_values($this->processes);  // Re-index array
    }

    // Method to get the next process to execute (process with shortest burst time)
    public function getNextProcess() {
        return isset($this->processes[0]) ? $this->processes[0] : null;  // Return the first process in the list (shortest burst time)
    }

    // Method to update the priority of processes based on their waiting time
    public function updatePriority() {
        foreach ($this->processes as &$process) {
            // Update priority by adding the waiting time divided by 10 (rounded down)
            $process->priority = $process->priority + floor($process->waiting_time / 10);
        }
        // Sort the processes by their priority (ascending order)
        usort($this->processes, function($a, $b) {
            return $a->priority - $b->priority;
        });
    }
}

// Function to simulate Shortest Job First (SJF) Scheduling
function sjfScheduling($processes) {
    $total_waiting_time = 0;     // Total waiting time for all processes
    $total_turnaround_time = 0;  // Total turnaround time for all processes
    $max_starvation_time = 0;    // Maximum starvation time (longest time a process has to wait before being executed)
    $current_time = 0;           // The current time in the simulation
    $gantt_chart = [];           // Array to store the Gantt chart showing the order of processes
    $interval_tree = new IntervalTree();  // Create an instance of the Interval Tree to manage processes

    // Insert all processes into the interval tree
    foreach ($processes as $process) {
        $interval_tree->insert($process);
    }

    echo "Starting the SJF Scheduling...\n";  // Print to indicate the start of scheduling

    // Run the scheduling until all processes are completed
    while (count($interval_tree->processes) > 0) {
        // Get the next process with the shortest burst time
        $next_process = $interval_tree->getNextProcess();
        if ($next_process) {
            echo "Executing Process P{$next_process->id}...\n";  // Print the process being executed

            // Calculate waiting time: time elapsed since the process arrived
            $next_process->waiting_time = max(0, $current_time - $next_process->arrival_time);
            // Calculate turnaround time: time from arrival to completion (waiting time + burst time)
            $next_process->turnaround_time = $next_process->waiting_time + $next_process->burst_time;

            // Add the process to the Gantt chart with its burst time
            $gantt_chart[] = "P" . $next_process->id . " (" . $next_process->burst_time . " ms)";

            // Add the waiting and turnaround times to the totals
            $total_waiting_time += $next_process->waiting_time;
            $total_turnaround_time += $next_process->turnaround_time;
            // Track the maximum waiting time (starvation)
            $max_starvation_time = max($max_starvation_time, $next_process->waiting_time);

            // Update the current time after the process completes
            $current_time += $next_process->burst_time;

            // Remove the completed process from the interval tree
            $interval_tree->remove($next_process->id);
        }

        // Dynamically update the priorities of the remaining processes
        $interval_tree->updatePriority();
    }

    // Calculate the average waiting time and turnaround time
    $average_waiting_time = $total_waiting_time / count($processes);
    $average_turnaround_time = $total_turnaround_time / count($processes);

    // Output the results
    echo "Gantt Chart: " . implode(' -> ', $gantt_chart) . "\n";  // Show the Gantt chart with the process order
    echo "Average Waiting Time: " . round($average_waiting_time, 2) . " ms\n";  // Show average waiting time
    echo "Average Turnaround Time: " . round($average_turnaround_time, 2) . " ms\n";  // Show average turnaround time
    echo "Maximum Starvation Time: " . $max_starvation_time . " ms\n";  // Show maximum starvation time
}

// Define a list of processes with their arrival time, burst time, and initial priority
$processes = [
    new Process(1, 0, 10, 1),   // Process 1 arrives at 0ms, burst time 10ms, priority 1
    new Process(2, 1, 5, 2),    // Process 2 arrives at 1ms, burst time 5ms, priority 2
    new Process(3, 2, 8, 1),    // Process 3 arrives at 2ms, burst time 8ms, priority 1
    new Process(4, 3, 6, 3),    // Process 4 arrives at 3ms, burst time 6ms, priority 3
];

// Run the Shortest Job First (SJF) scheduling
sjfScheduling($processes);
