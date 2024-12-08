<?php

// Class to represent a process in the system
class Process
{
    public $id; // Process ID (e.g., P1, P2)
    public $arrival_time; // Time when the process arrives
    public $burst_time; // The total time the process needs to finish
    public $remaining_burst_time; // Time left to complete the process

    // Constructor to initialize the process with id, arrival time, and burst time
    public function __construct($id, $arrival_time, $burst_time)
    {
        $this->id = $id;
        $this->arrival_time = $arrival_time;
        $this->burst_time = $burst_time;
        $this->remaining_burst_time = $burst_time; // Initially, remaining burst time is the same as burst time
    }
}

// Class to represent a priority queue that handles processes
class PriorityQueue
{
    public $heap = []; // Array to hold processes in a heap (priority queue)

    // Push a process into the priority queue (insert a process)
    public function push(Process $process)
    {
        $this->heap[] = $process; // Add process to the heap
        $this->heapifyUp(count($this->heap) - 1); // Maintain the heap property (sorted by arrival time)
    }

    // Pop a process from the priority queue (remove and return the top process)
    public function pop()
    {
        if (empty($this->heap)) {
            return null; // If the heap is empty, return null
        }

        $root = $this->heap[0]; // Get the first process in the heap
        $last = array_pop($this->heap); // Remove the last process in the heap
        if (!empty($this->heap)) {
            $this->heap[0] = $last; // Move the last process to the root
            $this->heapifyDown(0); // Maintain the heap property from the root downwards
        }
        return $root; // Return the root process (process with the highest priority)
    }

    // Check if the queue is empty
    public function isEmpty()
    {
        return empty($this->heap);
    }

    // Reorganize the heap upwards to maintain order after a new element is added
    private function heapifyUp($index)
    {
        $parent = floor(($index - 1) / 2); // Find the parent's index
        if ($index > 0 && $this->heap[$index]->arrival_time < $this->heap[$parent]->arrival_time) {
            $this->swap($index, $parent); // Swap with parent if the current process arrives earlier
            $this->heapifyUp($parent); // Recurse upwards to maintain heap property
        }
    }

    // Reorganize the heap downwards to maintain order after a process is removed
    private function heapifyDown($index)
    {
        $left = 2 * $index + 1; // Left child index
        $right = 2 * $index + 2; // Right child index
        $smallest = $index;

        // Compare with left child, if exists and has earlier arrival time
        if ($left < count($this->heap) && $this->heap[$left]->arrival_time < $this->heap[$smallest]->arrival_time) {
            $smallest = $left;
        }

        // Compare with right child, if exists and has earlier arrival time
        if ($right < count($this->heap) && $this->heap[$right]->arrival_time < $this->heap[$smallest]->arrival_time) {
            $smallest = $right;
        }

        // If smallest is not the current index, swap and continue heapify
        if ($smallest != $index) {
            $this->swap($index, $smallest);
            $this->heapifyDown($smallest);
        }
    }

    // Swap two processes in the heap
    private function swap($i, $j)
    {
        $temp = $this->heap[$i];
        $this->heap[$i] = $this->heap[$j];
        $this->heap[$j] = $temp;
    }

    // Peek at the first process in the heap without removing it
    public function peek()
    {
        return isset($this->heap[0]) ? $this->heap[0] : null;
    }
}

// Function to simulate Round Robin scheduling
function roundRobinScheduling($processes)
{
    $total_waiting_time = 0; // Total waiting time for all processes
    $total_turnaround_time = 0; // Total turnaround time for all processes
    $cpu_usage = 0; // CPU usage percentage
    $total_processes = count($processes); // Total number of processes
    $current_time = 0; // Current time during the scheduling
    $time_quantum = 5; // Time slice (quantum) for each process
    $gantt_chart = []; // Gantt chart to track process execution order

    // Create a priority queue to manage processes based on their arrival time
    $queue = new PriorityQueue();

    // Push all processes into the queue
    foreach ($processes as $process) {
        $queue->push($process);
    }

    // Start executing processes until the queue is empty
    while (!$queue->isEmpty()) {
        $process = $queue->pop(); // Get the next process from the queue

        // Preemption: Adjust the quantum dynamically based on remaining burst times
        if ($process->remaining_burst_time > 0) {
            // Calculate how much time the process will run (based on its remaining burst time and time quantum)
            $quantum_used = min($time_quantum, $process->remaining_burst_time);
            $process->remaining_burst_time -= $quantum_used; // Decrease remaining burst time
            $current_time += $quantum_used; // Increase the current time by the quantum used
            $gantt_chart[] = "P".$process->id." ($quantum_used ms)"; // Add to the Gantt chart

            // If the process still has remaining burst time, put it back in the queue
            if ($process->remaining_burst_time > 0) {
                $queue->push($process);
            } else {
                // Calculate the turnaround and waiting times once the process is complete
                $process->turnaround_time = $current_time - $process->arrival_time;
                $process->waiting_time = $process->turnaround_time - $process->burst_time;
                $total_waiting_time += $process->waiting_time; // Add to total waiting time
                $total_turnaround_time += $process->turnaround_time; // Add to total turnaround time
            }
        }

        // Dynamically adjust the time quantum after each round
        $avg_remaining_burst_time = 0; // Calculate average remaining burst time of processes in the queue
        $process_count = 0;
        foreach ($queue->heap as $p) {
            $avg_remaining_burst_time += $p->remaining_burst_time;
            $process_count++;
        }
        if ($process_count > 0) {
            $time_quantum = max(5, round($avg_remaining_burst_time / $process_count)); // Adjust the quantum
        }
    }

    // Calculate CPU utilization
    $cpu_usage = ($current_time - $total_waiting_time) / $current_time * 100;

    // Output the results: Gantt chart, total waiting time, turnaround time, and CPU usage
    echo "Gantt Chart: ".implode(' -> ', $gantt_chart)."\n"; // Print Gantt chart
    echo "Total Waiting Time: ".$total_waiting_time." ms\n"; // Print total waiting time
    echo "Total Turnaround Time: ".$total_turnaround_time." ms\n"; // Print total turnaround time
    echo "CPU Utilization: ".round($cpu_usage, 2)."%\n"; // Print CPU utilization percentage
}

// Define some sample processes with id, arrival time, and burst time
$processes = [
    new Process(1, 0, 20), // Process 1: arrives at time 0, needs 20 ms
    new Process(2, 2, 10), // Process 2: arrives at time 2, needs 10 ms
    new Process(3, 4, 30), // Process 3: arrives at time 4, needs 30 ms
    new Process(4, 6, 40), // Process 4: arrives at time 6, needs 40 ms
];

// Run the Round Robin scheduling algorithm on the processes
roundRobinScheduling($processes);
