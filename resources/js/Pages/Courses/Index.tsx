import React, { useState } from "react";
import {
    Typography,
    Container,
    Paper,
    Button,
    Link,
    Alert,
    Grid2,
    FormControlLabel,
    Switch,
    FormControl,
    InputLabel,
    Select,
    MenuItem,
    Stack,
} from "@mui/material";
import MainLayout from "../../Layouts/MainLayout";
import PageTitle from "../../Components/PageTitle";
import CourseSummary from "../../Components/CourseSummary";
import { CourseResource } from "../../types/resources";

const CourseGroup = ({
    title,
    courses,
}: {
    title: string;
    courses: CourseResource[];
}) => {
    if (courses.length === 0) return null;

    return (
        <Stack spacing={2}>
            <Typography variant="h5" gutterBottom>
                {title}
            </Typography>
            <Grid2 container spacing={3}>
                {courses.map((course) => {
                    return (
                        <Grid2 key={course.id} size={{ xs: 12, md: 6, lg: 4 }}>
                            <CourseSummary
                                course={course}
                                height="100%"
                            />
                        </Grid2>
                    );
                })}
            </Grid2>
        </Stack>
    );
};

type Props = {
    courses: CourseResource[];
    can?: {
        create: boolean;
    };
    urls: {
        create: string;
    };
    isPreview?: boolean;
};

const Index = ({
    courses,
    can = { create: false },
    urls,
    isPreview = false,
}: Props) => {
    const [showPaused, setShowPaused] = useState(false);
    const [hideCompleted, setHideCompleted] = useState(false);
    const [selectedRoom, setSelectedRoom] = useState<string>("all");

    const isUserTrainedForCourse = (course: CourseResource) => {
        return course.user_course_induction?.trained != null && course.user_course_induction.trained !== '';
    };

    const allAvailableRooms = [
        ...new Set(
            courses.flatMap((course) =>
                course.equipment.map(
                    (e) => e.room_display || "Not assigned to a room"
                )
            )
        ),
    ].sort();

    const filteredCourses = courses.filter((course) => {
        const pauseFilter = showPaused || !course.is_paused;
        const completionFilter =
            !hideCompleted || !isUserTrainedForCourse(course);

        let roomFilter = true;
        if (selectedRoom !== "all") {
            if (course.equipment.length === 0) {
                roomFilter = selectedRoom === "ungrouped";
            } else {
                roomFilter = course.equipment.some(
                    (e) =>
                        (e.room_display || "Not assigned to a room") ===
                        selectedRoom
                );
            }
        }

        return pauseFilter && completionFilter && roomFilter;
    });

    const allRooms = [
        ...new Set(
            filteredCourses.flatMap((course) =>
                course.equipment.map(
                    (e) => e.room_display || "Not assigned to a room"
                )
            )
        ),
    ].sort();

    const groupedCourses = allRooms.reduce<Record<string, CourseResource[]>>(
        (acc, room) => {
            acc[room] = filteredCourses.filter((course) =>
                course.equipment.some(
                    (e) => (e.room_display || "Not assigned to a room") === room
                )
            );
            return acc;
        },
        {}
    );

    const ungroupedCourses = filteredCourses.filter(
        (course) => !course.equipment || course.equipment.length === 0
    );

    const pausedCount = courses.filter((course) => course.is_paused).length;
    const completedCount = courses.filter((course) =>
        isUserTrainedForCourse(course)
    ).length;

    const actionButtons = (
        <Stack direction="row" justifyContent="flex-end">
            {can.create && (
                <Link href={urls.create} underline="none">
                    <Button variant="contained" color="primary">
                        Create induction
                    </Button>
                </Link>
            )}
        </Stack>
    );

    const filterControls = (
        <Paper sx={{ p: 2, mb: 4 }}>
            <Stack
                direction="row"
                spacing={2}
                // justifyContent="center"
                alignItems="center"
                flexWrap="wrap"
                useFlexGap
            >
                <FormControl size="small" sx={{ minWidth: 200 }}>
                    <InputLabel>Filter by Room</InputLabel>
                    <Select
                        value={selectedRoom}
                        label="Filter by Room"
                        onChange={(e) => setSelectedRoom(e.target.value)}
                    >
                        <MenuItem value="all">All Rooms</MenuItem>
                        {allAvailableRooms.map((room) => (
                            <MenuItem key={room} value={room}>
                                {room}
                            </MenuItem>
                        ))}
                        <MenuItem value="ungrouped">Ungrouped Courses</MenuItem>
                    </Select>
                </FormControl>

                {pausedCount > 0 && (
                    <FormControlLabel
                        control={
                            <Switch
                                checked={showPaused}
                                onChange={(e) =>
                                    setShowPaused(e.target.checked)
                                }
                            />
                        }
                        label={`Show unavailable (${pausedCount})`}
                    />
                )}
                {completedCount > 0 && (
                    <FormControlLabel
                        control={
                            <Switch
                                checked={hideCompleted}
                                onChange={(e) =>
                                    setHideCompleted(e.target.checked)
                                }
                            />
                        }
                        label={`Hide completed (${completedCount})`}
                    />
                )}
            </Stack>
        </Paper>
    );

    return (
        <>
            <PageTitle title="Inductions" actionButtons={actionButtons} />
            <Container sx={{ mt: 4 }}>
                <Stack spacing={4}>
                    <Stack spacing={2}>
                        <Paper sx={{ p: 3, mb: 4 }}>
                            <Typography sx={{ mb: 2 }}>
                                Inductions are required before using certain
                                equipment in the Hackspace. These cover safe
                                operation according to our safety protocols.
                            </Typography>
                            <Typography color="text.secondary">
                                For skills development, check our{" "}
                                <a
                                    href="https://list.hacman.org.uk/c/events/12"
                                    target="_blank"
                                >
                                    events forum
                                </a>{" "}
                                for workshops and classes.
                            </Typography>

                            {isPreview && (
                                <Alert severity="info" sx={{ mt: 3 }}>
                                    <Typography sx={{ mb: 1 }}>
                                        <strong>Preview Feature:</strong> This
                                        inductions section is currently only
                                        visible to admins, area coordinators,
                                        and equipment maintainers while we
                                        develop the system.
                                    </Typography>
                                    <Typography>
                                        It will be made available to all members
                                        once fully tested and ready.
                                    </Typography>
                                </Alert>
                            )}
                        </Paper>

                        {filterControls}
                    </Stack>
                    {Object.entries(groupedCourses).map(
                        ([areaName, areaCourses]) => (
                            <CourseGroup
                                key={areaName}
                                title={areaName}
                                courses={areaCourses}
                            />
                        )
                    )}

                    <CourseGroup
                        title="Ungrouped"
                        courses={ungroupedCourses}
                    />
                </Stack>
            </Container>
        </>
    );
};

Index.layout = (page: React.ReactNode) => <MainLayout children={page} />;

export default Index;
