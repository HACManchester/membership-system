import React, { useState } from 'react';
import {
  Card,
  Typography,
  IconButton,
  Grid2,
  Box,
  Button,
  Collapse,
  Stack,
  Tooltip,
} from '@mui/material';
import { router } from '@inertiajs/react';
import CloseIcon from '@mui/icons-material/Close';
import SchoolIcon from '@mui/icons-material/School';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';
import ExpandLessIcon from '@mui/icons-material/ExpandLess';
import UserCard from './UserCard';
import BulkTrainingForm from './BulkTrainingForm';
import { TrainingRecordResource } from '../../types/resources';

type Props = {
  trainedUsers: TrainingRecordResource[];
  memberSearchUrl: string;
  bulkTrainUrl: string;
};

const TrainedMembersSection: React.FC<Props> = ({
  trainedUsers,
  memberSearchUrl,
  bulkTrainUrl,
}) => {
  const [expanded, setExpanded] = useState(false);
  const nonTrainerTrainedUsers = trainedUsers.filter((user) => !user.is_trainer);

  // Already-trained members are excluded from the bulk-add search results.
  const trainedUserIds = trainedUsers
    .map((user) => user.user?.id)
    .filter((id): id is number => typeof id === 'number');

  return (
    <Card>
      <Stack spacing={2} sx={{ p: 2 }}>
        <Stack justifyContent="space-between" alignItems="center" spacing={2} direction="row">
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
              endIcon={expanded ? <ExpandLessIcon /> : <ExpandMoreIcon />}
              size="small"
              variant="outlined"
              color="info"
              sx={{ mb: 2 }}
            >
              {expanded ? 'Hide' : 'Show'} {nonTrainerTrainedUsers.length} members
            </Button>
          )}
          <Collapse in={expanded} timeout="auto">
            <Box>
              <Grid2 container spacing={2}>
                {nonTrainerTrainedUsers.map((trainingRecord) => (
                  <Grid2 key={trainingRecord.id} size={{ xs: 12, sm: 6, md: 4 }}>
                    <UserCard
                      trainingRecord={trainingRecord}
                      actions={
                        trainingRecord.urls && (
                          <>
                            <Tooltip title="Remove training">
                              <IconButton
                                size="small"
                                onClick={() => router.post(trainingRecord.urls!.untrain)}
                                sx={{ p: 0.5 }}
                              >
                                <CloseIcon fontSize="small" />
                              </IconButton>
                            </Tooltip>
                            <Tooltip title="Promote to trainer">
                              <IconButton
                                size="small"
                                onClick={() => router.post(trainingRecord.urls!.promote)}
                                sx={{ p: 0.5 }}
                              >
                                <SchoolIcon fontSize="small" />
                              </IconButton>
                            </Tooltip>
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
          memberSearchUrl={memberSearchUrl}
          excludeIds={trainedUserIds}
          bulkTrainUrl={bulkTrainUrl}
        />
      </Stack>
    </Card>
  );
};

export default TrainedMembersSection;
