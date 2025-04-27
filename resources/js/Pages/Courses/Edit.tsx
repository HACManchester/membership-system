import React from "react";
import {
    Typography,
    Container,
    Box,
    Paper,
} from "@mui/material";
import MainLayout from "../../Layouts/MainLayout";
import PageTitle from "../../Components/PageTitle";
import CourseForm from "../../Components/CourseForm";
import { Link } from "@inertiajs/react";

type Equipment = {
    id: number;
    name: string;
    slug: string;
    urls: {
        show: string;
    };
};

type CourseData = {
    data: {
        id: number;
        name: string;
        slug: string;
        description: string;
        format: { label: string; value: string };
        format_description: string;
        frequency: { label: string; value: string };
        frequency_description: string;
        wait_time: string;
        urls: {
            show: string;
        };
    };
    equipment: number[];
};

type Props = {
    course: CourseData;
    formatOptions: Record<string, string>;
    frequencyOptions: Record<string, string>;
    equipment: Equipment[];
    urls: {
        update: string;
        show: string;
        index: string;
    };
};

const Edit = ({
    course,
    equipment,
    formatOptions,
    frequencyOptions,
    urls,
}: Props) => {
    return (
        <>
            <PageTitle title="Edit an induction" />
            <Container maxWidth="lg" sx={{ mt: 4 }}>
                <Box sx={{ mb: 4 }}>
                    <Typography variant="h4" component="h1">
                        <Link href={urls.index} style={{ color: "inherit" }}>
                            Courses
                        </Link>{" "}
                        &gt;
                        <Link
                            href={urls.show}
                            style={{
                                color: "inherit",
                                marginLeft: "0.5rem",
                                marginRight: "0.5rem",
                            }}
                        >
                            {course.data.name}
                        </Link>{" "}
                        &gt; Edit
                    </Typography>
                </Box>

                <Paper elevation={2} sx={{ p: 3 }}>
                    <CourseForm
                        course={course}
                        formatOptions={formatOptions}
                        frequencyOptions={frequencyOptions}
                        equipment={equipment}
                        submitUrl={urls.update}
                        method="put"
                        submitLabel="Update"
                    />
                </Paper>
            </Container>
        </>
    );
};

Edit.layout = (page: React.ReactNode) => <MainLayout children={page} />;

export default Edit;
