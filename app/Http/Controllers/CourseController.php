<?php

namespace BB\Http\Controllers;

use BB\Entities\Course;
use BB\Entities\Equipment;
use BB\Http\Requests\StoreCourseRequest;
use BB\Http\Requests\UpdateCourseRequest;
use BB\Http\Resources\CourseResource;
use BB\Http\Resources\EquipmentResource;
use FlashNotification;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Course::class, 'course');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $courses = Course::with('equipment')->orderBy('name', 'ASC')->get();

        return Inertia::render('Courses/Index', [
            'courses' => CourseResource::collection($courses),
            'can' => [
                'create' => auth()->user() ? auth()->user()->can('create', Course::class) : false,
            ],
            'urls' => [
                'create' => route('courses.create', [], false),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $equipment = Equipment::where('induction_category', '')->orderBy('name')->get();

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
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCourseRequest $request)
    {
        $validated = $request->validated();
        $equipment = null;

        // Extract equipment IDs before creating the course
        if (isset($validated['equipment'])) {
            $equipment = $validated['equipment'];
            unset($validated['equipment']);
        }

        $course = Course::create($validated);

        // TODO: Probably not this way. Many to many relationships maybe?
        // Update equipment induction_category if equipment IDs were provided
        if ($equipment && !empty($equipment)) {
            Equipment::whereIn('id', $equipment)
                ->update(['induction_category' => $course->slug]);
        }

        return redirect()->route('courses.show', $course);
    }

    /**
     * Display the specified resource.
     *
     * @param  \BB\Entities\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function show(Course $course)
    {
        $course->load('equipment');

        return Inertia::render('Courses/Show', [
            'course' => (new CourseResource($course)),
            'can' => [
                'update' => auth()->user() ? auth()->user()->can('update', $course) : false,
                'delete' => auth()->user() ? auth()->user()->can('delete', $course) : false,
            ],
            'urls' => [
                'index' => route('courses.index', [], false),
                'edit' => route('courses.edit', $course, false),
                'destroy' => route('courses.destroy', $course, false),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \BB\Entities\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function edit(Course $course)
    {
        $course->load('equipment');
        $equipment = Equipment::orderBy('name')->get();

        return Inertia::render('Courses/Edit', [
            'course' => (new CourseResource($course))->additional([
                'equipment' => $course->equipment->pluck('id'),
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
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCourseRequest $request, Course $course)
    {
        $validated = $request->validated();
        $equipment = null;

        // Extract equipment IDs before updating the course
        if (isset($validated['equipment'])) {
            $equipment = $validated['equipment'];
            unset($validated['equipment']);
        }

        // If slug changes, we need to update all equipment references
        $oldSlug = $course->slug;

        $course->update($validated);

        // Clear previous equipment associations
        Equipment::where('induction_category', $oldSlug)
            ->update(['induction_category' => '']);

        // Update equipment induction_category if equipment IDs were provided
        if ($equipment && !empty($equipment)) {
            Equipment::whereIn('id', $equipment)
                ->update(['induction_category' => $course->slug]);
        }

        return redirect()->route('courses.show', $course);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \BB\Entities\Course  $course
     * @return \Illuminate\Http\Response
     */
    public function destroy(Course $course)
    {
        $course->delete();
        FlashNotification::success("Induction, {$course->name}, deleted successfully.");

        return redirect()->route('courses.index');
    }
}
