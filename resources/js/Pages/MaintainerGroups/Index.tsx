import React from 'react';
import {
  Typography,
  Container,
  Paper,
  Button,
  Link,
  Stack,
  Card,
  CardContent,
  Chip,
  Avatar,
  AvatarGroup,
  Box,
} from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';
import { MaintainerGroupResource } from '../../types/resources';

type Props = {
  maintainerGroups: MaintainerGroupResource[];
  can?: { create: boolean };
  urls: { create: string };
};

const Index = ({ maintainerGroups, can = { create: false }, urls }: Props) => {
  const actionButtons = (
    <Stack direction="row" justifyContent="flex-end">
      {can.create && (
        <Link href={urls.create} underline="none">
          <Button variant="contained" color="primary">
            Add a group
          </Button>
        </Link>
      )}
    </Stack>
  );

  return (
    <>
      <PageTitle title="Maintainer Groups" actionButtons={actionButtons} />
      <Container sx={{ mt: 4, pb: 4 }}>
        <Stack spacing={3}>
          <Paper sx={{ p: 3 }}>
            <Typography>
              Maintainer groups own equipment and the members who look after it. Each group belongs
              to an equipment area.
            </Typography>
          </Paper>

          {maintainerGroups.map((group) => (
            <Card key={group.id}>
              <CardContent>
                <Box
                  sx={{ display: 'flex', alignItems: 'center', gap: 1, flexWrap: 'wrap', mb: 1 }}
                >
                  <Typography variant="h6" sx={{ flexGrow: 1 }}>
                    <Link href={group.urls.show} underline="hover">
                      {group.name}
                    </Link>
                  </Typography>
                  {group.equipment_area && (
                    <Chip
                      component={Link}
                      href={group.equipment_area.url}
                      label={group.equipment_area.name}
                      size="small"
                      clickable
                    />
                  )}
                  <Chip
                    label={`${group.equipment_count} equipment`}
                    size="small"
                    variant="outlined"
                  />
                </Box>
                {group.description && (
                  <Typography color="text.secondary" sx={{ mb: 1 }}>
                    {group.description}
                  </Typography>
                )}
                {group.maintainers.length > 0 && (
                  <AvatarGroup max={8} sx={{ justifyContent: 'flex-start' }}>
                    {group.maintainers.map((member) => (
                      <Avatar
                        key={member.id}
                        src={member.profile_photo_url || undefined}
                        alt={member.name}
                        sx={{ width: 32, height: 32 }}
                      >
                        {member.name.charAt(0)}
                      </Avatar>
                    ))}
                  </AvatarGroup>
                )}
              </CardContent>
            </Card>
          ))}

          {maintainerGroups.length === 0 && (
            <Paper sx={{ p: 3 }}>
              <Typography color="text.secondary">No maintainer groups yet.</Typography>
            </Paper>
          )}
        </Stack>
      </Container>
    </>
  );
};

Index.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Index;
