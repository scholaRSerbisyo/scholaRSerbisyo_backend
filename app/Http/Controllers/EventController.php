<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventStoreRequest;
use App\Http\Requests\EventTypeStoreRequest;
use App\Http\Requests\EventUpdateRequest;
use App\Http\Requests\TimeInSubmissionRequest;
use App\Http\Requests\TimeOutSubmissionRequest;
use App\Models\EventType;
use App\Models\Event;
use App\Models\Submission;
use App\Models\Scholar;
use App\Models\ReturnService;
use App\Services\CloudflareR2Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\SendPushNotification;

use Illuminate\Support\Str;
use Carbon\Carbon;

class EventController extends Controller
{
    protected $r2Service;
    protected $sendPushNotification;

    public function __construct(CloudflareR2Service $r2Service, SendPushNotification $sendPushNotification)
    {
        $this->r2Service = $r2Service;
        $this->sendPushNotification = $sendPushNotification;
    }

    public function createEvent(EventStoreRequest $request)
    {
        try {
            $eventData = $request->validated();
    
            $eventData['status'] = $this->determineEventStatus($eventData['date']);
            $event = Event::create($eventData);
    
            if ($event) {
                $this->r2Service->uploadFileToBucket($request->input('image'), $eventData['event_image_uuid']);
                
                // Prepare notification data
                $notificationData = [
                    'event_id' => $event->event_id,
                    'event_name' => $event->event_name,
                    'event_type_name' => $event->eventType->event_type_name, // Assuming there's a relationship to EventType
                    'description' => $event->description,
                    'date' => $event->date,
                    'time_from' => $event->time_from,
                    'time_to' => $event->time_to,
                    'event_image_uuid' => $event->event_image_uuid,
                ];
                
                // Send broadcast notification
                $notificationResult = $this->sendPushNotification->sendBroadcastNotification(new Request($notificationData));
                
                Log::info('Event created successfully', ['event_id' => $event->event_id, 'notification_result' => $notificationResult]);
                return response([
                    'message' => 'Event created successfully',
                    'event' => $event,
                    'notification_result' => $notificationResult
                ], 201);
            }
        } catch (\Throwable $th) {
            Log::error('Error creating event', ['error' => $th->getMessage()]);
            return response(['message' => $th->getMessage()], 500);
        }
    }


