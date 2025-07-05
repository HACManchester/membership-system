import React from "react";
import {
    Typography,
    Container,
    Box,
    Paper,
    Button,
    Link,
    Alert,
    Grid2,
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
    urls: {
        show: string;
    };
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
    equipment: Equipment[];
    urls: {
        show: string;
    };
};

const CourseGroup = ({
    title,
    courses,
}: {
    title: string;
    courses: Course[];
}) => {
    if (courses.length === 0) return null;

    return (
        <Box sx={{ mb: 4 }}>
            <Typography variant="h5" gutterBottom>
                {title}
            </Typography>
            <Grid2 container spacing={3}>
                {courses.map((course) => (
                    <Grid2 key={course.id} size={{ xs: 12, md: 6, lg: 4 }}>
                        <CourseSummary course={course} />
                    </Grid2>
                ))}
            </Grid2>
        </Box>
    );
};

type Props = {
    courses: Course[];
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
    const allRooms = [
        ...new Set(
            courses.flatMap((course) =>
                course.equipment.map((e) => e.room_display)
            )
        ),
    ].sort();

    const groupedCourses = allRooms.reduce<Record<string, Course[]>>(
        (acc, room) => {
            acc[room] = courses.filter((course) =>
                course.equipment.some((e) => e.room_display === room)
            );
            return acc;
        },
        {}
    );

    const ungroupedCourses = courses.filter(
        (course) => !course.equipment || course.equipment.length === 0
    );

    const actionButtons = can.create ? (
        <Box sx={{ display: "flex", justifyContent: "flex-end" }}>
            <Link href={urls.create} underline="none">
                <Button variant="contained" color="primary">
                    Create induction
                </Button>
            </Link>
        </Box>
    ) : null;

    return (
        <>
            <PageTitle title="Inductions" actionButtons={actionButtons} />
            <Container sx={{ mt: 4 }}>
                <Paper sx={{ p: 3, mb: 4 }}>
                    <Typography variant="body1" sx={{ mb: 2 }}>
                        Inductions are required before using certain equipment
                        in the Hackspace. These cover safe operation according
                        to our safety protocols.
                    </Typography>
                    <Typography variant="body2" color="text.secondary">
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
                            <Typography variant="body2" sx={{ mb: 1 }}>
                                <strong>Preview Feature:</strong> This
                                inductions section is currently only visible to
                                admins, area coordinators, and equipment
                                maintainers while we develop the system.
                            </Typography>
                            <Typography variant="body2">
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
                        />
                    )
                )}

                <CourseGroup title="Ungrouped" courses={ungroupedCourses} />
            </Container>
        </>
    );
};

Index.layout = (page: React.ReactNode) => <MainLayout children={page} />;

export default Index;
