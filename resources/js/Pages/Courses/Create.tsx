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
    induction_category: string | null;
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
    const { data, setData, post, processing, errors } = useForm({
        name: "",
        slug: "",
        description: "",
        format: "",
        format_description: "",
        frequency: "",
        frequency_description: "",
        wait_time: "",
        training_organisation_description: "",
        schedule_url: "",
        quiz_url: "",
        request_induction_url: "",
        equipment: [] as number[],
        paused: false
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(urls.store);
    };

    return (
        <>
            <PageTitle title="Add an induction" />
            <Container sx={{ mt: 4 }}>
                <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
                    <Link href={urls.index} color="inherit" underline="hover">
                        Inductions
                    </Link>{" "}
                    / Create New
                </Typography>

                <Grid2 container spacing={4}>
                    <Grid2 size={{ xs: 12, lg: 8 }}>
                        <Card>
                            <CardContent>
                                <Typography variant="h5" component="h1" gutterBottom>
                                    Create New Induction
                                </Typography>
                                <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
                                    Fill in the details below to create a new induction course.
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
                                    submitLabel="Create Induction"
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
                                        is_paused: data.paused,
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

Create.layout = (page: React.ReactNode) => <MainLayout children={page} />;

export default Create;
