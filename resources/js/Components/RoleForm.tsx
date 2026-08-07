import React from 'react';
import { TextField, Button, Grid2, Typography } from '@mui/material';
import MemberMultiSelect from './MemberMultiSelect';
import { Member } from '../types/resources';

export type RoleFormData = {
  title: string;
  description: string;
  email_public: string;
  email_private: string;
  slack_channel: string;
  members: Member[];
};

type Props = {
  data: RoleFormData;
  setData: <K extends keyof RoleFormData>(key: K, value: RoleFormData[K]) => void;
  onSubmit: (e: React.FormEvent) => void;
  processing: boolean;
  errors: Record<string, string>;
  roleName: string;
  searchUrl: string;
  submitLabel?: string;
};

const RoleForm = ({
  data,
  setData,
  onSubmit,
  processing,
  errors,
  roleName,
  searchUrl,
  submitLabel = 'Save',
}: Props) => {
  return (
    <form onSubmit={onSubmit}>
      <Grid2 container spacing={3}>
        <Grid2 size={12}>
          <Typography variant="overline" color="text.secondary">
            Role key
          </Typography>
          <Typography>{roleName}</Typography>
          <Typography variant="caption" color="text.secondary">
            The internal identifier this role is matched by. It can’t be changed.
          </Typography>
        </Grid2>

        <Grid2 size={12}>
          <TextField
            label="Title"
            value={data.title}
            onChange={(e) => setData('title', e.target.value)}
            fullWidth
            error={!!errors.title}
            helperText={errors.title || 'The human-friendly name for this role.'}
          />
        </Grid2>

        <Grid2 size={12}>
          <TextField
            label="Description"
            value={data.description}
            onChange={(e) => setData('description', e.target.value)}
            fullWidth
            multiline
            rows={2}
            error={!!errors.description}
            helperText={errors.description || ''}
          />
        </Grid2>

        <Grid2 size={{ xs: 12, sm: 6 }}>
          <TextField
            label="Public email"
            value={data.email_public}
            onChange={(e) => setData('email_public', e.target.value)}
            fullWidth
            error={!!errors.email_public}
            helperText={errors.email_public || ''}
          />
        </Grid2>

        <Grid2 size={{ xs: 12, sm: 6 }}>
          <TextField
            label="Private email"
            value={data.email_private}
            onChange={(e) => setData('email_private', e.target.value)}
            fullWidth
            error={!!errors.email_private}
            helperText={errors.email_private || ''}
          />
        </Grid2>

        <Grid2 size={12}>
          <TextField
            label="Telegram channel"
            value={data.slack_channel}
            onChange={(e) => setData('slack_channel', e.target.value)}
            fullWidth
            error={!!errors.slack_channel}
            helperText={errors.slack_channel || ''}
          />
        </Grid2>

        <Grid2 size={12}>
          <MemberMultiSelect
            value={data.members}
            onChange={(members) => setData('members', members)}
            searchUrl={searchUrl}
            label="Members"
            error={!!errors.members}
            helperText={errors.members || 'Members who hold this role.'}
          />
        </Grid2>

        <Grid2 size={12} sx={{ mt: 2 }}>
          <Button
            type="submit"
            variant="contained"
            color="primary"
            disabled={processing}
            size="large"
          >
            {submitLabel}
          </Button>
        </Grid2>
      </Grid2>
    </form>
  );
};

export default RoleForm;