    public function getCompletedSubmissions($eventId)
    {
        try {
            $event = Event::findOrFail($eventId);

            $submissions = $event->submissions()
                ->whereNotNull('time_in')
                ->whereNotNull('time_out')
                ->with(['scholar.user', 'scholar.school', 'scholar.baranggay', 'scholar.scholarType'])
                ->get();

            if ($submissions->isEmpty()) {
                return response()->json([
                    'message' => 'No completed submissions found for this event.',
                    'submissions' => [],
                    'total' => 0
                ], 200);
            }

            $formattedSubmissions = $submissions->map(function ($submission) {
                $scholar = $submission->scholar;
                $currentYearStatus = $scholar->getCurrentYearReturnServiceStatus();

                return [
                    'id' => $submission->submission_id,
                    'scholar' => [
                        'id' => $scholar->scholar_id,
                        'firstname' => $scholar->firstname,
                        'lastname' => $scholar->lastname,
                        'age' => $scholar->age,
                        'yearLevel' => $scholar->yearlevel,
                        'scholarType' => $scholar->scholarType->scholar_type_name,
                        'school' => [
                            'name' => $scholar->school->school_name,
                        ],
                        'barangay' => [
                            'name' => $scholar->baranggay->baranggay_name,
                        ],
                        'returnServiceStatus' => $currentYearStatus,
                    ],
                    'time_in' => $submission->time_in->format('Y-m-d H:i:s'),
                    'time_out' => $submission->time_out->format('Y-m-d H:i:s'),
                    'time_in_location' => $submission->time_in_location,
                    'time_out_location' => $submission->time_out_location,
                    'time_in_image_uuid' => $submission->time_in_image_uuid,
                    'time_out_image_uuid' => $submission->time_out_image_uuid,
                    'created_at' => $submission->created_at->format('Y-m-d H:i:s'),
                    'is_accepted' => ReturnService::where('submission_id', $submission->submission_id)->exists(),
                ];
            });

            return response()->json([
                'submissions' => $formattedSubmissions,
                'total' => $formattedSubmissions->count(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in getCompletedSubmissions: ' . $e->getMessage(), [
                'event_id' => $eventId,
                'exception' => $e
            ]);
            return response()->json(['message' => 'An error occurred while fetching completed submissions: ' . $e->getMessage()], 500);
        }
    }

    public function acceptSubmission($submissionId)
    {
        try {
            DB::beginTransaction();

            $submission = Submission::with(['scholar', 'event'])->findOrFail($submissionId);

            $returnService = ReturnService::create([
                'scholar_id' => $submission->scholar_id,
                'submission_id' => $submission->submission_id,
                'event_id' => $submission->event_id,
                'year' => date('Y'),
                'completed_at' => now(),
            ]);

            if ($returnService) {
                // Update submission status only if ReturnService is created successfully
                Submission::where('submission_id', $submissionId)->update(['status' => 'accepted']);
                
                // Refresh the submission to get the updated status
                $submission->refresh();

                $formattedReturnService = [
                    'id' => $returnService->return_service_id,
                    'firstname' => $submission->scholar->firstname,
                    'lastname' => $submission->scholar->lastname,
                    'submission' => [
                        'id' => $submission->submission_id,
                        'time_in' => $submission->time_in,
                        'time_out' => $submission->time_out,
                        'status' => $submission->status, // Include updated status in the response
                    ],
                    'event' => [
                        'id' => $submission->event->event_id,
                        'name' => $submission->event->event_name,
                        'date' => $submission->event->date,
                    ],
                    'completed_at' => $returnService->completed_at->toDateTimeString(),
                ];

                DB::commit();

                return response()->json([
                    'message' => 'Submission accepted successfully',
                    'returnService' => $formattedReturnService
                ]);
            } else {
                throw new \Exception('Failed to create ReturnService');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in acceptSubmission: ' . $e->getMessage(), [
                'submission_id' => $submissionId,
                'exception' => $e
            ]);
            return response()->json(['message' => 'An error occurred while accepting the submission: ' . $e->getMessage()], 500);
        }
    }

