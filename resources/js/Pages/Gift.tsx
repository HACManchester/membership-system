import React from 'react';
import { Button, Card, CardContent, Container, Stack, TextField, Typography } from '@mui/material';
import MainLayout from '../Layouts/MainLayout';

type Props = {
  urls: {
    register: string;
  };
};

// A plain GET form, not an Inertia one: the registration page is still Blade, so
// the browser loads it in full with the code in its query string.
const Gift = ({ urls }: Props) => {
  return (
    <Container maxWidth="sm" sx={{ py: 6 }}>
      <Card>
        <CardContent sx={{ p: { xs: 3, sm: 5 } }}>
          <Typography variant="h3" component="h1" gutterBottom>
            Hackspace Manchester
          </Typography>
          <Typography variant="h5" component="h2" gutterBottom>
            A gift for you awaits!
          </Typography>
          <Typography sx={{ mb: 3 }}>
            Enter your gift code below, and you&apos;ll be able to register your account.
          </Typography>

          <form action={urls.register} method="GET">
            <input type="hidden" name="gift_certificate" value="1" />
            <Stack spacing={2}>
              <TextField
                name="gift_code"
                label="Gift code"
                placeholder="XXX-YYY-ZZZ"
                required
                fullWidth
              />
              <Button type="submit" variant="contained" color="primary" size="large">
                Claim your gift!
              </Button>
            </Stack>
          </form>
        </CardContent>
      </Card>
    </Container>
  );
};

Gift.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Gift;
