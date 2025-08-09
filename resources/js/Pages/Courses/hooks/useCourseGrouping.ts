import { useMemo } from "react";
import { CourseResource } from "../../../types/resources";

interface GroupedCourses {
    groupedByRoom: Record<string, CourseResource[]>;
    ungroupedCourses: CourseResource[];
}

export const useCourseGrouping = (courses: CourseResource[]): GroupedCourses => {
    return useMemo(() => {
        const allRooms = [
            ...new Set(
                courses.flatMap((course) =>
                    course.equipment.map(
                        (e) => e.room_display || "Not assigned to a room"
                    )
                )
            ),
        ].sort();

        const groupedByRoom = allRooms.reduce<Record<string, CourseResource[]>>(
            (acc, room) => {
                acc[room] = courses.filter((course) =>
                    course.equipment.some(
                        (e) => (e.room_display || "Not assigned to a room") === room
                    )
                );
                return acc;
            },
            {}
        );

        const ungroupedCourses = courses.filter(
            (course) => !course.equipment || course.equipment.length === 0
        );

        return {
            groupedByRoom,
            ungroupedCourses,
        };
    }, [courses]);
};