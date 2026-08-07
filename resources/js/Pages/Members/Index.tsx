import React from 'react';
import { Container, Grid2, Link, Card, Box, Typography } from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';

type MemberCard = {
  id: number;
  name: string;
  photo_url: string;
  url: string;
};

type Props = {
  members: MemberCard[];
};

const Index = ({ members }: Props) => {
  return (
    <>
      <PageTitle title="Members" />
      <Container sx={{ mt: 4, pb: 4 }}>
        <Grid2 container spacing={2}>
          {members.map((member) => (
            <Grid2 key={member.id} size={{ xs: 6, sm: 4, md: 3, lg: 2 }}>
              <Card
                component={Link}
                href={member.url}
                sx={{
                  display: 'block',
                  textDecoration: 'none',
                  bgcolor: 'black',
                  borderRadius: 2,
                  overflow: 'hidden',
                }}
              >
                <Box
                  component="img"
                  src={member.photo_url}
                  alt={member.name}
                  loading="lazy"
                  sx={{ width: '100%', display: 'block', aspectRatio: '1 / 1', objectFit: 'cover' }}
                />
                <Box sx={{ p: 1 }}>
                  <Typography variant="body2" sx={{ color: 'white', fontWeight: 'bold' }} noWrap>
                    {member.name}
                  </Typography>
                </Box>
              </Card>
            </Grid2>
          ))}
          {members.length === 0 && (
            <Grid2 size={12}>
              <Typography color="text.secondary">No members to show.</Typography>
            </Grid2>
          )}
        </Grid2>
      </Container>
    </>
  );
};

Index.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Index;
