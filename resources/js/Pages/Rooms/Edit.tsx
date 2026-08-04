import React from 'react';
import { Typography, Container, Card, CardContent, Grid2, Link } from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';
import RoomForm from '../../Components/RoomForm';
import { useForm } from '@inertiajs/react';
import { RoomResource } from '../../types/resources';

type Props = {
  room: RoomResource;
  urls: {
    index: string;
    show: string;
    update: string;
  };
};

const Edit = ({ room, urls }: Props) => {
  const { data, setData, put, processing, errors } = useForm({
    name: room.name,
    slug: room.slug,
    description: room.description || '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    put(urls.update);
  };

  return (
    <>
      <PageTitle title="Edit room" />
      <Container sx={{ mt: 4 }}>
        <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
          <Link href={urls.index} color="inherit" underline="hover">
            Rooms
          </Link>{' '}
          /{' '}
          <Link href={urls.show} color="inherit" underline="hover">
            {room.name}
          </Link>{' '}
          / Edit
        </Typography>

        <Grid2 container spacing={4}>
          <Grid2 size={{ xs: 12, lg: 8 }}>
            <Card>
              <CardContent>
                <RoomForm
                  data={data}
                  setData={setData}
                  onSubmit={handleSubmit}
                  processing={processing}
                  errors={errors}
                  submitLabel="Update room"
                />
              </CardContent>
            </Card>
          </Grid2>
        </Grid2>
      </Container>
    </>
  );
};

Edit.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Edit;
