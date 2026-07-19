import React from 'react';
import { Box, Typography, Stack, Tooltip, Paper, Avatar } from '@mui/material';
import { TrainingRecordResource } from '../../types/resources';
import { formatDateRelative } from '../../utils/formatDateRelative';

type Props = {
  trainingRecord: TrainingRecordResource;
  actions?: React.ReactNode;
  // Replaces the default date captions (an expired sign-off request date would
  // be misleading in contexts like the waitlist); the Trainer badge remains.
  caption?: React.ReactNode;
};

const UserCard: React.FC<Props> = ({ trainingRecord, actions, caption }) => {
  if (!trainingRecord.user) {
    return null;
  }

  return (
    <Paper
      sx={{
        p: 1.5,
        display: 'flex',
        alignItems: 'center',
        gap: 1.5,
        '&:hover': {
          bgcolor: 'action.hover',
        },
      }}
      elevation={1}
    >
      <Avatar
        src={trainingRecord.user.profile_photo_url || undefined}
        alt={trainingRecord.user.name}
        sx={{ width: 32, height: 32, fontSize: '0.875rem' }}
      >
        {trainingRecord.user.name?.charAt(0)}
      </Avatar>

      <Box flexGrow={1} minWidth={0}>
        <Stack direction="row" alignItems="baseline" spacing={1}>
          <Typography variant="body2" fontWeight={500} noWrap>
            {trainingRecord.user.name}
          </Typography>
          {trainingRecord.user.pronouns && (
            <Typography variant="caption" color="text.secondary">
              ({trainingRecord.user.pronouns})
            </Typography>
          )}
        </Stack>

        <Stack direction="row" spacing={1} alignItems="center">
          {caption}

          {!caption && trainingRecord.sign_off_requested_at && (
            <Tooltip title={new Date(trainingRecord.sign_off_requested_at).toLocaleString()}>
              <Typography variant="caption" color="warning.main">
                Requested {formatDateRelative(trainingRecord.sign_off_requested_at)}
              </Typography>
            </Tooltip>
          )}

          {!caption && trainingRecord.trained && (
            <Tooltip title={`Trained on ${new Date(trainingRecord.trained).toLocaleString()}`}>
              <Typography variant="caption" color="text.secondary">
                Trained {formatDateRelative(trainingRecord.trained)}
                {trainingRecord.trainer && ` by ${trainingRecord.trainer.name}`}
              </Typography>
            </Tooltip>
          )}

          {trainingRecord.is_trainer && (
            <Typography variant="caption" color="primary.main" fontWeight={600}>
              • Trainer
            </Typography>
          )}
        </Stack>
      </Box>

      {actions && (
        <Box display="flex" gap={0.5}>
          {actions}
        </Box>
      )}
    </Paper>
  );
};

export default UserCard;
