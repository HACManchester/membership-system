<?php

namespace BB\Http\Controllers;

use BB\Entities\Course;
use BB\Entities\Equipment;
use BB\Http\Requests\StoreCourseRequest;
use BB\Http\Requests\UpdateCourseRequest;
use BB\Http\Resources\CourseResource;
use BB\Http\Resources\EquipmentResource;
use BB\Http\Resources\InductionResource;
use BB\Repo\InductionRepository;
use FlashNotification;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CourseController extends Controller
{
    protected $inductionRepository;

    public function __construct(InductionRepository $inductionRepository)
    {
        $this->authorizeResource(Course::class, 'course');
        $this->inductionRepository = $inductionRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $courses = Course::with('equipment')->orderBy('name', 'ASC')->get();

        return Inertia::render('Courses/Index', [
            'courses' => CourseResource::collection($courses),
            'can' => [
                'create' => auth()->user()->can('create', Course::class),
            ],
            'urls' => [
                'create' => route('courses.create', [], false),
            ],
            'isPreview' => Course::isPreview(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $equipment = Equipment::whereDoesntHave('courses')
            ->where(function ($query) {
                $query->whereNull('induction_category')
                    ->orWhere('induction_category', '');
            })
            ->orderBy('name')
            ->get();

        return Inertia::render('Courses/Create', [
            'formatOptions' => Course::formatOptions(),
            'frequencyOptions' => Course::frequencyOptions(),
            'equipment' => EquipmentResource::collection($equipment),
            'urls' => [
                'index' => route('courses.index', [], false),
                'store' => route('courses.store', [], false),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreCourseRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreCourseRequest $request)
    {
        $validated = $request->validated();
        $equipmentIds = null;

        if (isset($validated['equipment'])) {
            $equipmentIds = $validated['equipment'];
            unset($validated['equipment']);
        }

        if (isset($validated['paused'])) {
            $validated['paused_at'] = $validated['paused'] ? now() : null;
            unset($validated['paused']);
        }

        return DB::transaction(function () use ($validated, $equipmentIds) {
            $course = Course::create($validated);

            if (!empty($equipmentIds)) {
                $course->equipment()->attach($equipmentIds);
            }

            return redirect()->route('courses.show', $course);
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  \BB\Entities\Course  $course
     * @return \Inertia\Response
     */
    public function show(Course $course)
    {
        $course->load('equipment');

        $userCourseInduction = $this->inductionRepository->getUserForCourse(
            auth()->user()->id,
            $course->id
        );

        return Inertia::render('Courses/Show', [
            'course' => (new CourseResource($course)),
            'userCourseInduction' => $userCourseInduction ? new InductionResource($userCourseInduction) : null,
            'can' => [
                'update' => auth()->user()->can('update', $course),
                'delete' => auth()->user()->can('delete', $course),
                'viewTraining' => auth()->user()->can('viewTraining', $course),
            ],
            'urls' => [
                'index' => route('courses.index', [], false),
                'edit' => route('courses.edit', $course, false),
                'destroy' => route('courses.destroy', $course, false),
                'training' => route('courses.training.index', $course, false),
                'requestSignOff' => route('courses.request-sign-off', $course, false),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \BB\Entities\Course  $course
     * @return \Inertia\Response
     */
    public function edit(Course $course)
    {
        $course->load('equipment');
        $equipment = Equipment::orderBy('name')->get();

        return Inertia::render('Courses/Edit', [
            'course' => (new CourseResource($course))->additional([
                'equipment' => $course->equipment()->pluck('equipment.id'),
            ]),
            'formatOptions' => Course::formatOptions(),
            'frequencyOptions' => Course::frequencyOptions(),
            'equipment' => EquipmentResource::collection($equipment),
            'urls' => [
                'update' => route('courses.update', $course, false),
                'show' => route('courses.show', $course, false),
                'index' => route('courses.index', [], false),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateCourseRequest  $request
     * @param  \BB\Entities\Course  $course
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateCourseRequest $request, Course $course)
    {
        $validated = $request->validated();
        $equipmentIds = null;

        if (isset($validated['equipment'])) {
            $equipmentIds = $validated['equipment'];
            unset($validated['equipment']);
        }

        if (isset($validated['paused'])) {
            $validated['paused_at'] = $validated['paused'] ? now() : null;
            unset($validated['paused']);
        }

        return DB::transaction(function () use ($validated, $equipmentIds, $course) {
            $course->update($validated);

            if ($equipmentIds !== null) {
                $course->equipment()->sync($equipmentIds ?: []);
            }

            return redirect()->route('courses.show', $course);
        });
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \BB\Entities\Course  $course
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Course $course)
    {
        return DB::transaction(function () use ($course) {
            $course->equipment()->detach();

            $course->delete();
            FlashNotification::success("Induction, {$course->name}, deleted successfully.");

            return redirect()->route('courses.index');
        });
    }
}
