import React from "react";
import {
    Typography,
    Container,
    Box,
    Paper,
    Grid2,
    Button
} from "@mui/material";
import MainLayout from "../../Layouts/MainLayout";
import PageTitle from "../../Components/PageTitle";
import { Link } from "@inertiajs/react";
import CourseSummary from "../../Components/CourseSummary";

type Equipment = {
    id: number;
    name: string;
    slug: string;
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

type Props = {
    courses: Course[];
    can?: {
        create: boolean;
    };
    urls: {
        create: string;
    };
};

const Index = ({ courses, can = { create: false }, urls }: Props) => {
    const actionButtons = can.create ? (
        <Box sx={{ display: "flex", justifyContent: "flex-end" }}>
            <Link href={urls.create}>
                <Button variant="contained" color="primary">
                    Create induction
                </Button>
            </Link>
        </Box>
    ) : null;

    return (
        <>
            <PageTitle title="Inductions" actionButtons={actionButtons} />
            <Container maxWidth="lg" sx={{ mt: 4 }}>
                <Grid2 container spacing={4}>
                    <Grid2
                        size={{
                            xs: 12,
                            md: 9,
                        }}
                    >
                        <Paper elevation={2} sx={{ p: 3, mb: 4 }}>
                            <Typography sx={{ marginBottom: "16px" }}>
                                Inductions are required before using certain
                                equipment in the Hackspace.
                            </Typography>

                            <Typography sx={{ marginBottom: "16px" }}>
                                These inductions cover safe operation according
                                to our safety protocols, and must be completed
                                even if you're already familiar with similar
                                equipment.
                            </Typography>

                            <Typography sx={{ marginBottom: "16px" }}>
                                If you would like to develop your skills further
                                on particular pieces of equipment, please keep
                                an eye on our{" "}
                                <a
                                    href="https://list.hacman.org.uk/c/events/12"
                                    target="_blank"
                                >
                                    our events forum
                                </a>{" "}
                                for upcoming events, classes, or workshops that
                                may be of interest to you.
                            </Typography>
                        </Paper>

                        {courses.map((course) => (
                            <CourseSummary key={course.id} course={course} />
                        ))}
                    </Grid2>
                </Grid2>
            </Container>
        </>
    );
};

Index.layout = (page: React.ReactNode) => <MainLayout children={page} />;

export default Index;
