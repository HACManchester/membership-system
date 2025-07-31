import React, { useState } from "react";
import {
    Typography,
    Container,
    Box,
    Grid2,
    Button,
    Dialog,
    DialogActions,
    DialogContent,
    DialogContentText,
    DialogTitle,
    Card,
    CardContent,
    Chip,
    Link,
    Alert,
} from "@mui/material";
import PauseIcon from "@mui/icons-material/Pause";
import CheckCircleIcon from "@mui/icons-material/CheckCircle";
import SchoolIcon from "@mui/icons-material/School";
import MainLayout from "../../Layouts/MainLayout";
import PageTitle from "../../Components/PageTitle";
import MarkdownRenderer from "../../Components/MarkdownRenderer";
import { useForm, router } from "@inertiajs/react";
import { InductionResource, CourseResource, EquipmentResource } from "../../types/resources";
import RequestSignOffButton from "../../Components/RequestSignOffButton";
import CourseTrainersSection from "../../Components/Courses/CourseTrainersSection";
import EquipmentAccessTable from "../../Components/Courses/EquipmentAccessTable";
import TrainingInstructionsSection from "../../Components/Courses/TrainingInstructionsSection";

type Props = {
    course: CourseResource;
    canSeeNonLiveCourses: boolean;
    can: {
        update: boolean;
        delete: boolean;
        viewTraining: boolean;
    };
    urls: {
        index: string;
        edit: string;
        destroy: string;
        training: string;
        requestSignOff: string | null;
    };
};

const Show = ({
    course,
    canSeeNonLiveCourses,
    can,
    urls,
}: Props) => {
    const [deleteModalOpen, setDeleteModalOpen] = useState(false);
    const { delete: destroy } = useForm();

    const isUserTrainedForCourse = course.user_course_induction?.trained != null && course.user_course_induction.trained !== '';

    const handleDelete = () => {
        destroy(urls.destroy);
        setDeleteModalOpen(false);
    };

    const actionButtons = (
        <Box sx={{ display: "flex", gap: 2 }}>
            {can.viewTraining && (
                <Link href={urls.training} underline="none">
                    <Button
                        variant="contained"
                        color="primary"
                        startIcon={<SchoolIcon />}
                    >
                        Manage Training
                    </Button>
                </Link>
            )}
            {can.update && (
                <Link href={urls.edit} underline="none">
                    <Button variant="contained" color="secondary">
                        Edit
                    </Button>
                </Link>
            )}
            {can.delete && (
                <Button
                    variant="contained"
                    color="error"
                    onClick={() => setDeleteModalOpen(true)}
                >
                    Delete
                </Button>
            )}
        </Box>
    );

    return (
        <>
            <PageTitle title={course.name} actionButtons={actionButtons} />
            <Container sx={{ mt: 4 }}>
                <Typography
                    variant="body2"
                    color="text.secondary"
                    sx={{ mb: 3 }}
                >
                    <Link href={urls.index} color="inherit" underline="hover">
                        Inductions
                    </Link>{" "}
                    / {course.name}
                </Typography>

                <Card sx={{ mb: 4 }}>
                    <CardContent>
                        <Box
                            sx={{
                                display: "flex",
                                alignItems: "center",
                                gap: 2,
                                mb: 2,
                            }}
                        >
                            <Typography
                                variant="h4"
                                component="h1"
                                sx={{ flexGrow: 1 }}
                            >
                                {course.name}
                            </Typography>
                            {isUserTrainedForCourse && (
                                <Box
                                    sx={{
                                        display: "flex",
                                        alignItems: "center",
                                        gap: 1,
                                        color: "success.main",
                                    }}
                                >
                                    <CheckCircleIcon />
                                    <Typography
                                        variant="body2"
                                        color="success.main"
                                        fontWeight="medium"
                                    >
                                        Completed
                                    </Typography>
                                </Box>
                            )}
                        </Box>

                        {course.is_paused && (
                            <Alert
                                severity="warning"
                                icon={<PauseIcon />}
                                sx={{ mb: 3 }}
                            >
                                This induction is currently unavailable for
                                enrollment.
                            </Alert>
                        )}

                        {canSeeNonLiveCourses && !course.live && (
                            <Alert severity="info" sx={{ mb: 3 }}>
                                <Typography variant="body2">
                                    <strong>Not yet live:</strong> This course is in development. Regular members cannot see it yet.
                                </Typography>
                            </Alert>
                        )}

                        <Box sx={{ mb: 3 }}>
                            <MarkdownRenderer
                                content={course.description}
                                variant="body1"
                            />
                        </Box>

                        <Box
                            sx={{
                                display: "flex",
                                flexWrap: "wrap",
                                gap: 1,
                                mb: 3,
                            }}
                        >
                            <Chip
                                label={course.format.label}
                                size="medium"
                                variant="outlined"
                            />
                            <Chip
                                label={course.frequency.label}
                                size="medium"
                                variant="outlined"
                            />
                            <Chip
                                label={`Wait: ${course.wait_time}`}
                                size="medium"
                                variant="outlined"
                            />
                        </Box>

                        <Grid2 container spacing={3}>
                            {course.format_description && (
                                <Grid2 size={{ xs: 12, md: 6 }}>
                                    <Typography
                                        variant="subtitle2"
                                        color="text.secondary"
                                    >
                                        About the Format
                                    </Typography>
                                    <MarkdownRenderer
                                        content={course.format_description}
                                        variant="body2"
                                    />
                                </Grid2>
                            )}
                            {course.frequency_description && (
                                <Grid2 size={{ xs: 12, md: 6 }}>
                                    <Typography
                                        variant="subtitle2"
                                        color="text.secondary"
                                    >
                                        About the Schedule
                                    </Typography>
                                    <MarkdownRenderer
                                        content={course.frequency_description}
                                        variant="body2"
                                    />
                                </Grid2>
                            )}
                        </Grid2>
                    </CardContent>
                </Card>

                <CourseTrainersSection trainers={course.trainers || []} />

                <TrainingInstructionsSection
                    course={course}
                    userCourseInduction={course.user_course_induction || null}
                    requestSignOffUrl={urls.requestSignOff}
                />

                <EquipmentAccessTable
                    equipment={course.equipment}
                    userCourseInduction={course.user_course_induction || null}
                    isUserTrained={isUserTrainedForCourse}
                />

                <Dialog
                    open={deleteModalOpen}
                    onClose={() => setDeleteModalOpen(false)}
                    aria-labelledby="alert-dialog-title"
                    aria-describedby="alert-dialog-description"
                >
                    <DialogTitle id="alert-dialog-title">
                        Confirm deletion
                    </DialogTitle>
                    <DialogContent>
                        <DialogContentText id="alert-dialog-description">
                            <p>
                                Deleting <em>{course.name}</em> will remove it
                                from the members system entirely.
                            </p>
                            <p>Are you sure you want to delete this item?</p>
                        </DialogContentText>
                    </DialogContent>
                    <DialogActions>
                        <Button onClick={() => setDeleteModalOpen(false)}>
                            Cancel
                        </Button>
                        <Button onClick={handleDelete} color="error" autoFocus>
                            Delete
                        </Button>
                    </DialogActions>
                </Dialog>
            </Container>
        </>
    );
};

Show.layout = (page: React.ReactNode) => <MainLayout children={page} />;

export default Show;
