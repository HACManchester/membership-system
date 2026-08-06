import { useState } from 'react';
import {
  Card,
  CardContent,
  Typography,
  Stack,
  Avatar,
  Link,
  Button,
  IconButton,
  Chip,
  Tooltip,
  Autocomplete,
  TextField,
  CircularProgress,
} from '@mui/material';
import CloseIcon from '@mui/icons-material/Close';
import CheckIcon from '@mui/icons-material/Check';
import SchoolIcon from '@mui/icons-material/School';
import EmailIcon from '@mui/icons-material/Email';
import { router } from '@inertiajs/react';
import { useMemberSearch } from '../../hooks/useMemberSearch';
import { Member } from '../../types/resources';

type TrainingUser = {
  id: number;
  name: string;
  pronouns: string | null;
  url: string;
};

type Trainer = {
  id: number;
  user: TrainingUser;
  can: { demote: boolean };
  urls: { demote: string };
};

type TrainedMember = {
  id: number;
  user: TrainingUser;
  is_trainer: boolean;
  trained_on: string | null;
  can: { untrain: boolean; promote: boolean };
  urls: { untrain: string; promote: string };
};

type PendingMember = {
  id: number;
  user: TrainingUser;
  requested_on: string | null;
  can: { delete: boolean; train: boolean };
  urls: { destroy: string; train: string };
};

export type EquipmentTraining = {
  trainers: Trainer[];
  trained: TrainedMember[];
  pending: PendingMember[];
};

type Props = {
  training: EquipmentTraining;
  can: { train: boolean };
  authUserId: number;
  urls: {
    requestInduction: string;
    memberSearch: string;
    emailTrainers: string;
    emailTrained: string;
    emailAwaiting: string;
  };
};

const MemberLine = ({ user }: { user: TrainingUser }) => (
  <Stack direction="row" spacing={1} alignItems="center">
    <Avatar sx={{ width: 28, height: 28, fontSize: 14 }}>
      {user.name ? user.name.charAt(0) : '?'}
    </Avatar>
    <span>
      <Link href={user.url}>{user.name}</Link>
      {user.pronouns && (
        <Typography component="span" color="text.secondary" sx={{ ml: 0.5 }}>
          ({user.pronouns})
        </Typography>
      )}
    </span>
  </Stack>
);

const EmailButton = ({ href }: { href: string }) => (
  <Button size="small" color="secondary" startIcon={<EmailIcon />} href={href}>
    Email these members
  </Button>
);

