import React from 'react';
import { useForm } from '@inertiajs/react';
import { Alert, Card, CardContent, Container, Link, Stack, Typography } from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';
import RouterLink from '../../Components/RouterLink';
import NotificationEmailForm, {
  NotificationEmailFormData,
} from '../../Components/NotificationEmailForm';

type Props = {
  equipment: {
    name: string;
  };
  trainingStatus: string;
  recipient: string;
  urls: {
    equipment: string;
    store: string;
  };
};

const Equipment = ({ equipment, trainingStatus, recipient, urls }: Props) => {
  const { data, setData, post, processing, errors } = useForm<NotificationEmailFormData>({
    recipient,
    subject: '',
    message: '',
    send_to_all: false,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(urls.store);
  };

  return (
    <>
      <PageTitle title="Email members by training status" />
      <Container maxWidth="md" sx={{ py: 4 }}>
        <Alert severity="info" sx={{ mb: 3 }}>
          Tool trainers may email the members who have a training record for their tool: those
          awaiting training, those trained, or the trainers. Keep every email strictly about the
          tool and non-personal; emails are copied to the board.
        </Alert>
        <Card>
          <CardContent>
            <NotificationEmailForm
              data={data}
              setData={setData}
              errors={errors}
              processing={processing}
              onSubmit={handleSubmit}
            >
              <Stack spacing={1}>
                <Typography variant="h6" component="h2">
                  Recipients
                </Typography>
                <Typography>
                  Members of{' '}
                  <Link component={RouterLink} href={urls.equipment}>
                    {equipment.name}
                  </Link>{' '}
                  with the training status <strong>{trainingStatus}</strong>
                </Typography>
                {errors.recipient && <Alert severity="error">{errors.recipient}</Alert>}
              </Stack>
            </NotificationEmailForm>
          </CardContent>
        </Card>
      </Container>
    </>
  );
};

Equipment.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Equipment;
