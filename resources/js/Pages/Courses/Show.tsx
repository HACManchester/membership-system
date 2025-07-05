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
    Table,
    TableBody,
    TableCell,
    TableContainer,
    TableHead,
    TableRow,
    Paper,
    Avatar,
} from "@mui/material";
import MainLayout from "../../Layouts/MainLayout";
import PageTitle from "../../Components/PageTitle";
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
                <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
                    <Link href={urls.index} color="inherit" underline="hover">
                        Inductions
                    </Link>{" "}
                    / {course.name}
                </Typography>

                <Card sx={{ mb: 4 }}>
                    <CardContent>
                        <Typography variant="h4" component="h1" gutterBottom>
                            {course.name}
                        </Typography>
                        
                        <Typography variant="body1" color="text.secondary" sx={{ mb: 3 }}>
                            {course.description}
                        </Typography>
                        
                        <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 1, mb: 3 }}>
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
                                    <Typography variant="subtitle2" color="text.secondary">
                                        About the Format
                                    </Typography>
                                    <Typography variant="body2">
                                        {course.format_description}
                                    </Typography>
                                </Grid2>
                            )}
                            {course.frequency_description && (
                                <Grid2 size={{ xs: 12, md: 6 }}>
                                    <Typography variant="subtitle2" color="text.secondary">
                                        About the Schedule
                                    </Typography>
                                    <Typography variant="body2">
                                        {course.frequency_description}
                                    </Typography>
                                </Grid2>
                            )}
                        </Grid2>
                    </CardContent>
                </Card>

                {course.equipment.length > 0 && (
                    <Card>
                        <CardContent>
                            <Typography variant="h5" component="h2" gutterBottom>
                                Equipment Access
                            </Typography>
                            <Typography variant="body1" color="text.secondary" sx={{ mb: 3 }}>
                                Completing this induction will grant you access to the following equipment:
                            </Typography>

                            <TableContainer component={Paper} variant="outlined">
                                <Table>
                                    <TableHead>
                                        <TableRow>
                                            <TableCell></TableCell>
                                            <TableCell>Name</TableCell>
                                            <TableCell>Location</TableCell>
                                            <TableCell>PPE Required</TableCell>
                                            <TableCell>Status</TableCell>
                                            <TableCell align="center">Dangerous</TableCell>
                                        </TableRow>
                                    </TableHead>
                                    <TableBody>
                                        {course.equipment.map((equipment) => (
                                            <TableRow key={equipment.id}>
                                                <TableCell>
                                                    {equipment.photo_url ? (
                                                        <Avatar 
                                                            src={equipment.photo_url} 
                                                            alt={equipment.name}
                                                            variant="rounded"
                                                            sx={{ width: 60, height: 60 }}
                                                        />
                                                    ) : (
                                                        <Avatar 
                                                            variant="rounded"
                                                            sx={{ width: 60, height: 60, bgcolor: 'grey.300' }}
                                                        >
                                                            🔧
                                                        </Avatar>
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    <Link href={equipment.urls.show} underline="hover">
                                                        <Typography variant="body2" fontWeight="medium">
                                                            {equipment.name}
                                                        </Typography>
                                                    </Link>
                                                </TableCell>
                                                <TableCell>
                                                    <Typography variant="body2">
                                                        {equipment.room_display && (
                                                            <Box component="span" sx={{ fontWeight: 'medium' }}>
                                                                {equipment.room_display}
                                                            </Box>
                                                        )}
                                                    </Typography>
                                                </TableCell>
                                                <TableCell>
                                                    {equipment.ppe.length > 0 && (
                                                        <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 0.5 }}>
                                                            {equipment.ppe.map((item, index) => (
                                                                <Chip 
                                                                    key={index}
                                                                    label={item} 
                                                                    size="small" 
                                                                    variant="outlined"
                                                                    color="info"
                                                                />
                                                            ))}
                                                        </Box>
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 0.5 }}>
                                                        {equipment.working ? (
                                                            <Chip 
                                                                label="Working" 
                                                                size="small" 
                                                                color="success"
                                                                variant="filled"
                                                            />
                                                        ) : (
                                                            <Chip 
                                                                label="Out of action" 
                                                                size="small" 
                                                                color="error"
                                                                variant="filled"
                                                            />
                                                        )}
                                                        {equipment.permaloan ? (
                                                            <Chip 
                                                                label="Permaloan" 
                                                                size="small" 
                                                                color="warning"
                                                                variant="filled"
                                                            />
                                                        ) : null}
                                                    </Box>
                                                </TableCell>
                                                <TableCell align="center">
                                                    {equipment.dangerous ? '⚠️' : ''}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </TableContainer>
                        </CardContent>
                    </Card>
                )}

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
