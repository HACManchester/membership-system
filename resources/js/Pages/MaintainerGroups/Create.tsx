import React from 'react';
import { Typography, Container, Card, CardContent, Grid2, Link } from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';
import MaintainerGroupForm from '../../Components/MaintainerGroupForm';
import { useForm } from '@inertiajs/react';
import { Member } from '../../types/resources';

type Props = {
  equipmentAreaOptions: { id: number; name: string }[];
  urls: { index: string; store: string };
  searchUrl: string;
};

const Create = ({ equipmentAreaOptions, urls, searchUrl }: Props) => {
  const { data, setData, post, processing, errors, transform } = useForm({
    name: '',
    slug: '',
    description: '',
    equipment_area_id: '' as number | '',
    maintainers: [] as Member[],
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    transform((d) => ({ ...d, maintainers: d.maintainers.map((m) => m.id) }));
    post(urls.store);
  };

  return (
    <>
      <PageTitle title="Add a group" />
      <Container sx={{ mt: 4 }}>
        <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
          <Link href={urls.index} color="inherit" underline="hover">
            Maintainer Groups
          </Link>{' '}
          / Add
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
                  submitLabel="Create group"
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
