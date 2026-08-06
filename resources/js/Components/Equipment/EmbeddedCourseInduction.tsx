import React from 'react';
import {
  Card,
  CardContent,
  Typography,
  Box,
  Chip,
  Grid2,
  Button,
  Link,
  Alert,
} from '@mui/material';
import PauseIcon from '@mui/icons-material/Pause';
import CheckCircleIcon from '@mui/icons-material/CheckCircle';
import LaunchIcon from '@mui/icons-material/Launch';
import MarkdownRenderer from '../MarkdownRenderer';
import CourseTrainersSection from '../Courses/CourseTrainersSection';
import TrainingInstructionsSection from '../Courses/TrainingInstructionsSection';
import { CourseResource, TrainingRecordResource } from '../../types/resources';

type Props = {
  course: CourseResource;
  // The member's own record and trainers come from the equipment's dual-linkage
  // query (course_id OR legacy induction key), not from the course's own
  // course_id-only lookup — so legacy-trained members read as trained.
  userRecord: TrainingRecordResource | null;
  trainers: TrainingRecordResource[];
  canRegisterInterest: boolean;
  urls: {
    courseShow: string;
    requestSignOff: string;
    courseInterest: string;
  };
};

const EmbeddedCourseInduction: React.FC<Props> = ({
  course,
  userRecord,
  trainers,
  canRegisterInterest,
  urls,
}) => {
  const isUserTrained = userRecord?.trained != null && userRecord.trained !== '';

  return (
    <Box>
      <Card sx={{ mb: 4 }}>
        <CardContent>
          <Box sx={{ display: 'flex', alignItems: 'center', gap: 2, mb: 2 }}>
            <Typography variant="h5" component="h2" sx={{ flexGrow: 1 }}>
              Induction:{' '}
              <Link href={urls.courseShow} underline="hover">
                {course.name}
              </Link>
            </Typography>
            {isUserTrained && (
              <Box sx={{ display: 'flex', alignItems: 'center', gap: 1, color: 'success.main' }}>
                <CheckCircleIcon />
                <Typography variant="body2" color="success.main" fontWeight="medium">
                  Completed
                </Typography>
              </Box>
            )}
          </Box>

          {course.is_paused && (
            <Alert severity="warning" icon={<PauseIcon />} sx={{ mb: 3 }}>
              This induction is currently unavailable for enrollment.
            </Alert>
          )}

          <Box sx={{ mb: 3 }}>
            <MarkdownRenderer content={course.description} variant="body1" />
          </Box>

          <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 1, mb: 3 }}>
            <Chip label={course.format.label} size="medium" variant="outlined" />
            <Chip label={course.frequency.label} size="medium" variant="outlined" />
            <Chip label={`Wait: ${course.wait_time}`} size="medium" variant="outlined" />
          </Box>

          <Grid2 container spacing={3}>
            {course.format_description && (
              <Grid2 size={{ xs: 12, md: 6 }}>
                <Typography variant="subtitle2" color="text.secondary">
                  About the Format
                </Typography>
                <MarkdownRenderer content={course.format_description} variant="body2" />
              </Grid2>
            )}
            {course.frequency_description && (
              <Grid2 size={{ xs: 12, md: 6 }}>
                <Typography variant="subtitle2" color="text.secondary">
                  About the Schedule
                </Typography>
                <MarkdownRenderer content={course.frequency_description} variant="body2" />
              </Grid2>
            )}
          </Grid2>

          <Box sx={{ mt: 3 }}>
            <Button href={urls.courseShow} variant="outlined" endIcon={<LaunchIcon />}>
              View full course page
            </Button>
          </Box>
        </CardContent>
      </Card>

      <CourseTrainersSection trainers={trainers} />

      <TrainingInstructionsSection
        course={course}
        userCourseTrainingRecord={userRecord}
        requestSignOffUrl={urls.requestSignOff}
        interestUrl={urls.courseInterest}
        canRegisterInterest={canRegisterInterest}
      />
    </Box>
  );
};

export default EmbeddedCourseInduction;
