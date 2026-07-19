import React from 'react';
import { Button, Stack, Tooltip } from '@mui/material';
import HowToRegIcon from '@mui/icons-material/HowToReg';
import PersonAddIcon from '@mui/icons-material/PersonAdd';
import PersonRemoveIcon from '@mui/icons-material/PersonRemove';
import { router } from '@inertiajs/react';
import { TrainingRecordResource } from '../../types/resources';
import { isExpired } from '../../utils/timeRemaining';

type Props = {
  userCourseTrainingRecord: TrainingRecordResource | null;
  interestUrl: string;
  canRegisterInterest: boolean;
};

const CourseInterestButton: React.FC<Props> = ({
  userCourseTrainingRecord,
  interestUrl,
  canRegisterInterest,
}) => {
  // Hidden for self-serve courses and members who are already trained
  if (!canRegisterInterest || userCourseTrainingRecord?.trained) {
    return null;
  }

  // No record yet: offer to join the waitlist
  if (!userCourseTrainingRecord) {
    return (
      <Button
        variant="contained"
        startIcon={<PersonAddIcon />}
        onClick={() => router.post(interestUrl)}
      >
        I&apos;m interested in training
      </Button>
    );
  }

  // Withdrawal is blocked server-side while other state is on the record
  const hasPendingSignOff =
    !!userCourseTrainingRecord.sign_off_requested_at &&
    !isExpired(userCourseTrainingRecord.sign_off_expires_at);
  const canWithdraw = !userCourseTrainingRecord.is_trainer && !hasPendingSignOff;

  const handleWithdraw = () => {
    if (confirm('Withdraw your interest in training for this course?')) {
      router.delete(interestUrl);
    }
  };

  const interestedSince = new Date(userCourseTrainingRecord.created_at);

  return (
    <Stack direction="row" spacing={1}>
      <Tooltip title={`Interest registered on ${interestedSince.toLocaleString()}`}>
        <Button variant="outlined" disabled startIcon={<HowToRegIcon />} color="success">
          Interested since {interestedSince.toLocaleDateString()}
        </Button>
      </Tooltip>
      {canWithdraw && (
        <Button
          variant="outlined"
          color="error"
          startIcon={<PersonRemoveIcon />}
          onClick={handleWithdraw}
        >
          No longer interested
        </Button>
      )}
    </Stack>
  );
};

export default CourseInterestButton;
