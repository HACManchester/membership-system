import React from "react";
import {
    Typography,
    Container,
    Box,
    Paper,
    Grid2,
    Stack,
    List,
    ListItem,
    Button,
    Divider,
} from "@mui/material";
import MainLayout from "../../Layouts/MainLayout";
import PageTitle from "../../Components/PageTitle";
import { Link } from "@inertiajs/react";

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
                            md: 9
                        }}>
                        <Paper elevation={2} sx={{ p: 3, mb: 4 }}>
                            <Typography sx={{ marginBottom: "16px" }}>
                                Inductions are required before using certain
                                equipment in the Hackspace.
                            </Typography>

                            <Typography sx={{ marginBottom: "16px" }}>
                                These inductions cover safe operation according
                                to our safety protocols, even if you're already
                                familiar with similar equipment.
                            </Typography>

                            <Typography sx={{ marginBottom: "16px" }}>
                                Inductions focus on basic safe use, not skill
                                development. For more advanced training, check{" "}
                                <a
                                    href="https://list.hacman.org.uk/c/events/12"
                                    target="_blank"
                                >
                                    our events forum
                                </a>
                                .
                            </Typography>
                        </Paper>

                        {courses.map((course) => (
                            <Paper
                                elevation={2}
                                sx={{ p: 3, mb: 4 }}
                                key={course.id}
                            >
                                <Box
                                    component={Link}
                                    href={course.urls.show}
                                    sx={{
                                        textDecoration: "none",
                                        color: "inherit",
                                    }}
                                >
                                    <Typography
                                        variant="h4"
                                        component="h2"
                                        gutterBottom
                                    >
                                        {course.name}
                                    </Typography>
                                </Box>

                                <Typography sx={{ marginBottom: "16px" }}>
                                    {course.description}
                                </Typography>

                                <Grid2
                                    container
                                    spacing={2}
                                    textAlign="center"
                                    sx={{ mb: 3 }}
                                >
                                    <Grid2 size={4}>
                                        <Typography
                                            variant="subtitle2"
                                            color="text.secondary"
                                            gutterBottom
                                        >
                                            Format
                                        </Typography>
                                        <Typography variant="h6" gutterBottom>
                                            {course.format.label}
                                        </Typography>
                                        <Typography variant="body2">
                                            {course.format_description}
                                        </Typography>
                                    </Grid2>
                                    <Grid2 size={4}>
                                        <Typography
                                            variant="subtitle2"
                                            color="text.secondary"
                                            gutterBottom
                                        >
                                            Frequency
                                        </Typography>
                                        <Typography variant="h6" gutterBottom>
                                            {course.frequency.label}
                                        </Typography>
                                        <Typography variant="body2">
                                            {course.frequency_description}
                                        </Typography>
                                    </Grid2>
                                    <Grid2 size={4}>
                                        <Typography
                                            variant="subtitle2"
                                            color="text.secondary"
                                            gutterBottom
                                        >
                                            Wait Time
                                        </Typography>
                                        <Typography variant="h6">
                                            {course.wait_time}
                                        </Typography>
                                    </Grid2>
                                </Grid2>

                                <Typography sx={{ marginBottom: "16px" }}>
                                    Completing this induction will allow you to
                                    use the following equipment:
                                </Typography>

                                <Grid2 container>
                                    {course.equipment.length > 0 &&
                                        [
                                            ...Array(
                                                Math.ceil(
                                                    course.equipment.length / 2
                                                )
                                            ),
                                        ].map((_, i) => (
                                            <Grid2
                                                key={i}
                                                size={{
                                                    xs: 12,
                                                    md: 6
                                                }}>
                                                <List>
                                                    {course.equipment
                                                        .slice(i * 2, i * 2 + 2)
                                                        .map((equipment) => (
                                                            <ListItem
                                                                key={
                                                                    equipment.id
                                                                }
                                                                sx={{ py: 0.5 }}
                                                            >
                                                                <Link
                                                                    href={
                                                                        equipment
                                                                            .urls
                                                                            .show
                                                                    }
                                                                >
                                                                    {
                                                                        equipment.name
                                                                    }
                                                                </Link>
                                                            </ListItem>
                                                        ))}
                                                </List>
                                            </Grid2>
                                        ))}
                                </Grid2>
                            </Paper>
                        ))}
                    </Grid2>
                </Grid2>
            </Container>
        </>
    );
};

Index.layout = (page: React.ReactNode) => <MainLayout children={page} />;

export default Index;
