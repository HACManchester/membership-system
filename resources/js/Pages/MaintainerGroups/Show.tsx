import React from 'react';
import {
  Typography,
  Container,
  Card,
  CardContent,
  Grid2,
  Link,
  Button,
  Stack,
  Avatar,
  Box,
  Chip,
} from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';
import { router } from '@inertiajs/react';
import { MaintainerGroupResource } from '../../types/resources';

type Props = {
  maintainerGroup: MaintainerGroupResource;
  can: { update: boolean; delete: boolean };
  urls: { index: string; edit: string; destroy: string };
};

const Show = ({ maintainerGroup, can, urls }: Props) => {
  const handleDelete = () => {
    if (window.confirm(`Delete the maintainer group "${maintainerGroup.name}"?`)) {
      router.delete(urls.destroy);
    }
  };

  const actionButtons = (
    <Stack direction="row" spacing={1} justifyContent="flex-end">
      {can.update && (
        <Link href={urls.edit} underline="none">
          <Button variant="contained" color="primary">
            Edit
          </Button>
        </Link>
      )}
      {can.delete && (
        <Button variant="outlined" color="error" onClick={handleDelete}>
          Delete
        </Button>
      )}
    </Stack>
  );

  return (
    <>
      <PageTitle title={maintainerGroup.name} actionButtons={actionButtons} />
      <Container sx={{ mt: 4, pb: 4 }}>
        <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
          <Link href={urls.index} color="inherit" underline="hover">
            Maintainer Groups
          </Link>{' '}
          / {maintainerGroup.name}
        </Typography>

        <Grid2 container spacing={4}>
          <Grid2 size={{ xs: 12, lg: 8 }}>
            <Card>
              <CardContent>
                <Stack spacing={3}>
                  {maintainerGroup.equipment_area && (
                    <div>
                      <Typography variant="overline" color="text.secondary">
                        Area
                      </Typography>
                      <div>
                        <Chip
                          component={Link}
                          href={maintainerGroup.equipment_area.url}
                          label={maintainerGroup.equipment_area.name}
                          clickable
                        />
                      </div>
                    </div>
                  )}

                  {maintainerGroup.description && (
                    <div>
                      <Typography variant="overline" color="text.secondary">
                        Description
                      </Typography>
                      <Typography>{maintainerGroup.description}</Typography>
                    </div>
                  )}

                  <div>
                    <Typography variant="overline" color="text.secondary">
                      Maintainers
                    </Typography>
                    {maintainerGroup.maintainers.length > 0 ? (
                      <Grid2 container spacing={2} sx={{ mt: 0.5 }}>
                        {maintainerGroup.maintainers.map((member) => (
                          <Grid2 key={member.id} size={{ xs: 12, sm: 6 }}>
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
                                sx={{ width: 36, height: 36 }}
                              >
                                {member.name.charAt(0)}
                              </Avatar>
                              <Typography>{member.name}</Typography>
                            </Box>
                          </Grid2>
                        ))}
                      </Grid2>
                    ) : (
                      <Typography color="text.secondary">No maintainers assigned.</Typography>
                    )}
                  </div>

                  <div>
                    <Typography variant="overline" color="text.secondary">
                      Equipment
                    </Typography>
                    {maintainerGroup.equipment && maintainerGroup.equipment.length > 0 ? (
                      <Grid2 container spacing={1} sx={{ mt: 0.5 }}>
                        {maintainerGroup.equipment.map((item) => (
                          <Grid2 key={item.url} size={{ xs: 12, sm: 6 }}>
                            <Link href={item.url}>{item.name}</Link>
                          </Grid2>
                        ))}
                      </Grid2>
                    ) : (
                      <Typography color="text.secondary">
                        No equipment assigned to this group.
                      </Typography>
                    )}
                  </div>
                </Stack>
              </CardContent>
            </Card>
          </Grid2>
        </Grid2>
      </Container>
    </>
  );
};

Show.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Show;
