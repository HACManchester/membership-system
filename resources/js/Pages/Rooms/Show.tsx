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
  Chip,
} from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';
import { router } from '@inertiajs/react';
import { RoomResource } from '../../types/resources';

type Props = {
  room: RoomResource;
  can: {
    update: boolean;
    delete: boolean;
  };
  urls: {
    index: string;
    edit: string;
    destroy: string;
  };
};

const Show = ({ room, can, urls }: Props) => {
  const handleDelete = () => {
    if (
      window.confirm(
        `Delete the room "${room.name}"? This is only possible when no equipment is assigned to it.`
      )
    ) {
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
      <PageTitle title={room.name} actionButtons={actionButtons} />
      <Container sx={{ mt: 4, pb: 4 }}>
        <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
          <Link href={urls.index} color="inherit" underline="hover">
            Rooms
          </Link>{' '}
          / {room.name}
        </Typography>

        <Grid2 container spacing={4}>
          <Grid2 size={{ xs: 12, lg: 8 }}>
            <Card>
              <CardContent>
                <Stack spacing={2}>
                  <div>
                    <Typography variant="overline" color="text.secondary">
                      Slug
                    </Typography>
                    <Typography>{room.slug}</Typography>
                  </div>
                  {room.description && (
                    <div>
                      <Typography variant="overline" color="text.secondary">
                        Description
                      </Typography>
                      <Typography>{room.description}</Typography>
                    </div>
                  )}
                  <div>
                    <Typography variant="overline" color="text.secondary">
                      Equipment in this room
                    </Typography>
                    <div>
                      <Chip label={room.equipment_count} size="small" />
                    </div>
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
