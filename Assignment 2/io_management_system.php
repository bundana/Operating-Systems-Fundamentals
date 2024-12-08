<?php

/*
 *Represents an input/output (I/O) request to be processed by a device.
 * Purpose: The script simulates how multiple devices
 * (e.g., Disk, Printer) handle I/O requests based on their arrival
 * time and processing duration.
 * How it Works:
 * Requests are added to device-specific queues and sorted by arrival time and duration.
 * The simulation processes requests sequentially, keeping track of when
 *  each device becomes available.
 * The execution log for each device records when requests are
 * processed, including their start and end times.
 * */

class IORequest
{
    public $requestID;     // Unique ID for the request.
    public $deviceType;    // Type of device (e.g., Disk, Printer).
    public $arrivalTime;   // Time at which the request arrives in the system.
    public $duration;      // How long it takes to process the request.

    // Constructor to initialize a request with its details.
    public function __construct($requestID, $deviceType, $arrivalTime, $duration)
    {
        $this->requestID = $requestID;
        $this->deviceType = $deviceType;
        $this->arrivalTime = $arrivalTime;
        $this->duration = $duration;
    }
}

// Represents a processing queue for a specific device type (e.g., Disk, Printer).
class DeviceQueue
{
    public $deviceType;      // The type of device this queue is for.
    public $queue = [];      // Array to hold pending requests for this device.
    public $busyUntil = 0;   // Keeps track of when the device will be free.
    public $executionLog = [];// Log to store the start and end times of processed requests.

    // Constructor to initialize the queue for a specific device type.
    public function __construct($deviceType)
    {
        $this->deviceType = $deviceType;
    }

    // Adds a request to the queue and sorts it based on arrival time,
    // and if two requests have the same arrival time, by duration.
    public function addRequest($request)
    {
        array_push($this->queue, $request); // Add the request to the queue.
        usort($this->queue, function ($a, $b) {
            // Sort by arrival time first, then by duration for ties.
            if ($a->arrivalTime === $b->arrivalTime) {
                return $a->duration - $b->duration;
            }
            return $a->arrivalTime - $b->arrivalTime;
        });
    }

    // Processes requests in the queue based on the current time.
    public function processRequests($currentTime)
    {
        // Check if the device is free and there are requests in the queue.
        if (!empty($this->queue) && $this->busyUntil <= $currentTime) {
            $request = array_shift($this->queue); // Get the first request from the queue.

            // Determine the start time of the request.
            $startTime = max($request->arrivalTime, $currentTime);
            $this->busyUntil = $startTime + $request->duration; // Update when the device will be free.

            // Log the execution details (start and end times) for this request.
            $this->executionLog[] = [
                "requestID" => $request->requestID,
                "startTime" => $startTime,
                "endTime" => $this->busyUntil
            ];
        }
    }
}

// Example setup of requests and devices
$requests = [
    new IORequest("R1", "Disk", 0, 20),       // Request R1 for Disk, arrives at 0, takes 20 units.
    new IORequest("R2", "Printer", 10, 25),  // Request R2 for Printer, arrives at 10, takes 25 units.
    new IORequest("R3", "Printer", 5, 15),   // Request R3 for Printer, arrives at 5, takes 15 units.
];

$devices = [
    "Disk" => new DeviceQueue("Disk"),        // Create a queue for Disk.
    "Printer" => new DeviceQueue("Printer"),  // Create a queue for Printer.
];

// Assign requests to their corresponding device queues.
foreach ($requests as $request) {
    $devices[$request->deviceType]->addRequest($request);
}

// Simulate the processing of requests over time.
$currentTime = 0;       // Start simulation at time 0.
$endTime = 100;         // Simulate up to time 100.
while ($currentTime <= $endTime) {
    foreach ($devices as $device) {
        $device->processRequests($currentTime); // Process requests for each device at the current time.
    }
    $currentTime++;     // Move to the next time unit.
}

// Output the results: display the execution log for each device.
foreach ($devices as $device) {
    echo "Execution log for {$device->deviceType}:\n";
    foreach ($device->executionLog as $log) {
        echo "Request {$log['requestID']} - Start: {$log['startTime']} End: {$log['endTime']}\n";
    }
    echo "\n"; // Add a blank line between logs for clarity.
}
