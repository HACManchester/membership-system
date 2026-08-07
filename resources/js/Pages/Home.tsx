import React from 'react';
import { Container, Card, CardContent, Typography, Button, Grid2, Link, Box } from '@mui/material';
import MainLayout from '../Layouts/MainLayout';

type Props = {
  urls: {
    register: string;
    gift: string;
    login: string;
  };
};

const Home = ({ urls }: Props) => {
  return (
    <Container maxWidth="md" sx={{ py: 6 }}>
      <Card>
        <CardContent sx={{ p: { xs: 3, sm: 5 } }}>
          <Typography variant="h3" component="h1" gutterBottom>
            Hackspace Manchester
          </Typography>
          <Typography variant="h6" color="text.secondary" gutterBottom>
            Welcome to the Hackspace Manchester membership system.
          </Typography>

          <Typography sx={{ mt: 2 }}>Here you can:</Typography>
          <Box component="ul" sx={{ mt: 1, mb: 3 }}>
            <li>Sign up to Hackspace Manchester</li>
            <li>Manage your membership</li>
            <li>Book tool inductions</li>
            <li>Join teams</li>
            <li>… and more!</li>
          </Box>

          <Typography sx={{ mb: 3 }}>
            For more information on Hackspace Manchester please visit{' '}
            <Link href="https://www.hacman.org.uk" target="_blank" rel="noopener noreferrer">
              www.hacman.org.uk
            </Link>
            .
          </Typography>

          <Grid2 container spacing={3}>
            <Grid2 size={{ xs: 12, sm: 6 }}>
              <Card variant="outlined" sx={{ height: '100%' }}>
                <CardContent>
                  <Typography variant="h6" gutterBottom>
                    Ready to join?
                  </Typography>
                  <Button variant="contained" color="primary" href={urls.register} sx={{ mb: 1 }}>
                    ✨ Become a member
                  </Button>
                  <div>
                    <Link href={urls.gift}>🎁 Got a gift code?</Link>
                  </div>
                </CardContent>
              </Card>
            </Grid2>
            <Grid2 size={{ xs: 12, sm: 6 }}>
              <Card variant="outlined" sx={{ height: '100%' }}>
                <CardContent>
                  <Typography variant="h6" gutterBottom>
                    Already a member?
                  </Typography>
                  <Button variant="outlined" color="primary" href={urls.login}>
                    🔑 Log in
                  </Button>
                </CardContent>
              </Card>
            </Grid2>
          </Grid2>
        </CardContent>
      </Card>
    </Container>
  );
};

Home.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Home;
