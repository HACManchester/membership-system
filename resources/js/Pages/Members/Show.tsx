import React from 'react';
import {
  Container,
  Grid2,
  Card,
  CardContent,
  Typography,
  Button,
  Link,
  Stack,
  Box,
  List,
  ListItem,
  Divider,
} from '@mui/material';
import EditIcon from '@mui/icons-material/Edit';
import PersonIcon from '@mui/icons-material/Person';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';

type ProfileLinks = {
  github: string | null;
  twitter: string | null;
  telegram: string | null;
  facebook: string | null;
  website: string | null;
};

type Profile = {
  name: string;
  pronouns: string | null;
  photo_url: string;
  tagline: string | null;
  description: string | null;
  links: ProfileLinks;
  irc: string | null;
  skills: { name: string; icon: string }[];
};

type Props = {
  profile: Profile;
  can: { edit: boolean; viewAccount: boolean };
  urls: { index: string; editProfile: string; account: string };
};

const SOCIAL_LABELS: { key: keyof ProfileLinks; label: string }[] = [
  { key: 'github', label: 'GitHub' },
  { key: 'twitter', label: 'Twitter' },
  { key: 'telegram', label: 'Telegram' },
  { key: 'facebook', label: 'Facebook' },
  { key: 'website', label: 'Website' },
];

const Show = ({ profile, can, urls }: Props) => {
  const socialLinks = SOCIAL_LABELS.filter(({ key }) => profile.links[key]);

  const actionButtons = (
    <Stack direction="row" spacing={1} justifyContent="flex-end">
      {can.edit && (
        <Link href={urls.editProfile} underline="none">
          <Button variant="outlined" startIcon={<EditIcon />}>
            Edit Profile
          </Button>
        </Link>
      )}
      {can.viewAccount && (
        <Link href={urls.account} underline="none">
          <Button variant="contained" color="info" startIcon={<PersonIcon />}>
            Member Account
          </Button>
        </Link>
      )}
    </Stack>
  );

  return (
    <>
      <PageTitle
        title={`${profile.name}${profile.pronouns ? ` (${profile.pronouns})` : ''}`}
        actionButtons={actionButtons}
      />
      <Container sx={{ mt: 4, pb: 4 }}>
        <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
          <Link href={urls.index} color="inherit" underline="hover">
            Members
          </Link>{' '}
          / {profile.name}
        </Typography>

        <Grid2 container spacing={4}>
          <Grid2 size={{ xs: 12, sm: 5, md: 4 }}>
            <Box
              component="img"
              src={profile.photo_url}
              alt={profile.name}
              sx={{ width: '100%', borderRadius: 2, display: 'block' }}
            />
          </Grid2>

          <Grid2 size={{ xs: 12, sm: 7, md: 8 }}>
            <Card>
              <CardContent>
                {profile.tagline && (
                  <Typography variant="h5" component="h2" gutterBottom>
                    {profile.tagline}
                  </Typography>
                )}
                {profile.description && (
                  <Typography sx={{ whiteSpace: 'pre-line', mb: 2 }}>
                    {profile.description}
                  </Typography>
                )}

                {(socialLinks.length > 0 || profile.irc) && (
                  <>
                    <Divider sx={{ my: 2 }} />
                    <List dense>
                      {socialLinks.map(({ key, label }) => (
                        <ListItem key={key} disableGutters>
                          {label} —{' '}
                          <Link
                            href={profile.links[key] as string}
                            target="_blank"
                            rel="noopener noreferrer"
                            sx={{ ml: 0.5 }}
                          >
                            {profile.links[key]}
                          </Link>
                        </ListItem>
                      ))}
                      {profile.irc && <ListItem disableGutters>IRC — {profile.irc}</ListItem>}
                    </List>
                  </>
                )}
              </CardContent>
            </Card>
          </Grid2>

          {profile.skills.length > 0 && (
            <Grid2 size={12}>
              <Card>
                <CardContent>
                  <Typography variant="h6" component="h3" gutterBottom>
                    Skills
                  </Typography>
                  <Grid2 container spacing={2}>
                    {profile.skills.map((skill) => (
                      <Grid2 key={skill.name} size={{ xs: 6, sm: 4, md: 3 }}>
                        <Stack alignItems="center" spacing={1}>
                          <Box
                            component="img"
                            src={`/img/skills/${skill.icon}`}
                            alt={skill.name}
                            sx={{ width: 80, height: 80, objectFit: 'contain' }}
                          />
                          <Typography variant="body2" align="center">
                            {skill.name}
                          </Typography>
                        </Stack>
                      </Grid2>
                    ))}
                  </Grid2>
                </CardContent>
              </Card>
            </Grid2>
          )}
        </Grid2>
      </Container>
    </>
  );
};

Show.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Show;
