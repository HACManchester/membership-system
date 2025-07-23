import React, { useState } from "react";
import {
    Typography,
    Container,
    Box,
    Paper,
    Button,
    Link,
    Alert,
    Grid2,
    FormControlLabel,
    Switch,
} from "@mui/material";
import MainLayout from "../../Layouts/MainLayout";
import PageTitle from "../../Components/PageTitle";
import CourseSummary from "../../Components/CourseSummary";

type Equipment = {
    id: number;
    name: string;
    slug: string;
    working: boolean;
    permaloan: boolean;
    dangerous: boolean;
    room: string;
    room_display: string;
    ppe: string[];
    photo_url: string | null;
    induction_category: string | null;
    urls: {
        show: string;
    };
};

type Induction = {
    key: string;
    trained: string;
    is_trainer: boolean;
};

type Course = {
    id: number;
    name: string;
    slug: string;
    description: string;
    format: { label: string; value: string };
    format_description: string;
    frequency: { label: string; value: string };
    frequency_description: string;
    wait_time: string;
    paused_at: string | null;
    is_paused: boolean;
    equipment: Equipment[];
    urls: {
        show: string;
    };
};

const CourseGroup = ({
    title,
    courses,
    userInductions,
}: {
    title: string;
    courses: Course[];
    userInductions: Induction[];
}) => {
    if (courses.length === 0) return null;

    return (
        <Box sx={{ mb: 4 }}>
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
                                userInductions={userInductions}
                            />
                        </Grid2>
                    );
                })}
            </Grid2>
        </Box>
    );
};

type Props = {
    courses: Course[];
    userInductions: Induction[];
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
    userInductions = [],
    can = { create: false },
    urls,
    isPreview = false,
}: Props) => {
    const [showPaused, setShowPaused] = useState(false);

    // Filter courses based on pause status
    const filteredCourses = courses.filter(
        (course) => showPaused || !course.is_paused
    );

    const allRooms = [
        ...new Set(
            filteredCourses.flatMap((course) =>
                course.equipment.map((e) => e.room_display)
            )
        ),
    ].sort();

    const groupedCourses = allRooms.reduce<Record<string, Course[]>>(
        (acc, room) => {
            acc[room] = filteredCourses.filter((course) =>
                course.equipment.some((e) => e.room_display === room)
            );
            return acc;
        },
        {}
    );

    const ungroupedCourses = filteredCourses.filter(
        (course) => !course.equipment || course.equipment.length === 0
    );

    const pausedCount = courses.filter((course) => course.is_paused).length;

    const actionButtons = (
        <Box
            sx={{
                display: "flex",
                justifyContent: "flex-end",
                alignItems: "center",
                gap: 2,
            }}
        >
            {pausedCount > 0 && (
                <FormControlLabel
                    control={
                        <Switch
                            checked={showPaused}
                            onChange={(e) => setShowPaused(e.target.checked)}
                        />
                    }
                    label={`Show unavailable inductions (${pausedCount})`}
                />
            )}
            {can.create && (
                <Link href={urls.create} underline="none">
                    <Button variant="contained" color="primary">
                        Create induction
                    </Button>
                </Link>
            )}
        </Box>
    );

    return (
        <>
            <PageTitle title="Inductions" actionButtons={actionButtons} />
            <Container sx={{ mt: 4 }}>
                <Paper sx={{ p: 3, mb: 4 }}>
                    <Typography sx={{ mb: 2 }}>
                        Inductions are required before using certain equipment
                        in the Hackspace. These cover safe operation according
                        to our safety protocols.
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
                                inductions section is currently only visible to
                                admins, area coordinators, and equipment
                                maintainers while we develop the system.
                            </Typography>
                            <Typography>
                                It will be made available to all members once
                                fully tested and ready.
                            </Typography>
                        </Alert>
                    )}
                </Paper>

                {Object.entries(groupedCourses).map(
                    ([areaName, areaCourses]) => (
                        <CourseGroup
                            key={areaName}
                            title={areaName}
                            courses={areaCourses}
                            userInductions={userInductions}
                        />
                    )
                )}

                <CourseGroup
                    title="Ungrouped"
                    courses={ungroupedCourses}
                    userInductions={userInductions}
                />
            </Container>
        </>
    );
};

Index.layout = (page: React.ReactNode) => <MainLayout children={page} />;

export default Index;
