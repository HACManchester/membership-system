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
  Grid2,
  Avatar,
  Box,
} from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';
import { EquipmentAreaResource } from '../../types/resources';

type Props = {
  areas: EquipmentAreaResource[];
  can?: { create: boolean };
  urls: { create: string };
};

const Index = ({ areas, can = { create: false }, urls }: Props) => {
  const actionButtons = (
    <Stack direction="row" justifyContent="flex-end">
      {can.create && (
        <Link href={urls.create} underline="none">
          <Button variant="contained" color="primary">
            Add an area
          </Button>
        </Link>
      )}
    </Stack>
  );

  return (
    <>
      <PageTitle title="Area Coordinators" actionButtons={actionButtons} />
      <Container sx={{ mt: 4, pb: 4 }}>
        <Stack spacing={3}>
          <Paper sx={{ p: 3 }}>
            <Typography>
              Equipment areas group the space by discipline. Each area has coordinators who look
              after its equipment and maintainer groups.
            </Typography>
          </Paper>

          {areas.map((area) => (
            <Card key={area.id}>
              <CardContent>
                <Typography variant="h6" gutterBottom>
                  <Link href={area.urls.show} underline="hover">
                    {area.name}
                  </Link>
                </Typography>
                {area.description && (
                  <Typography color="text.secondary" sx={{ mb: 2 }}>
                    {area.description}
                  </Typography>
                )}
                <Typography variant="overline" color="text.secondary">
                  Area coordinators
                </Typography>
                {area.area_coordinators.length > 0 ? (
                  <Grid2 container spacing={2} sx={{ mt: 0.5 }}>
                    {area.area_coordinators.map((member) => (
                      <Grid2 key={member.id} size={{ xs: 12, sm: 6, md: 4 }}>
                        <Box
                          component={Link}
                          href={member.url}
                          sx={{
                            display: 'flex',
                            alignItems: 'center',
                            gap: 1.5,
                            textDecoration: 'none',
                            color: 'inherit',
                          }}
                        >
                          <Avatar
                            src={member.profile_photo_url || undefined}
                            sx={{ width: 32, height: 32 }}
                          >
                            {member.name.charAt(0)}
                          </Avatar>
                          <Typography variant="body2">{member.name}</Typography>
                        </Box>
                      </Grid2>
                    ))}
                  </Grid2>
                ) : (
                  <Typography color="text.secondary" variant="body2" sx={{ mt: 0.5 }}>
                    No coordinators assigned.
                  </Typography>
                )}
              </CardContent>
            </Card>
          ))}

          {areas.length === 0 && (
            <Paper sx={{ p: 3 }}>
              <Typography color="text.secondary">No equipment areas yet.</Typography>
            </Paper>
          )}
        </Stack>
      </Container>
    </>
  );
};

Index.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Index;
