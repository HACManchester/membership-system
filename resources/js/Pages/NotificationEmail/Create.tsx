import React from 'react';
import { useForm } from '@inertiajs/react';
import { Card, CardContent, Container, MenuItem, TextField, Typography } from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';
import NotificationEmailForm, {
  NotificationEmailFormData,
} from '../../Components/NotificationEmailForm';

type Props = {
  recipients: { value: string; label: string }[];
  urls: {
    store: string;
  };
};

const Create = ({ recipients, urls }: Props) => {
  const { data, setData, post, processing, errors, reset } = useForm<NotificationEmailFormData>({
    recipient: recipients[0]?.value ?? '',
    subject: '',
    message: '',
    send_to_all: false,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(urls.store, { onSuccess: () => reset() });
  };

  return (
    <>
      <PageTitle title="Email Members" />
      <Container maxWidth="md" sx={{ py: 4 }}>
        <Typography sx={{ mb: 3 }}>
          Send an email to all the active members or specific groups. If you manage a group you can
          only send to that group; only admins can email everyone.
        </Typography>
        <Card>
          <CardContent>
            <NotificationEmailForm
              data={data}
              setData={setData}
              errors={errors}
              processing={processing}
              onSubmit={handleSubmit}
            >
              <TextField
                select
                label="Recipient"
                name="recipient"
                value={data.recipient}
                onChange={(e) => setData('recipient', e.target.value)}
                fullWidth
                error={!!errors.recipient}
                helperText={errors.recipient}
              >
                {recipients.map((recipient) => (
                  <MenuItem key={recipient.value} value={recipient.value}>
                    {recipient.label}
                  </MenuItem>
                ))}
              </TextField>
            </NotificationEmailForm>
          </CardContent>
        </Card>
      </Container>
    </>
  );
};

Create.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Create;
