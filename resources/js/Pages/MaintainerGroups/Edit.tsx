import React from 'react';
import { Typography, Container, Card, CardContent, Grid2, Link } from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';
import MaintainerGroupForm from '../../Components/MaintainerGroupForm';
import { useForm } from '@inertiajs/react';
import { MaintainerGroupResource, Member } from '../../types/resources';

type Props = {
  maintainerGroup: MaintainerGroupResource;
  equipmentAreaOptions: { id: number; name: string }[];
  urls: { index: string; show: string; update: string };
  searchUrl: string;
};

const Edit = ({ maintainerGroup, equipmentAreaOptions, urls, searchUrl }: Props) => {
  const { data, setData, put, processing, errors, transform } = useForm({
    name: maintainerGroup.name,
    slug: maintainerGroup.slug,
    description: maintainerGroup.description || '',
    equipment_area_id: (maintainerGroup.equipment_area?.id ?? '') as number | '',
    maintainers: maintainerGroup.maintainers.map((m) => ({ id: m.id, name: m.name })) as Member[],
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    transform((d) => ({ ...d, maintainers: d.maintainers.map((m) => m.id) }));
    put(urls.update);
  };

  return (
    <>
      <PageTitle title="Edit group" />
      <Container sx={{ mt: 4 }}>
        <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
          <Link href={urls.index} color="inherit" underline="hover">
            Maintainer Groups
          </Link>{' '}
          /{' '}
          <Link href={urls.show} color="inherit" underline="hover">
            {maintainerGroup.name}
          </Link>{' '}
          / Edit
        </Typography>

        <Grid2 container spacing={4}>
          <Grid2 size={{ xs: 12, lg: 8 }}>
            <Card>
              <CardContent>
                <MaintainerGroupForm
                  data={data}
                  setData={setData}
                  onSubmit={handleSubmit}
                  processing={processing}
                  errors={errors}
                  areaOptions={equipmentAreaOptions}
                  searchUrl={searchUrl}
                  submitLabel="Update group"
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
