import React from 'react';
import {
  Alert,
  Box,
  Button,
  Card,
  CardContent,
  Checkbox,
  Chip,
  Container,
  FormControl,
  FormControlLabel,
  FormGroup,
  FormHelperText,
  Grid2,
  List,
  ListItem,
  ListItemText,
  Stack,
  TextField,
  Typography,
} from '@mui/material';
import { useForm } from '@inertiajs/react';
import MainLayout from '../../../Layouts/MainLayout';
import PageTitle from '../../../Components/PageTitle';
import { AccessLockdownResource } from '../../../types/resources';

type RoleOption = { id: number; name: string; title: string | null };

type Props = {
  lockdown: AccessLockdownResource | null;
  history: AccessLockdownResource[];
  roles: RoleOption[];
  defaultRoles: string[];
  urls: { store: string; destroy: string };
};

const formatDateTime = (value: string | null) =>
  value ? new Date(value).toLocaleString('en-GB') : '';

const roleLabel = (roles: RoleOption[], name: string) =>
  roles.find((role) => role.name === name)?.title || name;

const Index = ({ lockdown, history, roles, defaultRoles, urls }: Props) => {
  const {
    data,
    setData,
    post,
    delete: destroy,
    processing,
    errors,
  } = useForm({
    reason: '',
    roles: defaultRoles,
  });

  // Laravel keys per-item failures as `roles.0`, not `roles`, so a rejected role
  // name would otherwise fail silently and the button would look like a no-op.
  const roleError = Object.entries(errors).find(
    ([key]) => key === 'roles' || key.startsWith('roles.')
  )?.[1];

  const toggleRole = (name: string) => {
    setData(
      'roles',
      data.roles.includes(name) ? data.roles.filter((role) => role !== name) : [...data.roles, name]
    );
  };

  const handleStart = (e: React.FormEvent) => {
    e.preventDefault();
    if (
      !window.confirm(
        'This will remove door access for everyone outside the selected roles on the ' +
          "door system's next poll. Continue?"
      )
    ) {
      return;
    }
    post(urls.store);
  };

  const handleLift = () => {
    if (!window.confirm('Restore door access for all active members?')) {
      return;
    }
    destroy(urls.destroy);
  };

  return (
    <>
      <PageTitle title="Space Access Lockdown" />
      <Container sx={{ mt: 4, pb: 4 }}>
        <Grid2 container spacing={4}>
          <Grid2 size={{ xs: 12, lg: 8 }}>
            {lockdown ? (
              <Card>
                <CardContent>
                  <Alert severity="error" sx={{ mb: 3 }}>
                    <Typography variant="subtitle1" component="p" fontWeight="bold">
                      The space is locked down.
                    </Typography>
                    Only members holding one of the roles below can get in.
                  </Alert>

                  <Stack spacing={2}>
                    <Box>
                      <Typography variant="overline" color="text.secondary">
                        Started
                      </Typography>
                      <Typography>
                        {formatDateTime(lockdown.started_at)}
                        {lockdown.started_by ? ` by ${lockdown.started_by}` : ''}
                      </Typography>
                    </Box>

                    <Box>
                      <Typography variant="overline" color="text.secondary">
                        Reason
                      </Typography>
                      <Typography>{lockdown.reason || 'No reason recorded.'}</Typography>
                    </Box>

                    <Box>
                      <Typography variant="overline" color="text.secondary">
                        Roles keeping access
                      </Typography>
                      <Stack direction="row" spacing={1} flexWrap="wrap" useFlexGap>
                        {lockdown.roles.map((name) => (
                          <Chip key={name} label={roleLabel(roles, name)} size="small" />
                        ))}
                      </Stack>
                    </Box>

                    <Box>
                      <Button
                        variant="contained"
                        color="primary"
                        onClick={handleLift}
                        disabled={processing}
                      >
                        Lift lockdown
                      </Button>
                    </Box>
                  </Stack>
                </CardContent>
              </Card>
            ) : (
              <Card>
                <CardContent>
                  <Typography variant="h6" component="h2" gutterBottom>
                    Start a lockdown
                  </Typography>
                  <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
                    Removes door access for everyone outside the selected roles. The door system
                    picks the change up on its next poll, and lifting the lockdown restores everyone
                    the same way.
                  </Typography>

                  <form onSubmit={handleStart}>
                    <Stack spacing={3}>
                      <TextField
                        label="Reason"
                        value={data.reason}
                        onChange={(e) => setData('reason', e.target.value)}
                        error={Boolean(errors.reason)}
                        helperText={errors.reason || 'Recorded against the lockdown for the log.'}
                        fullWidth
                      />

                      <FormControl
                        component="fieldset"
                        error={Boolean(roleError) || data.roles.length === 0}
                        variant="standard"
                      >
                        <Typography variant="subtitle2" component="legend" gutterBottom>
                          Roles that keep access
                        </Typography>
                        <FormGroup>
                          {roles.map((role) => (
                            <FormControlLabel
                              key={role.id}
                              control={
                                <Checkbox
                                  checked={data.roles.includes(role.name)}
                                  onChange={() => toggleRole(role.name)}
                                />
                              }
                              label={role.title || role.name}
                            />
                          ))}
                        </FormGroup>
                        <FormHelperText>
                          {roleError ||
                            (data.roles.length === 0
                              ? 'Select at least one role - nobody would keep access.'
                              : 'Everyone outside these roles loses door access.')}
                        </FormHelperText>
                      </FormControl>

                      <Box>
                        <Button
                          type="submit"
                          variant="contained"
                          color="error"
                          disabled={processing || data.roles.length === 0}
                        >
                          Lock down the space
                        </Button>
                      </Box>
                    </Stack>
                  </form>
                </CardContent>
              </Card>
            )}
          </Grid2>

          <Grid2 size={{ xs: 12, lg: 4 }}>
            <Card>
              <CardContent>
                <Typography variant="h6" component="h2" gutterBottom>
                  Previous lockdowns
                </Typography>
                {history.length === 0 ? (
                  <Typography variant="body2" color="text.secondary">
                    None yet.
                  </Typography>
                ) : (
                  <List dense>
                    {history.map((entry) => (
                      <ListItem key={entry.id} disableGutters>
                        <ListItemText
                          primary={entry.reason || 'No reason recorded'}
                          secondary={`${formatDateTime(entry.started_at)} - ${formatDateTime(
                            entry.lifted_at
                          )}${entry.started_by ? ` - started by ${entry.started_by}` : ''}`}
                        />
                      </ListItem>
                    ))}
                  </List>
                )}
              </CardContent>
            </Card>
          </Grid2>
        </Grid2>
      </Container>
    </>
  );
};

Index.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Index;
