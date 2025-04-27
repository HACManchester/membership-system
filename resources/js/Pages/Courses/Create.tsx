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

type Props = {
    formatOptions: Record<string, string>;
    frequencyOptions: Record<string, string>;
    equipment: Equipment[];
    urls: {
        index: string;
        store: string;
    };
};

const Create = ({
    formatOptions,
    frequencyOptions,
    equipment,
    urls,
}: Props) => {
    return (
        <>
            <PageTitle title="Add an induction" />
            <Container maxWidth="lg" sx={{ mt: 4 }}>
                <Box sx={{ mb: 4 }}>
                    <Typography variant="h4" component="h1">
                        <Link href={urls.index} style={{ color: "inherit" }}>
                            Courses
                        </Link>{" "}
                        &gt; Create
                    </Typography>
                </Box>

                <Paper elevation={2} sx={{ p: 3 }}>
                    <CourseForm
                        formatOptions={formatOptions}
                        frequencyOptions={frequencyOptions}
                        equipment={equipment}
                        submitUrl={urls.store}
                        method="post"
                        submitLabel="Save"
                    />
                </Paper>
            </Container>
        </>
    );
};

Create.layout = (page: React.ReactNode) => <MainLayout children={page} />;

export default Create;
