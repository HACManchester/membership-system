import React from 'react';
import {
  Box,
  Button,
  Card,
  CardContent,
  Container,
  IconButton,
  Stack,
  TextField,
  Tooltip,
  Typography,
} from '@mui/material';
import VisibilityIcon from '@mui/icons-material/Visibility';
import VisibilityOffIcon from '@mui/icons-material/VisibilityOff';
import { useForm } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';
import PageTitle from '../../../Components/PageTitle';
import { SettingResource } from '../../../types/resources';

type Props = {
  settings: SettingResource[];
};

const formatDateTime = (value: string) => new Date(value).toLocaleString('en-GB');

const lastChanged = (setting: SettingResource) => {
  if (!setting.updated_at) {
    return 'Not changed through this page yet.';
  }

  return `Last changed ${formatDateTime(setting.updated_at)}${
    setting.updated_by ? ` by ${setting.updated_by}` : ''
  }.`;
};

const SettingCard = ({ setting }: { setting: SettingResource }) => {
  // Masked by default so the secret isn't on screen for anyone looking over a shoulder.
  const [revealed, setRevealed] = React.useState(false);
  const { data, setData, put, processing, errors, reset } = useForm({ setting_value: '' });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(setting.update_url, {
      preserveScroll: true,
      onSuccess: () => reset('setting_value'),
    });
  };

  const fieldId = `setting-${setting.key}`;

  return (
    <Card>
      <CardContent>
        <Typography variant="h6" component="h2" gutterBottom>
          {setting.label}
        </Typography>
        <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
          {setting.description}
        </Typography>

        <Box sx={{ mb: 2 }}>
          <Typography variant="overline" color="text.secondary">
            Current value
          </Typography>
          {setting.value === null ? (
            <Typography color="error">Not set</Typography>
          ) : (
            <Stack direction="row" alignItems="center" spacing={1}>
              <Typography sx={{ fontFamily: 'monospace', fontSize: '1.25rem' }}>
                {revealed ? setting.value : '••••••••'}
              </Typography>
              <Tooltip title={revealed ? 'Hide' : 'Show'}>
                <IconButton
                  size="small"
                  onClick={() => setRevealed(!revealed)}
                  aria-label={revealed ? `Hide ${setting.label}` : `Show ${setting.label}`}
                >
                  {revealed ? <VisibilityOffIcon /> : <VisibilityIcon />}
                </IconButton>
              </Tooltip>
            </Stack>
          )}
          <Typography variant="caption" color="text.secondary">
            {lastChanged(setting)}
          </Typography>
        </Box>

        <form onSubmit={handleSubmit}>
          <Stack direction={{ xs: 'column', sm: 'row' }} spacing={2} alignItems="flex-start">
            <TextField
              id={fieldId}
              label="New value"
              value={data.setting_value}
              onChange={(e) => setData('setting_value', e.target.value)}
              error={Boolean(errors.setting_value)}
              helperText={errors.setting_value}
              autoComplete="off"
              size="small"
              sx={{ flexGrow: 1 }}
            />
            <Button
              type="submit"
              variant="contained"
              disabled={processing || data.setting_value.trim() === ''}
            >
              Save
            </Button>
          </Stack>
        </form>
      </CardContent>
    </Card>
  );
};

const Index = ({ settings }: Props) => (
  <>
    <PageTitle title="Settings" />
    <Container sx={{ mt: 4, pb: 4 }}>
      <Stack spacing={3} sx={{ maxWidth: 720 }}>
        <Typography variant="body2" color="text.secondary">
          These values are secrets. Only admins can see this page, and changes are logged without
          the value.
        </Typography>
        {settings.map((setting) => (
          <SettingCard key={setting.key} setting={setting} />
        ))}
      </Stack>
    </Container>
  </>
);

Index.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Index;
