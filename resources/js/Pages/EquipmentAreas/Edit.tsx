import React from 'react';
import { Typography, Container, Card, CardContent, Grid2, Link } from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';
import EquipmentAreaForm from '../../Components/EquipmentAreaForm';
import { useForm } from '@inertiajs/react';
import { EquipmentAreaResource, Member } from '../../types/resources';

type Props = {
  area: EquipmentAreaResource;
  urls: { index: string; show: string; update: string };
  searchUrl: string;
};

const Edit = ({ area, urls, searchUrl }: Props) => {
  const { data, setData, put, processing, errors, transform } = useForm({
    name: area.name,
    slug: area.slug,
    description: area.description || '',
    area_coordinators: area.area_coordinators.map((c) => ({ id: c.id, name: c.name })) as Member[],
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    transform((d) => ({ ...d, area_coordinators: d.area_coordinators.map((m) => m.id) }));
    put(urls.update);
  };

  return (
    <>
      <PageTitle title="Edit area" />
      <Container sx={{ mt: 4 }}>
        <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
          <Link href={urls.index} color="inherit" underline="hover">
            Area Coordinators
          </Link>{' '}
          /{' '}
          <Link href={urls.show} color="inherit" underline="hover">
            {area.name}
          </Link>{' '}
          / Edit
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
                  submitLabel="Update area"
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
