import React from 'react';
import { Box, Typography, Stack, Tooltip, Paper, Avatar } from '@mui/material';
import { TrainingRecordResource } from '../../types/resources';

type Props = {
  trainingRecord: TrainingRecordResource;
  actions?: React.ReactNode;
};

const UserCard: React.FC<Props> = ({ trainingRecord, actions }) => {
  if (!trainingRecord.user) {
    return null;
  }

  const formatDateRelative = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));

    if (diffDays === 0) return 'today';
    if (diffDays === 1) return 'yesterday';
    if (diffDays < 7) return `${diffDays}d ago`;
    if (diffDays < 30) return `${Math.floor(diffDays / 7)}w ago`;
    if (diffDays < 365) return `${Math.floor(diffDays / 30)}mo ago`;
    return date.toLocaleDateString();
  };

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
          {trainingRecord.sign_off_requested_at && (
            <Tooltip title={new Date(trainingRecord.sign_off_requested_at).toLocaleString()}>
              <Typography variant="caption" color="warning.main">
                Requested {formatDateRelative(trainingRecord.sign_off_requested_at)}
              </Typography>
            </Tooltip>
          )}

          {trainingRecord.trained && (
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
