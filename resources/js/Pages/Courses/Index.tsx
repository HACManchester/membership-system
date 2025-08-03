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
    Collapse,
} from "@mui/material";
import MainLayout from "../../Layouts/MainLayout";
import PageTitle from "../../Components/PageTitle";
import CourseSummary from "../../Components/CourseSummary";
import { CourseResource, EquipmentResource } from "../../types/resources";
import { TransitionGroup } from "react-transition-group";

const CourseGroup = ({
    title,
    courses,
    canSeeNonLiveCourses,
}: {
    title: string;
    courses: CourseResource[];
    canSeeNonLiveCourses: boolean;
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
                                canSeeNonLiveCourses={canSeeNonLiveCourses}
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
    equipmentWithoutLiveCourse: EquipmentResource[];
    canSeeNonLiveCourses: boolean;
    can?: {
        create: boolean;
    };
    urls: {
        create: string;
    };
};

const Index = ({
    courses,
    equipmentWithoutLiveCourse,
    canSeeNonLiveCourses,
    can = { create: false },
    urls,
}: Props) => {
    const [showPaused, setShowPaused] = useState(false);
    const [hideCompleted, setHideCompleted] = useState(false);
    const [hidePending, setHidePending] = useState(false);
    const [selectedRoom, setSelectedRoom] = useState<string>("all");
    const [equipmentExpanded, setEquipmentExpanded] = useState<boolean>(false);

    const isUserTrainedForCourse = (course: CourseResource) => {
        return (
            course.user_course_induction?.trained != null &&
            course.user_course_induction.trained !== ""
        );
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
        const liveFilter = !hidePending || course.live;

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

        return pauseFilter && completionFilter && roomFilter && liveFilter;
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
    const pendingCount = courses.filter((course) => !course.live).length;

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
                {pendingCount > 0 && (
                    <FormControlLabel
                        control={
                            <Switch
                                checked={hidePending}
                                onChange={(e) =>
                                    setHidePending(e.target.checked)
                                }
                            />
                        }
                        label={`Hide non-live (${pendingCount})`}
                    />
                )}
            </Stack>
        </Paper>
    );

    return (
        <>
            <PageTitle title="Inductions" actionButtons={actionButtons} />
            <Container sx={{ mt: 4, pb: 4 }}>
                <Stack spacing={4}>
                    <Stack spacing={2}>
                        <Paper sx={{ p: 3, mb: 4 }}>
                            <Stack spacing={2}>
                                <Typography>
                                    Certain equipment in the Hackspace require
                                    members to complete an induction before use.
                                    These inductions will help you learn how to
                                    use the equipment safely and effectively,
                                    according to our risk assessments and safety
                                    protocols.
                                </Typography>
                                <Typography>
                                    <strong>
                                        If you have prior experience with a
                                        particular tool:
                                    </strong>{" "}
                                    You must still complete the induction to
                                    ensure you are familiar with our procedures
                                    and protocols. Due to insurance
                                    requirements, we are unable to accept or
                                    recognise external training or
                                    certifications of any kind.
                                </Typography>
                                <Typography>
                                    Our inductions are not intended to teach or
                                    develop new skills; for skills development
                                    please subscribe to our{" "}
                                    <a
                                        href="https://list.hacman.org.uk/c/events/12"
                                        target="_blank"
                                    >
                                        events forum
                                    </a>{" "}
                                    to find out about future workshops and
                                    classes.
                                </Typography>

                                {canSeeNonLiveCourses && (
                                    <Alert severity="info">
                                        <Typography variant="body2">
                                            As an admin, area coordinator, or
                                            maintainer, you can see courses that
                                            are not yet live. These appear with
                                            special indicators and are not
                                            visible to regular members until
                                            made live.
                                        </Typography>
                                    </Alert>
                                )}
                            </Stack>

                            {equipmentWithoutLiveCourse.length > 0 && (
                                <Alert severity="warning" sx={{ mt: 3 }}>
                                    <Stack spacing={2}>
                                        <Typography>
                                            The following pieces of equipment
                                            have not been migrated to the new
                                            induction courses system. Please
                                            visit their separate equipment pages
                                            for induction information.
                                        </Typography>
                                        <Stack spacing={0.5}>
                                            <TransitionGroup>
                                                {equipmentWithoutLiveCourse
                                                    .slice(
                                                        0,
                                                        equipmentExpanded
                                                            ? Number.POSITIVE_INFINITY
                                                            : 5
                                                    )
                                                    .map((equipment) => (
                                                        <Collapse
                                                            key={equipment.id}
                                                        >
                                                            <Typography
                                                                key={
                                                                    equipment.id
                                                                }
                                                            >
                                                                •{" "}
                                                                <Link
                                                                    href={
                                                                        equipment
                                                                            .urls
                                                                            .show
                                                                    }
                                                                    target="_blank"
                                                                    rel="noopener"
                                                                >
                                                                    {
                                                                        equipment.name
                                                                    }
                                                                </Link>
                                                                {equipment.room_display && (
                                                                    <Typography
                                                                        component="span"
                                                                        color="text.secondary"
                                                                        sx={{
                                                                            ml: 1,
                                                                        }}
                                                                    >
                                                                        (
                                                                        {
                                                                            equipment.room_display
                                                                        }
                                                                        )
                                                                    </Typography>
                                                                )}
                                                            </Typography>
                                                        </Collapse>
                                                    ))}
                                            </TransitionGroup>
                                        </Stack>

                                        <Button
                                            onClick={() =>
                                                setEquipmentExpanded(
                                                    !equipmentExpanded
                                                )
                                            }
                                            variant="text"
                                            color="secondary"
                                            sx={{ alignSelf: "flex-start" }}
                                        >
                                            {equipmentExpanded
                                                ? `Show less`
                                                : `Show more (${
                                                      equipmentWithoutLiveCourse.length -
                                                      5
                                                  })`}
                                        </Button>
                                    </Stack>
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
                                canSeeNonLiveCourses={canSeeNonLiveCourses}
                            />
                        )
                    )}

                    <CourseGroup
                        title="Ungrouped"
                        courses={ungroupedCourses}
                        canSeeNonLiveCourses={canSeeNonLiveCourses}
                    />
                </Stack>
            </Container>
        </>
    );
};

Index.layout = (page: React.ReactNode) => <MainLayout children={page} />;

export default Index;