    public function declineSubmission($submissionId)
    {
        try {
            DB::beginTransaction();

            $submission = Submission::with(['scholar', 'event'])->findOrFail($submissionId);

            $returnService = ReturnService::create([
                'scholar_id' => $submission->scholar_id,
                'submission_id' => $submission->submission_id,
                'event_id' => $submission->event_id,
                'year' => date('Y'),
            ]);

            if ($returnService) {
                // Update submission status to "declined"
                Submission::where('submission_id', $submissionId)->update(['status' => 'declined']);
                
                // Refresh the submission to get the updated status
                $submission->refresh();

                $formattedReturnService = [
                    'id' => $returnService->return_service_id,
                    'firstname' => $submission->scholar->firstname,
                    'lastname' => $submission->scholar->lastname,
                    'submission' => [
                        'id' => $submission->submission_id,
                        'time_in' => $submission->time_in,
                        'time_out' => $submission->time_out,
                        'status' => $submission->status, // Include updated status in the response
                    ],
                    'event' => [
                        'id' => $submission->event->event_id,
                        'name' => $submission->event->event_name,
                        'date' => $submission->event->date,
                    ],
                ];

                DB::commit();

                return response()->json([
                    'message' => 'Submission declined successfully',
                    'returnService' => $formattedReturnService
                ]);
            } else {
                throw new \Exception('Failed to create ReturnService');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in declineSubmission: ' . $e->getMessage(), [
                'submission_id' => $submissionId,
                'exception' => $e
            ]);
            return response()->json(['message' => 'An error occurred while declining the submission: ' . $e->getMessage()], 500);
        }
    }

    public function getScholarsWithReturnServiceCount()
    {
        try {
            $scholars = Scholar::with(['user', 'school', 'baranggay', 'scholarType'])
                ->select('scholars.*')
                ->selectRaw('COALESCE(COUNT(return_services.return_service_id), 0) as return_service_count')
                ->leftJoin('return_services', function ($join) {
                    $join->on('scholars.scholar_id', '=', 'return_services.scholar_id')
                         ->where('return_services.year', '=', date('Y'));
                })
                ->groupBy('scholars.scholar_id')
                ->get();

            $formattedScholars = $scholars->map(function ($scholar) {
                return [
                    'id' => $scholar->scholar_id,
                    'firstname' => $scholar->firstname,
                    'lastname' => $scholar->lastname,
                    'mobilenumber' => $scholar->mobilenumber,
                    'age' => (string) $scholar->age,
                    'yearLevel' => $scholar->yearlevel,
                    'scholarType' => $scholar->scholarType->scholar_type_name,
                    'school' => [
                        'id' => $scholar->school->school_id,
                        'name' => $scholar->school->school_name,
                    ],
                    'barangay' => [
                        'id' => $scholar->baranggay->baranggay_id,
                        'name' => $scholar->baranggay->baranggay_name,
                    ],
                    'returnServiceCount' => (int) $scholar->return_service_count
                ];
            });

            return response()->json([
                'scholars' => $formattedScholars,
                'total' => $formattedScholars->count(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in getScholarsWithReturnServiceCount: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json(['message' => 'An error occurred while fetching scholars with return service count: ' . $e->getMessage()], 500);
        }
    }

    public function getScholarWithReturnServiceCount($scholarId)
    {
        try {
            $scholar = Scholar::with(['user', 'school', 'baranggay', 'scholarType'])
                ->select('scholars.*')
                ->where('scholars.scholar_id', $scholarId)
                ->first();

            if (!$scholar) {
                return response()->json(['message' => 'Scholar not found'], 404);
            }

            $returnServices = ReturnService::where('scholar_id', $scholarId)
                ->select('year', DB::raw('COUNT(*) as count'))
                ->groupBy('year')
                ->orderBy('year', 'desc')
                ->get();

            $formattedScholar = [
                'id' => $scholar->scholar_id,
                'firstname' => $scholar->firstname,
                'lastname' => $scholar->lastname,
                'mobilenumber' => $scholar->mobilenumber,
                'age' => $scholar->age,
                'yearLevel' => $scholar->yearlevel,
                'scholarType' => $scholar->scholarType->scholar_type_name,
                'school' => [
                    'name' => $scholar->school->school_name,
                ],
                'barangay' => [
                    'name' => $scholar->baranggay->baranggay_name,
                ],
                'yearlyReturnServices' => $returnServices->map(function ($service) {
                    return [
                        'year' => $service->year,
                        'count' => $service->count,
                    ];
                }),
            ];

            return response()->json([
                'scholar' => $formattedScholar,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in getScholarWithReturnServiceCount: ' . $e->getMessage(), [
                'exception' => $e,
                'scholar_id' => $scholarId
            ]);
            return response()->json(['message' => 'An error occurred while fetching scholar with return service count: ' . $e->getMessage()], 500);
        }
    }

    public function getScholarEvents($scholarId)
    {
        $scholar = Scholar::findOrFail($scholarId);
        
        $events = ReturnService::where('scholar_id', $scholarId)
            ->with(['event' => function ($query) {
                $query->select('event_id', 'event_name', 'event_type_id', 'date', 'time_from', 'time_to', 'location', 'description', 'event_image_uuid', 'status')
                    ->with(['eventType:event_type_id,name']);
            }])
            ->get()
            ->map(function ($returnService) {
                $event = $returnService->event;
                $event->completed_at = $returnService->completed_at;
                $event->year = $returnService->year;
                $event->event_type_name = $event->eventType->name;
                unset($event->eventType); // Remove the eventType relationship from the response
                return $event;
            });

        return response()->json([
            'scholar' => [
                'id' => $scholar->scholar_id,
                'firstname' => $scholar->firstname,
                'lastname' => $scholar->lastname,
            ],
            'events' => $events
        ]);
    }

    public function storeTimeInSubmission(TimeInSubmissionRequest $request)
    {
        try {
            $submissionData = $request->validated();

            return DB::transaction(function () use ($submissionData, $request) {
                // Create the submission with Time In data
                $submission = Submission::create([
                    'event_id' => $submissionData['event_id'],
                    'scholar_id' => $submissionData['scholar_id'],
                    'time_in_location' => $submissionData['time_in_location'],
                    'time_in' => $submissionData['time_in'],
                    'time_in_image_uuid' => $submissionData['time_in_image_uuid'],
                    'description' => $submissionData['description'] ?? null,
                    'status' => 'pending'
                ]);

                // Upload time_in image
                if ($request->has('time_in_image')) {
                    $timeInImageUrl = $this->r2Service->uploadFileToBucket($request->input('time_in_image'), $submissionData['time_in_image_uuid']);
                    if (!$timeInImageUrl) {
                        throw new \Exception('Failed to upload time-in image');
                    }
                } else {
                    throw new \Exception('Time-in image is required');
                }

                // Update submission with confirmed image UUID
                $submission->update([
                    'time_in_image_uuid' => $submissionData['time_in_image_uuid'],
                ]);

                Log::info('Time In submission created successfully', ['submission_id' => $submission->submission_id]);
                return response(['message' => 'Time In submission created successfully', 'submission_id' => $submission->submission_id], 201);
            });
        } catch (\Exception $e) {
            Log::error('Error in storeTimeInSubmission: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->except('time_in_image')
            ]);
            return response(['message' => 'An error occurred while processing your request. Please try again.', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateTimeOutSubmission(TimeOutSubmissionRequest $request)
    {
        try {
            $submissionData = $request->validated();

            return DB::transaction(function () use ($submissionData, $request) {
                $submission = Submission::findOrFail($submissionData['submission_id']);

                // Update the submission with Time Out data
                $submission->update([
                    'time_out_location' => $submissionData['time_out_location'],
                    'time_out' => $submissionData['time_out'],
                    'time_out_image_uuid' => $submissionData['time_out_image_uuid'],
                ]);

                // Upload time_out image
                if ($request->has('time_out_image')) {
                    $timeOutImageUrl = $this->r2Service->uploadFileToBucket($request->input('time_out_image'), $submissionData['time_out_image_uuid']);
                    if (!$timeOutImageUrl) {
                        throw new \Exception('Failed to upload time-out image');
                    }
                } else {
                    throw new \Exception('Time-out image is required');
                }

                // Update submission with confirmed time out image UUID
                $submission->update([
                    'time_out_image_uuid' => $submissionData['time_out_image_uuid'],
                ]);

                return response(['message' => 'Time Out submission updated successfully', 'submission' => $submission], 200);
            });
        } catch (\Exception $e) {
            Log::error('Error in updateTimeOutSubmission: ' . $e->getMessage());
            return response(['message' => $e->getMessage()], 500);
        }
    }

    public function checkExistingSubmission($eventId)
{
    try {
        $user = auth()->user();
        $scholar = $user->scholar;

        $submission = Submission::where('event_id', $eventId)
            ->where('scholar_id', $scholar->scholar_id)
            ->first();

        if ($submission) {
            return response()->json([
                'hasSubmission' => true,
                'submissionId' => $submission->submission_id,
                'timeIn' => $submission->time_in,
                'timeOut' => $submission->time_out
            ]);
        } else {
            return response()->json([
                'hasSubmission' => false,
                'timeIn' => null,
                'timeOut' => null
            ]);
        }
    } catch (\Exception $e) {
        Log::error('Error in checkExistingSubmission: ' . $e->getMessage(), [
            'event_id' => $eventId,
            'exception' => $e
        ]);
        return response()->json([
            'message' => 'An error occurred while checking for existing submissions.'
        ], 500);
    }
}


    public function updateEvent(EventUpdateRequest $request, string $id)
    {
        try {
            $event = Event::findOrFail($id);
            $eventData = $request->validated();

            $eventData['status'] = $this->determineEventStatus($eventData['date']);

            $oldImageUuid = $event->event_image_uuid;

            if ($request->has('image')) {
                // Generate a new UUID for the image
                $newImageUuid = (string) Str::uuid();
                $eventData['event_image_uuid'] = $newImageUuid;

                // Upload the new image
                $newImageUrl = $this->r2Service->uploadFileToBucket($request->input('image'), $newImageUuid);
                
                if ($newImageUrl) {
                    $eventData['image'] = $newImageUrl;

                    // Delete the old image if it exists
                    if ($oldImageUuid) {
                        $this->r2Service->deleteFile($oldImageUuid);
                    }
                } else {
                    // If upload fails, don't update the image-related fields
                    unset($eventData['event_image_uuid']);
                    unset($eventData['image']);
                }
            }

            $event->update($eventData);

            return response(['message' => 'Event updated successfully!', 'event' => $event], 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function getScholarSubmissions(Request $request)
    {
        try {
            $user = auth()->user();
            $scholar = $user->scholar;

            if (!$scholar) {
                return response()->json(['message' => 'Scholar not found'], 404);
            }

            $submissions = Submission::where('scholar_id', $scholar->scholar_id)
                ->with(['event' => function ($query) {
                    $query->with(['eventType', 'school', 'barangay']);
                }])
                ->get();

            $formattedSubmissions = $submissions->map(function ($submission) {
                $event = $submission->event;
                return [
                    'submission_id' => $submission->submission_id,
                    'event' => [
                        'event_id' => $event->event_id,
                        'event_name' => $event->event_name,
                        'description' => $event->description,
                        'date' => $event->formatted_date,
                        'time_from' => $event->formatted_time_from,
                        'time_to' => $event->formatted_time_to,
                        'location' => $event->location,
                        'status' => $event->status,
                        'event_type' => $event->eventType->name,
                        'event_Type' => [
                            'name' => $event->eventType->name
                        ],
                        'school' => $event->school ? $event->school->name : null,
                        'barangay' => $event->barangay ? $event->barangay->name : null,
                    ],
                    'submission_details' => [
                        'time_in' => $submission->time_in,
                        'time_out' => $submission->time_out,
                        'time_in_location' => $submission->time_in_location,
                        'time_out_location' => $submission->time_out_location,
                        'time_in_image_uuid' => $submission->time_in_image_uuid,
                        'time_out_image_uuid' => $submission->time_out_image_uuid,
                    ],
                    'status' => $submission->status
                ];
            });

            return response()->json($formattedSubmissions, 200);
        } catch (\Exception $e) {
            Log::error('Error in getScholarSubmissions: ' . $e->getMessage(), [
                'scholar_id' => $scholar->scholar_id ?? 'N/A',
                'exception' => $e
            ]);
            return response()->json(['message' => 'An error occurred while fetching submissions'], 500);
        }
    }

    public function getScholarSubmissionImages()
    {
        try {
            $user = auth()->user();
            $scholar = $user->scholar;

            if (!$scholar) {
                return response()->json(['message' => 'Scholar not found'], 404);
            }

            $submissions = Submission::where('scholar_id', $scholar->scholar_id)->get();

            $imageUuids = $submissions->flatMap(function ($submission) {
                return [
                    [
                        'submission_id' => $submission->submission_id,
                        'event_id' => $submission->event_id,
                        'event_name' => $submission->event->event_name,
                        'time_in_image_uuid' => $submission->time_in_image_uuid,
                        'time_out_image_uuid' => $submission->time_out_image_uuid,
                    ]
                ];
            });

            return response()->json([
                'message' => 'Scholar submission images retrieved successfully',
                'data' => $imageUuids
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in getScholarSubmissionImages: ' . $e->getMessage(), [
                'scholar_id' => $scholar->scholar_id ?? 'N/A',
                'exception' => $e
            ]);
            return response()->json(['message' => 'An error occurred while fetching submission images'], 500);
        }
    }

    public function getAllEvents() {
        try {
            $events = Event::whereHas('eventType', function ($query) {
                $query->whereIn('name', ['CSO', 'School', 'Community']);
            })->with(['eventType', 'school', 'barangay'])->get();

            $formattedEvents = $events->map(function ($event) {
                return [
                    'event_id' => $event->event_id,
                    'event_image_uuid' => $event->event_image_uuid,
                    'event_name' => $event->event_name,
                    'description' => $event->description,
                    'date' => $event->formatted_date,
                    'time_from' => $event->formatted_time_from,
                    'time_to' => $event->formatted_time_to,
                    'location' => $event->location,
                    'status' => $event->status,
                    'admin_id' => $event->admin_id,
                    'event_type' => $event->eventType,
                    'school' => $event->school,
                    'barangay' => $event->barangay,
                    'created_at' => $event->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $event->updated_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json($formattedEvents, 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function getCSOEvents()
    {
        try {
            return response()->json(Event::whereHas('eventType', function ($query) {
                $query->where('name', 'CSO');
            })->get(), 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function getSchoolEvents() {
        try {
            return response()->json(Event::where('event_type_id', 2)->with('school')->get(), 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function getBarangayEvents() {
        try {
            return response()->json(Event::where('event_type_id', 3)->with('barangay')->get(), 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function getEventById($id)
    {
        try {
            $event = Event::with(['eventType', 'school', 'barangay'])->where('event_id', $id)->firstOrFail();

            $formattedEvent = [
                'event_id' => $event->event_id,
                'event_image_uuid' => $event->event_image_uuid,
                'event_name' => $event->event_name,
                'description' => $event->description,
                'date' => $event->formatted_date,
                'time_from' => $event->formatted_time_from,
                'time_to' => $event->formatted_time_to,
                'location' => $event->location,
                'status' => $event->status,
                'admin_id' => $event->admin_id,
                'event_type' => $event->eventType,
                'school' => $event->school,
                'barangay' => $event->barangay,
                'created_at' => $event->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $event->updated_at->format('Y-m-d H:i:s'),
            ];

            return response()->json($formattedEvent, 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Event not found', ['id' => $id]);
            return response()->json(['message' => 'Event not found'], 404);
        } catch (\Throwable $th) {
            Log::error('Error in getEventById: ' . $th->getMessage(), [
                'id' => $id,
                'exception' => $th
            ]);
            return response()->json(['message' => 'An error occurred while fetching the event.'], 500);
        }
    }

    public function getImage(Request $request)
    {
        $imageUuid = $request->input('image_uuid');

        if (empty($imageUuid)) {
            return response()->json(['error' => 'Image UUID is required'], 400);
        }

        try {
            $url = Cache::remember("image_url_{$imageUuid}", now()->addMinutes(30), function () use ($imageUuid) {
                return $this->r2Service->getFileUrl($imageUuid);
            });

            if ($url === null) {
                return response()->json(['error' => 'Unable to generate image URL'], 500);
            }

            return response()->json(['url' => $url], 200);
        } catch (\Throwable $th) {
            Log::error('Error generating image URL: ' . $th->getMessage());
            return response()->json(['error' => 'Unable to generate image URL'], 500);
        }
    }

    public function createEventType(EventTypeStoreRequest $request) {
        try {
            $eventType = EventType::create($request->validated());
            return response(['message' => 'Event type created successfully', 'eventType' => $eventType], 201);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function getEventTypes() {
        try {
            return response()->json(EventType::all(), 200);
        } catch (\Throwable $th) {
            return response(['message' => $th->getMessage()], 500);
        }
    }

    public function determineEventStatus(string $date): string
    {
        $eventDate = Carbon::parse($date);

        if ($eventDate->isToday()) {
            return 'ongoing';
        } elseif ($eventDate->isFuture()) {
            return 'upcoming';
        } else {
            return 'completed';
        }
    }
}