import React from 'react';
import { Card, CardContent, Typography, Box, Button, Stack } from '@mui/material';
import LaunchIcon from '@mui/icons-material/Launch';
import MarkdownRenderer from '../MarkdownRenderer';
import RequestSignOffButton from '../RequestSignOffButton';
import CourseInterestButton from './CourseInterestButton';
import { CourseResource, TrainingRecordResource } from '../../types/resources';

type Props = {
  course: CourseResource;
  userCourseTrainingRecord: TrainingRecordResource | null;
  requestSignOffUrl: string | null;
  interestUrl: string;
  canRegisterInterest: boolean;
};

const TrainingInstructionsSection: React.FC<Props> = ({
  course,
  userCourseTrainingRecord,
  requestSignOffUrl,
  interestUrl,
  canRegisterInterest,
}) => {
  const hasTrainingContent =
    course.training_organisation_description ||
    course.schedule_url ||
    course.quiz_url ||
    course.request_induction_url ||
    requestSignOffUrl ||
    canRegisterInterest;

  if (!hasTrainingContent) {
    return null;
  }

  return (
    <Card sx={{ mb: 4 }}>
      <CardContent>
        <Typography variant="h5" component="h2" gutterBottom>
          How to Get Trained
        </Typography>

        {course.training_organisation_description && (
          <Box sx={{ mb: 3 }}>
            <MarkdownRenderer content={course.training_organisation_description} />
          </Box>
        )}

        <Stack direction="row" spacing={2}>
          {course.schedule_url && (
            <Button
              variant="contained"
              href={course.schedule_url}
              target="_blank"
              rel="noopener noreferrer"
              endIcon={<LaunchIcon />}
            >
              View Training Schedule
            </Button>
          )}

          {course.quiz_url && (
            <Button
              variant="contained"
              href={course.quiz_url}
              target="_blank"
              rel="noopener noreferrer"
              endIcon={<LaunchIcon />}
            >
              Take Online Quiz
            </Button>
          )}

          {course.request_induction_url && (
            <Button
              variant="contained"
              href={course.request_induction_url}
              target="_blank"
              rel="noopener noreferrer"
              endIcon={<LaunchIcon />}
            >
              Request Training
            </Button>
          )}

          <CourseInterestButton
            userCourseTrainingRecord={userCourseTrainingRecord}
            interestUrl={interestUrl}
            canRegisterInterest={canRegisterInterest}
          />

          <RequestSignOffButton
            userCourseTrainingRecord={userCourseTrainingRecord}
            requestSignOffUrl={requestSignOffUrl}
          />
        </Stack>
      </CardContent>
    </Card>
  );
};

export default TrainingInstructionsSection;
