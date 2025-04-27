import React, { useState } from "react";
import {
    Typography,
    Container,
    Box,
    Paper,
    Grid2,
    Button,
    Dialog,
    DialogActions,
    DialogContent,
    DialogContentText,
    DialogTitle,
    List,
    ListItem,
    Divider,
} from "@mui/material";
import MainLayout from "../../Layouts/MainLayout";
import PageTitle from "../../Components/PageTitle";
import { Link, useForm } from "@inertiajs/react";

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
    course: Course;
    can: {
        update: boolean;
        delete: boolean;
    };
    urls: {
        index: string;
        edit: string;
        destroy: string;
    };
};

const Show = ({ course, can, urls }: Props) => {
    const [deleteModalOpen, setDeleteModalOpen] = useState(false);
    const { delete: destroy } = useForm();

    const handleDelete = () => {
        destroy(urls.destroy);
        setDeleteModalOpen(false);
    };

    const actionButtons = (
        <Box sx={{ display: "flex", gap: 2 }}>
            {can.update && (
                <Link href={urls.edit}>
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
            <Container maxWidth="lg" sx={{ mt: 4 }}>
                <Box sx={{ display: "flex", alignItems: "center", mb: 4 }}>
                    <Box>
                        <Typography variant="h4" component="h1">
                            <Link
                                href={urls.index}
                                style={{ color: "inherit" }}
                            >
                                Inductions
                            </Link>{" "}
                            &gt; {course.name}
                        </Typography>
                    </Box>
                </Box>

                <Paper elevation={2} sx={{ p: 3, mb: 4 }}>
                    <Box
                        component={Link}
                        href={course.urls.show}
                        sx={{ textDecoration: "none", color: "inherit" }}
                    >
                        <Typography variant="h4" component="h2" gutterBottom>
                            {course.name}
                        </Typography>
                    </Box>

                    <Typography variant="body1" sx={{
                        marginBottom: "16px"
                    }}>
                        {course.description}
                    </Typography>

                    <Grid2 container spacing={2} textAlign="center">
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
                </Paper>

                <Paper elevation={2} sx={{ p: 3, mb: 4 }}>
                    <Typography variant="h5" component="h3" gutterBottom>
                        Equipment
                    </Typography>

                    <Typography variant="body1" sx={{
                        marginBottom: "16px"
                    }}>
                        Completing this induction will allow you to use the
                        following equipment:
                    </Typography>

                    <Grid2 container>
                        {course.equipment.length > 0 &&
                            [
                                ...Array(
                                    Math.ceil(course.equipment.length / 2)
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
                                                    key={equipment.id}
                                                    sx={{
                                                        py: 1,
                                                        borderRadius: 1,
                                                        border: "1px solid",
                                                        borderColor: "divider",
                                                        mb: 1,
                                                    }}
                                                >
                                                    <Link
                                                        href={
                                                            equipment.urls.show
                                                        }
                                                    >
                                                        {equipment.name}
                                                    </Link>
                                                </ListItem>
                                            ))}
                                    </List>
                                </Grid2>
                            ))}
                    </Grid2>
                </Paper>

                {/* Delete Confirmation Dialog */}
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
