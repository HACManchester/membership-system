import React from 'react';
import { Typography, Container, Card, CardContent, Grid, Link } from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';
import RoomForm from '../../Components/RoomForm';
import { useForm } from '@inertiajs/react';

type Props = {
  urls: {
    index: string;
    store: string;
  };
};

const Create = ({ urls }: Props) => {
  const { data, setData, post, processing, errors } = useForm({
    name: '',
    slug: '',
    description: '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(urls.store);
  };

  return (
    <>
      <PageTitle title="Add a room" />
      <Container sx={{ mt: 4 }}>
        <Typography variant="body2" sx={{ color: 'text.secondary', mb: 3 }}>
          <Link href={urls.index} color="inherit" underline="hover">
            Rooms
          </Link>{' '}
          / Add
        </Typography>

        <Grid container spacing={4}>
          <Grid size={{ xs: 12, lg: 8 }}>
            <Card>
              <CardContent>
                <RoomForm
                  data={data}
                  setData={setData}
                  onSubmit={handleSubmit}
                  processing={processing}
                  errors={errors}
                  submitLabel="Create room"
                />
              </CardContent>
            </Card>
          </Grid>
        </Grid>
      </Container>
    </>
  );
};

Create.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Create;
