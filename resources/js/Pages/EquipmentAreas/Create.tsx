import React from 'react';
import { Typography, Container, Card, CardContent, Grid2, Link } from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';
import EquipmentAreaForm from '../../Components/EquipmentAreaForm';
import { useForm } from '@inertiajs/react';
import { Member } from '../../types/resources';

type Props = {
  urls: { index: string; store: string };
  searchUrl: string;
};

const Create = ({ urls, searchUrl }: Props) => {
  const { data, setData, post, processing, errors, transform } = useForm({
    name: '',
    slug: '',
    description: '',
    area_coordinators: [] as Member[],
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    transform((d) => ({ ...d, area_coordinators: d.area_coordinators.map((m) => m.id) }));
    post(urls.store);
  };

  return (
    <>
      <PageTitle title="Add an area" />
      <Container sx={{ mt: 4 }}>
        <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
          <Link href={urls.index} color="inherit" underline="hover">
            Area Coordinators
          </Link>{' '}
          / Add
        </Typography>

        <Grid2 container spacing={4}>
          <Grid2 size={{ xs: 12, lg: 8 }}>
            <Card>
              <CardContent>
                <EquipmentAreaForm
                  data={data}
                  setData={setData}
                  onSubmit={handleSubmit}
                  processing={processing}
                  errors={errors}
                  searchUrl={searchUrl}
                  submitLabel="Create area"
                />
              </CardContent>
            </Card>
          </Grid2>
        </Grid2>
      </Container>
    </>
  );
};

Create.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Create;
