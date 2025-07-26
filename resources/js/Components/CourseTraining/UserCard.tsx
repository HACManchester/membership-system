import React from "react";
import {
    Card,
    CardContent,
    Avatar,
    Box,
    Typography,
    Stack,
    Tooltip,
} from "@mui/material";
import { InductionResource } from "../../types/resources";

type Props = {
    induction: InductionResource;
    actions?: React.ReactNode;
};

const UserCard: React.FC<Props> = ({ induction, actions }) => {
    if (!induction.user) {
        return null; // Skip rendering if user data is missing
    }

    const formatDateWithTooltip = (dateString: string) => {
        const date = new Date(dateString);
        const now = new Date();
        const diffMs = now.getTime() - date.getTime();
        const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
        
        // Use relative dates if within the last week
        let displayText: string;
        if (diffDays === 0) {
            displayText = "today";
        } else if (diffDays === 1) {
            displayText = "yesterday";
        } else if (diffDays < 7) {
            displayText = `${diffDays} days ago`;
        } else {
            // Use absolute date for older dates
            displayText = date.toLocaleDateString();
        }
        
        return {
            display: displayText,
            tooltip: date.toLocaleDateString() + ' ' + date.toLocaleTimeString()
        };
    };

    return (
        <Card sx={{ height: "100%" }}>
            <CardContent>
                <Box display="flex" alignItems="center" gap={2}>
                    <Avatar
                        src={induction.user.profile_photo_url || undefined}
                        alt={induction.user.name}
                    >
                        {induction.user.name?.charAt(0)}
                    </Avatar>
                    <Box flexGrow={1}>
                        <Typography variant="subtitle1">
                            {induction.user.name}
                            {induction.user.pronouns && (
                                <Typography
                                    component="span"
                                    variant="body2"
                                    color="text.secondary"
                                    ml={1}
                                >
                                    ({induction.user.pronouns})
                                </Typography>
                            )}
                        </Typography>
                        <Stack spacing={0.5}>
                            {/* Show sign-off request date if set */}
                            {induction.sign_off_requested_at && (
                                <Tooltip title={formatDateWithTooltip(induction.sign_off_requested_at).tooltip}>
                                    <Typography variant="body2" color="text.secondary">
                                        Sign-off requested {formatDateWithTooltip(induction.sign_off_requested_at).display}
                                    </Typography>
                                </Tooltip>
                            )}
                            
                            {/* Show training completion date */}
                            {induction.trained && (
                                <Tooltip title={formatDateWithTooltip(induction.trained).tooltip}>
                                    <Typography variant="body2" color="text.secondary">
                                        Trained {formatDateWithTooltip(induction.trained).display}
                                        {induction.trainer && ` by ${induction.trainer.name}`}
                                    </Typography>
                                </Tooltip>
                            )}
                            
                            {/* Show trainer status */}
                            {induction.is_trainer && (
                                <Typography variant="body2" color="text.secondary">
                                    Trainer
                                </Typography>
                            )}
                        </Stack>
                    </Box>
                </Box>
                {actions && (
                    <Box mt={2} display="flex" justifyContent="flex-end" gap={1}>
                        {actions}
                    </Box>
                )}
            </CardContent>
        </Card>
    );
};

export default UserCard;