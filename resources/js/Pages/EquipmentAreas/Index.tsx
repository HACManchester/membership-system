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
  Grid,
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
    <Stack direction="row" sx={{ justifyContent: 'flex-end' }}>
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
                  <Typography sx={{ color: 'text.secondary', mb: 2 }}>
                    {area.description}
                  </Typography>
                )}
                <Typography variant="overline" sx={{ color: 'text.secondary' }}>
                  Area coordinators
                </Typography>
                {area.area_coordinators.length > 0 ? (
                  <Grid container spacing={2} sx={{ mt: 0.5 }}>
                    {area.area_coordinators.map((member) => (
                      <Grid key={member.id} size={{ xs: 12, sm: 6, md: 4 }}>
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
                      </Grid>
                    ))}
                  </Grid>
                ) : (
                  <Typography variant="body2" sx={{ color: 'text.secondary', mt: 0.5 }}>
                    No coordinators assigned.
                  </Typography>
                )}
              </CardContent>
            </Card>
          ))}

          {areas.length === 0 && (
            <Paper sx={{ p: 3 }}>
              <Typography sx={{ color: 'text.secondary' }}>No equipment areas yet.</Typography>
            </Paper>
          )}
        </Stack>
      </Container>
    </>
  );
};

Index.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Index;
