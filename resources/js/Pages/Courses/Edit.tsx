import React from "react";
import {
    Typography,
    Container,
    Card,
    CardContent,
    Grid2,
    Link,
} from "@mui/material";
import MainLayout from "../../Layouts/MainLayout";
import PageTitle from "../../Components/PageTitle";
import CourseForm from "../../Components/CourseForm";
import CourseSummary from "../../Components/CourseSummary";
import { useForm } from "@inertiajs/react";

type Equipment = {
    id: number;
    name: string;
    slug: string;
    working: boolean;
    permaloan: boolean;
    dangerous: boolean;
    room: string | null;
    room_display: string | null;
    ppe: string[];
    photo_url: string | null;
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
        equipment: Equipment[];
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
    const { data, setData, put, processing, errors } = useForm({
        name: course.data.name,
        slug: course.data.slug,
        description: course.data.description,
        format: typeof course.data.format === 'object' ? course.data.format.value : course.data.format,
        format_description: course.data.format_description,
        frequency: typeof course.data.frequency === 'object' ? course.data.frequency.value : course.data.frequency,
        frequency_description: course.data.frequency_description,
        wait_time: course.data.wait_time,
        equipment: course.equipment
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(urls.update);
    };

    return (
        <>
            <PageTitle title="Edit induction" />
            <Container sx={{ mt: 4 }}>
                <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
                    <Link href={urls.index} color="inherit" underline="hover">
                        Inductions
                    </Link>{" "}
                    / <Link href={urls.show} color="inherit" underline="hover">
                        {course.data.name}
                    </Link>{" "}
                    / Edit
                </Typography>

                <Grid2 container spacing={4}>
                    <Grid2 size={{ xs: 12, lg: 8 }}>
                        <Card>
                            <CardContent>
                                <Typography variant="h5" component="h1" gutterBottom>
                                    Edit {course.data.name}
                                </Typography>
                                <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
                                    Update the induction details below.
                                </Typography>
                                
                                <CourseForm
                                    data={data}
                                    setData={setData}
                                    formatOptions={formatOptions}
                                    frequencyOptions={frequencyOptions}
                                    equipment={equipment}
                                    onSubmit={handleSubmit}
                                    processing={processing}
                                    errors={errors}
                                    submitLabel="Update Induction"
                                />
                            </CardContent>
                        </Card>
                    </Grid2>

                    <Grid2 size={{ xs: 12, lg: 4 }}>
                        <Card>
                            <CardContent>
                                <CourseSummary
                                    course={{
                                        name: data.name,
                                        description: data.description,
                                        format: data.format,
                                        format_description: data.format_description,
                                        frequency: data.frequency,
                                        frequency_description: data.frequency_description,
                                        wait_time: data.wait_time,
                                        equipment: equipment.filter(eq => data.equipment.includes(eq.id))
                                    }}
                                    formatOptions={formatOptions}
                                    frequencyOptions={frequencyOptions}
                                    clickable={false}
                                    isPreview={true}
                                />
                            </CardContent>
                        </Card>
                    </Grid2>
                </Grid2>
            </Container>
        </>
    );
};

Edit.layout = (page: React.ReactNode) => <MainLayout children={page} />;

export default Edit;
