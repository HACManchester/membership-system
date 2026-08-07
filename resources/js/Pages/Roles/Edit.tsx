import React from 'react';
import { Typography, Container, Card, CardContent, Grid2, Link } from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';
import RoleForm from '../../Components/RoleForm';
import { useForm } from '@inertiajs/react';
import { RoleResource, Member } from '../../types/resources';

type Props = {
  role: RoleResource;
  searchUrl: string;
  urls: { index: string; update: string };
};

const Edit = ({ role, searchUrl, urls }: Props) => {
  const { data, setData, put, processing, errors, transform } = useForm({
    title: role.title || '',
    description: role.description || '',
    email_public: role.email_public || '',
    email_private: role.email_private || '',
    slack_channel: role.slack_channel || '',
    members: role.members.map((m) => ({ id: m.id, name: m.name })) as Member[],
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    transform((d) => ({ ...d, members: d.members.map((m) => m.id) }));
    put(urls.update);
  };

  return (
    <>
      <PageTitle title={`Edit ${role.title || role.name}`} />
      <Container sx={{ mt: 4 }}>
        <Typography variant="body2" color="text.secondary" sx={{ mb: 3 }}>
          <Link href={urls.index} color="inherit" underline="hover">
            Roles &amp; Teams
          </Link>{' '}
          / {role.title || role.name}
        </Typography>

        <Grid2 container spacing={4}>
          <Grid2 size={{ xs: 12, lg: 8 }}>
            <Card>
              <CardContent>
                <RoleForm
                  data={data}
                  setData={setData}
                  onSubmit={handleSubmit}
                  processing={processing}
                  errors={errors}
                  roleName={role.name}
                  searchUrl={searchUrl}
                  submitLabel="Save changes"
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
