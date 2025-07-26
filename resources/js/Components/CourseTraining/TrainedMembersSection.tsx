import React from "react";
import {
    Card,
    CardContent,
    Typography,
    Grid2,
    IconButton,
    Stack,
} from "@mui/material";
import { router } from "@inertiajs/react";
import CloseIcon from "@mui/icons-material/Close";
import SchoolIcon from "@mui/icons-material/School";
import UserCard from "./UserCard";
import BulkTrainingForm from "./BulkTrainingForm";
import { InductionResource, Member } from "../../types/resources";

type Props = {
    trainedUsers: InductionResource[];
    memberList: Member[];
    bulkTrainUrl: string;
};

const TrainedMembersSection: React.FC<Props> = ({ 
    trainedUsers, 
    memberList, 
    bulkTrainUrl 
}) => {
    const nonTrainerTrainedUsers = trainedUsers.filter(user => !user.is_trainer);

    return (
        <Card sx={{ mb: 4 }}>
            <CardContent>
                <Typography variant="h5" component="h2" gutterBottom>
                    Trained Members
                </Typography>
                <Typography variant="body1" color="text.secondary" sx={{ mb: 3 }}>
                    There are currently <strong>{nonTrainerTrainedUsers.length}</strong> members who are trained for this course.
                </Typography>
                
                <Grid2 container spacing={2} sx={{ mb: 3 }}>
                    {nonTrainerTrainedUsers.map((induction) => (
                        <Grid2 key={induction.id} size={{ xs: 12, sm: 6, md: 4 }}>
                            <UserCard
                                induction={induction}
                                actions={
                                    induction.urls && (
                                        <Stack direction="row" spacing={1}>
                                            <IconButton
                                                size="small"
                                                onClick={() => router.post(induction.urls!.untrain)}
                                                title="Remove training"
                                            >
                                                <CloseIcon />
                                            </IconButton>
                                            <IconButton
                                                size="small"
                                                onClick={() => router.post(induction.urls!.promote)}
                                                title="Promote to trainer"
                                            >
                                                <SchoolIcon />
                                            </IconButton>
                                        </Stack>
                                    )
                                }
                            />
                        </Grid2>
                    ))}
                </Grid2>

                <BulkTrainingForm
                    memberList={memberList}
                    bulkTrainUrl={bulkTrainUrl}
                />
            </CardContent>
        </Card>
    );
};

export default TrainedMembersSection;