const EquipmentTrainingSections = ({ training, can, authUserId, urls }: Props) => {
  const [memberToAdd, setMemberToAdd] = useState<Member | null>(null);
  const {
    members: memberOptions,
    searching,
    search: searchMembers,
  } = useMemberSearch(urls.memberSearch);

  const post = (url: string, data: Record<string, number> = {}) =>
    router.post(url, data, { preserveScroll: true });

  const addMember = () => {
    if (!memberToAdd) return;
    router.post(
      urls.requestInduction,
      { user_id: memberToAdd.id },
      { preserveScroll: true, onSuccess: () => setMemberToAdd(null) }
    );
  };

  return (
    <Stack spacing={3} sx={{ mt: 4 }}>
      <Typography variant="h5" component="h2">
        Member statuses for this tool
      </Typography>

      {/* Trainers */}
      <Card>
        <CardContent>
          <Typography variant="h6" component="h3" gutterBottom>
            🎓 Trainers
          </Typography>
          <Typography color="text.secondary" sx={{ mb: 2 }}>
            These members are permitted to induct other members on this tool.
          </Typography>
          <Stack spacing={1}>
            {training.trainers.map((trainer) => (
              <Stack
                key={trainer.id}
                direction="row"
                justifyContent="space-between"
                alignItems="center"
              >
                <MemberLine user={trainer.user} />
                {trainer.can.demote && (
                  <Tooltip title="Remove as trainer">
                    <IconButton size="small" onClick={() => post(trainer.urls.demote)}>
                      <CloseIcon fontSize="small" />
                    </IconButton>
                  </Tooltip>
                )}
              </Stack>
            ))}
            {training.trainers.length === 0 && (
              <Typography color="text.secondary">No trainers yet.</Typography>
            )}
          </Stack>
          {can.train && (
            <Stack direction="row" sx={{ mt: 2 }}>
              <EmailButton href={urls.emailTrainers} />
            </Stack>
          )}
        </CardContent>
      </Card>

      {/* Trained members */}
      <Card>
        <CardContent>
          <Typography variant="h6" component="h3" gutterBottom>
            Trained members
          </Typography>
          <Typography color="text.secondary" sx={{ mb: 2 }}>
            {training.trained.length} member(s) are trained to use this tool.
          </Typography>
          <Stack spacing={1}>
            {training.trained.map((member) => (
              <Stack
                key={member.id}
                direction="row"
                justifyContent="space-between"
                alignItems="center"
              >
                <Stack direction="row" spacing={1} alignItems="center">
                  <MemberLine user={member.user} />
                  {member.is_trainer && <Chip label="Trainer" size="small" color="info" />}
                  {member.trained_on && (
                    <Typography variant="body2" color="text.secondary">
                      trained {member.trained_on}
                    </Typography>
                  )}
                </Stack>
                <Stack direction="row" spacing={0.5}>
                  {member.can.promote && !member.is_trainer && (
                    <Tooltip title="Promote to trainer">
                      <IconButton size="small" onClick={() => post(member.urls.promote)}>
                        <SchoolIcon fontSize="small" />
                      </IconButton>
                    </Tooltip>
                  )}
                  {member.can.untrain && (
                    <Tooltip title="Remove training">
                      <IconButton size="small" onClick={() => post(member.urls.untrain)}>
                        <CloseIcon fontSize="small" />
                      </IconButton>
                    </Tooltip>
                  )}
                </Stack>
              </Stack>
            ))}
            {training.trained.length === 0 && (
              <Typography color="text.secondary">No trained members yet.</Typography>
            )}
          </Stack>
          {can.train && (
            <Stack direction="row" sx={{ mt: 2 }}>
              <EmailButton href={urls.emailTrained} />
            </Stack>
          )}
        </CardContent>
      </Card>

      {/* Awaiting training */}
      <Card>
        <CardContent>
          <Typography variant="h6" component="h3" gutterBottom>
            Awaiting training
          </Typography>
          <Typography color="text.secondary" sx={{ mb: 2 }}>
            {training.pending.length} member(s) are awaiting training for this tool.
          </Typography>
          <Stack spacing={1}>
            {training.pending.map((member) => (
              <Stack
                key={member.id}
                direction="row"
                justifyContent="space-between"
                alignItems="center"
              >
                <Stack direction="row" spacing={1} alignItems="center">
                  <MemberLine user={member.user} />
                  {member.requested_on && (
                    <Typography variant="body2" color="text.secondary">
                      requested {member.requested_on}
                    </Typography>
                  )}
                </Stack>
                <Stack direction="row" spacing={0.5}>
                  {member.can.train && (
                    <Tooltip title="Mark as trained">
                      <IconButton
                        size="small"
                        color="success"
                        onClick={() => post(member.urls.train, { trainer_user_id: authUserId })}
                      >
                        <CheckIcon fontSize="small" />
                      </IconButton>
                    </Tooltip>
                  )}
                  {member.can.delete && (
                    <Tooltip title="Remove request">
                      <IconButton
                        size="small"
                        onClick={() => router.delete(member.urls.destroy, { preserveScroll: true })}
                      >
                        <CloseIcon fontSize="small" />
                      </IconButton>
                    </Tooltip>
                  )}
                </Stack>
              </Stack>
            ))}
            {training.pending.length === 0 && (
              <Typography color="text.secondary">Nobody is awaiting training.</Typography>
            )}
          </Stack>

          {can.train && (
            <Stack direction="row" spacing={1} alignItems="center" sx={{ mt: 3 }}>
              <Autocomplete
                options={memberOptions}
                getOptionLabel={(option) => option.name}
                filterOptions={(x) => x}
                isOptionEqualToValue={(option, value) => option.id === value.id}
                loading={searching}
                value={memberToAdd}
                onChange={(_, option) => setMemberToAdd(option)}
                onInputChange={(_, value) => searchMembers(value)}
                noOptionsText="Type to search members"
                sx={{ minWidth: 280 }}
                renderInput={(params) => (
                  <TextField
                    {...params}
                    label="Add a member"
                    size="small"
                    InputProps={{
                      ...params.InputProps,
                      endAdornment: (
                        <>
                          {searching ? <CircularProgress size={18} /> : null}
                          {params.InputProps.endAdornment}
                        </>
                      ),
                    }}
                  />
                )}
              />
              <Button variant="outlined" onClick={addMember} disabled={!memberToAdd}>
                Add
              </Button>
              <EmailButton href={urls.emailAwaiting} />
            </Stack>
          )}
        </CardContent>
      </Card>
    </Stack>
  );
};

export default EquipmentTrainingSections;
