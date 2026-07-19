import React from 'react';
import { Card, CardContent, Typography, Grid2, Button, Tooltip } from '@mui/material';
import { router } from '@inertiajs/react';
import CheckIcon from '@mui/icons-material/Check';
import NotificationsIcon from '@mui/icons-material/Notifications';
import UserCard from './UserCard';
import { TrainingRecordResource } from '../../types/resources';

type Props = {
  usersPendingSignOff: TrainingRecordResource[];
};

const PendingSignOffSection: React.FC<Props> = ({ usersPendingSignOff }) => {
  if (usersPendingSignOff.length === 0) {
    return null;
  }

  return (
    <Card sx={{ mb: 4 }}>
      <CardContent>
        <Typography variant="h5" component="h2" gutterBottom>
          <NotificationsIcon sx={{ mr: 1, verticalAlign: 'middle' }} />
          Pending Sign-off Requests
        </Typography>
        <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
          These members have completed their training and are awaiting sign-off. Requests older than
          7 days are automatically hidden and must be re-submitted.
        </Typography>
        <Grid2 container spacing={2}>
          {usersPendingSignOff.map((trainingRecord) => (
            <Grid2 key={trainingRecord.id} size={{ xs: 12, sm: 6, md: 4 }}>
              <UserCard
                trainingRecord={trainingRecord}
                actions={
                  trainingRecord.urls && (
                    <Tooltip title="Confirm this member has completed their training and mark them as trained">
                      <Button
                        variant="contained"
                        color="success"
                        size="small"
                        startIcon={<CheckIcon />}
                        onClick={() => router.post(trainingRecord.urls!.train)}
                      >
                        Sign Off
                      </Button>
                    </Tooltip>
                  )
                }
              />
            </Grid2>
          ))}
        </Grid2>
      </CardContent>
    </Card>
  );
};

export default PendingSignOffSection;
