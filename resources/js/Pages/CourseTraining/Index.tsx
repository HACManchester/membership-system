import React from 'react';
import { Container, Typography, Alert, Link, Box, Button } from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';
import PendingSignOffSection from '../../Components/CourseTraining/PendingSignOffSection';
import WaitlistSection from '../../Components/CourseTraining/WaitlistSection';
import TrainersManagementSection from '../../Components/CourseTraining/TrainersManagementSection';
import TrainedMembersSection from '../../Components/CourseTraining/TrainedMembersSection';
import { TrainingRecordResource, CourseResource, Member } from '../../types/resources';

type Props = {
  course: CourseResource;
  trainers: TrainingRecordResource[];
  trainedUsers: TrainingRecordResource[];
  usersPendingSignOff: TrainingRecordResource[];
  waitlist: TrainingRecordResource[];
  memberList: Member[];
  urls: {
    bulkTrain: string;
    back: string;
  };
};

const TrainingIndex = ({
  course,
  trainers,
  trainedUsers,
  usersPendingSignOff,
  waitlist,
  memberList,
  urls,
}: Props) => {
  const actionButtons = (
    <Box sx={{ display: 'flex', gap: 2 }}>
      <Link href={course.urls.show} underline="none">
        <Button variant="contained" color="primary">
          View Course
        </Button>
      </Link>
    </Box>
  );

  return (
    <>
      <PageTitle title={`Training Management - ${course.name}`} actionButtons={actionButtons} />
      <Container sx={{ mt: 4 }}>
        <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
          <Link href="/courses" color="inherit" underline="hover">
            Inductions
          </Link>{' '}
          /{' '}
          <Link href={urls.back} color="inherit" underline="hover">
            {course.name}
          </Link>{' '}
          / Training Management
        </Typography>

        {course.is_paused && (
          <Alert severity="warning" sx={{ mb: 3 }}>
            This course is currently paused. New inductions cannot be created.
          </Alert>
        )}

        <PendingSignOffSection usersPendingSignOff={usersPendingSignOff} />

        <WaitlistSection waitlist={waitlist} />

        <TrainersManagementSection trainers={trainers} />

        <TrainedMembersSection
          trainedUsers={trainedUsers}
          memberList={memberList}
          bulkTrainUrl={urls.bulkTrain}
        />
      </Container>
    </>
  );
};

TrainingIndex.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default TrainingIndex;
