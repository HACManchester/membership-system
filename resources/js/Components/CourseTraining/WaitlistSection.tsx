import React from 'react';
import { Card, CardContent, Typography, Grid2, IconButton, Tooltip } from '@mui/material';
import { router } from '@inertiajs/react';
import CheckIcon from '@mui/icons-material/Check';
import PersonRemoveIcon from '@mui/icons-material/PersonRemove';
import HourglassEmptyIcon from '@mui/icons-material/HourglassEmpty';
import UserCard from './UserCard';
import { TrainingRecordResource } from '../../types/resources';
import { formatDateRelative } from '../../utils/formatDateRelative';

type Props = {
  waitlist: TrainingRecordResource[];
};

const WaitlistSection: React.FC<Props> = ({ waitlist }) => {
  if (waitlist.length === 0) {
    return null;
  }

  const handleRemove = (trainingRecord: TrainingRecordResource) => {
    if (confirm(`Remove ${trainingRecord.user?.name} from the interested members list?`)) {
      router.delete(trainingRecord.urls!.removeFromWaitlist);
    }
  };

  return (
    <Card sx={{ mb: 4 }}>
      <CardContent>
        <Typography variant="h5" component="h2" gutterBottom>
          <HourglassEmptyIcon sx={{ mr: 1, verticalAlign: 'middle' }} />
          Interested Members
        </Typography>
        <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
          These members have registered interest in training, longest-waiting first. Some trainers
          use this list when organising training sessions to give long-waiting members priority
          (bear in mind that not all interested members may have an active need or be proactive in
          attending training).
        </Typography>
        <Grid2 container spacing={2}>
          {waitlist.map((trainingRecord) => (
            <Grid2 key={trainingRecord.id} size={{ xs: 12, sm: 6, md: 4 }}>
              <UserCard
                trainingRecord={trainingRecord}
                caption={
                  <Tooltip title={new Date(trainingRecord.created_at).toLocaleString()}>
                    <Typography variant="caption" color="text.secondary">
                      Interested {formatDateRelative(trainingRecord.created_at)}
                    </Typography>
                  </Tooltip>
                }
                actions={
                  trainingRecord.urls && (
                    <>
                      <Tooltip title="Mark as trained">
                        <IconButton
                          size="small"
                          onClick={() => router.post(trainingRecord.urls!.train)}
                          sx={{ p: 0.5 }}
                        >
                          <CheckIcon fontSize="small" />
                        </IconButton>
                      </Tooltip>
                      <Tooltip title="Remove from this list">
                        <IconButton
                          size="small"
                          onClick={() => handleRemove(trainingRecord)}
                          sx={{ p: 0.5 }}
                        >
                          <PersonRemoveIcon fontSize="small" />
                        </IconButton>
                      </Tooltip>
                    </>
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

export default WaitlistSection;
