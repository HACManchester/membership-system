import React from 'react';
import { Card, CardContent, Typography, Grid2, IconButton, Tooltip } from '@mui/material';
import { router } from '@inertiajs/react';
import CloseIcon from '@mui/icons-material/Close';
import SchoolIcon from '@mui/icons-material/School';
import UserCard from './UserCard';
import { TrainingRecordResource } from '../../types/resources';

type Props = {
  trainers: TrainingRecordResource[];
};

const TrainersManagementSection: React.FC<Props> = ({ trainers }) => {
  return (
    <Card sx={{ mb: 4 }}>
      <CardContent>
        <Typography variant="h5" component="h2" gutterBottom>
          <SchoolIcon sx={{ mr: 1, verticalAlign: 'middle' }} />
          Trainers
        </Typography>
        <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
          These members are permitted to induct other members on this course.
        </Typography>
        <Grid2 container spacing={2}>
          {trainers.map((trainer) => (
            <Grid2 key={trainer.id} size={{ xs: 12, sm: 6, md: 4 }}>
              <UserCard
                trainingRecord={trainer}
                actions={
                  trainer.urls && (
                    <Tooltip title="Remove trainer status">
                      <IconButton
                        size="small"
                        onClick={() => router.post(trainer.urls!.demote)}
                        sx={{ p: 0.5 }}
                      >
                        <CloseIcon fontSize="small" />
                      </IconButton>
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

export default TrainersManagementSection;
