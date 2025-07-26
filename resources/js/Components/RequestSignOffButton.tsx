import React from "react";
import { Button } from "@mui/material";
import CheckIcon from "@mui/icons-material/Check";
import HourglassTopIcon from "@mui/icons-material/HourglassTop";
import { router } from "@inertiajs/react";
import { InductionResource } from "../types/resources";
import { getTimeRemainingText, isExpired } from "../utils/timeRemaining";

type Props = {
    userCourseInduction: InductionResource | null;
    requestSignOffUrl: string | null;
};

const RequestSignOffButton: React.FC<Props> = ({
    userCourseInduction,
    requestSignOffUrl,
}) => {
    // Don't show button if no URL or user is already trained
    if (!requestSignOffUrl || userCourseInduction?.trained) {
        return null;
    }

    const signOffExpired = isExpired(
        userCourseInduction?.sign_off_expires_at || null
    );

    const handleSignOffRequest = () => {
        const message = signOffExpired
            ? "Your previous sign-off request has expired. Request sign-off again for this course? Only click this after completing the training."
            : "Request sign-off for this course? Only click this after completing the training.";

        if (confirm(message)) {
            router.post(requestSignOffUrl);
        }
    };

    // Show pending state if request is active and not expired
    if (userCourseInduction?.sign_off_requested_at && !signOffExpired) {
        const timeText =
            getTimeRemainingText(userCourseInduction.sign_off_expires_at) ||
            "pending";

        return (
            <Button
                variant="outlined"
                disabled
                startIcon={<HourglassTopIcon />}
                color="warning"
            >
                Sign-off Requested ({timeText})
            </Button>
        );
    }

    // Show request/re-request button
    return (
        <Button
            variant="contained"
            color="success"
            startIcon={<CheckIcon />}
            onClick={handleSignOffRequest}
        >
            {signOffExpired ? "Re-request Sign-off" : "Request Sign-off"}
        </Button>
    );
};

export default RequestSignOffButton;
