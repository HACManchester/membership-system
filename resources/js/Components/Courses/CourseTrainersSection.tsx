import React from "react";
import {
    Card,
    CardContent,
    Typography,
    Grid2,
    Box,
    Avatar,
} from "@mui/material";
import SchoolIcon from "@mui/icons-material/School";
import { InductionResource } from "../../types/resources";

type Props = {
    trainers: InductionResource[];
};

const CourseTrainersSection: React.FC<Props> = ({ trainers }) => {
    if (!trainers || trainers.length === 0) {
        return null;
    }

    return (
        <Card sx={{ mb: 4 }}>
            <CardContent>
                <Typography
                    variant="h5"
                    component="h2"
                    gutterBottom
                >
                    <SchoolIcon sx={{ mr: 1, verticalAlign: "middle" }} />
                    Course Trainers
                </Typography>
                <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
                    These members are authorized to provide training and sign-offs for this course.
                </Typography>
                <Grid2 container spacing={2}>
                    {trainers.map((trainer) => (
                        trainer.user && (
                            <Grid2 key={trainer.id} size={{ xs: 12, sm: 6, md: 4 }}>
                                <Box
                                    sx={{
                                        display: "flex",
                                        alignItems: "center",
                                        gap: 2,
                                        p: 2,
                                        border: 1,
                                        borderColor: "divider",
                                        borderRadius: 1,
                                        bgcolor: "background.paper",
                                    }}
                                >
                                    <Avatar
                                        src={trainer.user.profile_photo_url || undefined}
                                        alt={trainer.user.name}
                                        sx={{ width: 40, height: 40 }}
                                    >
                                        {trainer.user.name.charAt(0)}
                                    </Avatar>
                                    <Box>
                                        <Typography variant="subtitle2">
                                            {trainer.user.name}
                                        </Typography>
                                        {trainer.user.pronouns && (
                                            <Typography
                                                variant="caption"
                                                color="text.secondary"
                                            >
                                                ({trainer.user.pronouns})
                                            </Typography>
                                        )}
                                    </Box>
                                </Box>
                            </Grid2>
                        )
                    ))}
                </Grid2>
            </CardContent>
        </Card>
    );
};

export default CourseTrainersSection;