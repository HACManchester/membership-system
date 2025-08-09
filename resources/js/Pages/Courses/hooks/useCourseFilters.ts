import { useState, useMemo } from "react";
import { CourseResource } from "../../../types/resources";

interface FilterState {
    showPaused: boolean;
    hideCompleted: boolean;
    hidePending: boolean;
    selectedRoom: string;
    selectedFormat: string;
}

interface FilterOptions {
    rooms: string[];
    formats: Array<{ value: string; label: string }>;
}

interface FilterCounts {
    paused: number;
    completed: number;
    pending: number;
}

export const useCourseFilters = (courses: CourseResource[]) => {
    const [filters, setFilters] = useState<FilterState>({
        showPaused: false,
        hideCompleted: false,
        hidePending: false,
        selectedRoom: "all",
        selectedFormat: "all",
    });

    const isUserTrainedForCourse = (course: CourseResource) => {
        return (
            course.user_course_induction?.trained != null &&
            course.user_course_induction.trained !== ""
        );
    };

    const filterOptions = useMemo<FilterOptions>(() => {
        const rooms = [
            ...new Set(
                courses.flatMap((course) =>
                    course.equipment.map(
                        (e) => e.room_display || "Not assigned to a room"
                    )
                )
            ),
        ].sort();

        const formatValues = [
            ...new Set(
                courses
                    .filter((course) => course.format?.value)
                    .map((course) => course.format.value)
            ),
        ].sort();

        const formats = formatValues.map((value) => ({
            value,
            label:
                courses.find((c) => c.format?.value === value)?.format.label ||
                value,
        }));

        return { rooms, formats };
    }, [courses]);

    const counts = useMemo<FilterCounts>(() => ({
        paused: courses.filter((course) => course.is_paused).length,
        completed: courses.filter((course) => isUserTrainedForCourse(course))
            .length,
        pending: courses.filter((course) => !course.live).length,
    }), [courses]);

    const filteredCourses = useMemo(() => {
        return courses.filter((course) => {
            const pauseFilter = filters.showPaused || !course.is_paused;
            const completionFilter =
                !filters.hideCompleted || !isUserTrainedForCourse(course);
            const liveFilter = !filters.hidePending || course.live;

            let roomFilter = true;
            if (filters.selectedRoom !== "all") {
                if (course.equipment.length === 0) {
                    roomFilter = filters.selectedRoom === "ungrouped";
                } else {
                    roomFilter = course.equipment.some(
                        (e) =>
                            (e.room_display || "Not assigned to a room") ===
                            filters.selectedRoom
                    );
                }
            }

            const formatFilter =
                filters.selectedFormat === "all" ||
                course.format?.value === filters.selectedFormat;

            return (
                pauseFilter &&
                completionFilter &&
                roomFilter &&
                liveFilter &&
                formatFilter
            );
        });
    }, [courses, filters]);

    const updateFilter = <K extends keyof FilterState>(
        key: K,
        value: FilterState[K]
    ) => {
        setFilters((prev) => ({ ...prev, [key]: value }));
    };

    return {
        filters,
        filteredCourses,
        filterOptions,
        counts,
        updateFilter,
    };
};