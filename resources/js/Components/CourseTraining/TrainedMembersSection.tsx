import React, { useState } from "react";
import {
    Card,
    Typography,
    IconButton,
    Grid2,
    Box,
    Button,
    Collapse,
    Stack,
} from "@mui/material";
import { router } from "@inertiajs/react";
import CloseIcon from "@mui/icons-material/Close";
import SchoolIcon from "@mui/icons-material/School";
import ExpandMoreIcon from "@mui/icons-material/ExpandMore";
import ExpandLessIcon from "@mui/icons-material/ExpandLess";
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
    bulkTrainUrl,
}) => {
    const [expanded, setExpanded] = useState(false);
    const nonTrainerTrainedUsers = trainedUsers.filter(
        (user) => !user.is_trainer
    );

    // Filter out already trained members from the member list
    const trainedUserIds = new Set(
        trainedUsers.map((user) => user.user?.id).filter(Boolean)
    );
    const availableMembers = memberList.filter(
        (member) => !trainedUserIds.has(member.id)
    );

    return (
        <Card>
            <Stack spacing={2} sx={{ p: 2 }}>
                <Stack
                    justifyContent="space-between"
                    alignItems="center"
                    spacing={2}
                    direction="row"
                >
                    <Box>
                        <Typography variant="h5" component="h2" gutterBottom>
                            Trained Members
                        </Typography>
                    </Box>
                </Stack>

                <Box>
                    {nonTrainerTrainedUsers.length > 0 && (
                        <Button
                            onClick={() => setExpanded(!expanded)}
                            endIcon={
                                expanded ? (
                                    <ExpandLessIcon />
                                ) : (
                                    <ExpandMoreIcon />
                                )
                            }
                            size="small"
                            variant="outlined"
                            color="info"
                            sx={{ mb: 2 }}
                        >
                            {expanded ? "Hide" : "Show"} {nonTrainerTrainedUsers.length} members
                        </Button>
                    )}
                    <Collapse in={expanded} timeout="auto">
                        <Box>
                            <Grid2 container spacing={2}>
                                {nonTrainerTrainedUsers.map((induction) => (
                                    <Grid2
                                        key={induction.id}
                                        size={{ xs: 12, sm: 6, md: 4 }}
                                    >
                                        <UserCard
                                            induction={induction}
                                            actions={
                                                induction.urls && (
                                                    <>
                                                        <IconButton
                                                            size="small"
                                                            onClick={() =>
                                                                router.post(
                                                                    induction.urls!
                                                                        .untrain
                                                                )
                                                            }
                                                            title="Remove training"
                                                            sx={{ p: 0.5 }}
                                                        >
                                                            <CloseIcon fontSize="small" />
                                                        </IconButton>
                                                        <IconButton
                                                            size="small"
                                                            onClick={() =>
                                                                router.post(
                                                                    induction.urls!
                                                                        .promote
                                                                )
                                                            }
                                                            title="Promote to trainer"
                                                            sx={{ p: 0.5 }}
                                                        >
                                                            <SchoolIcon fontSize="small" />
                                                        </IconButton>
                                                    </>
                                                )
                                            }
                                        />
                                    </Grid2>
                                ))}
                            </Grid2>
                        </Box>
                    </Collapse>
                </Box>

                <BulkTrainingForm
                    memberList={availableMembers}
                    bulkTrainUrl={bulkTrainUrl}
                />
            </Stack>
        </Card>
    );
};

export default TrainedMembersSection;
