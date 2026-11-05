import React from 'react';
import { Alert, Button, Checkbox, FormControlLabel, Stack, TextField } from '@mui/material';
import SendIcon from '@mui/icons-material/Send';

export type NotificationEmailFormData = {
  recipient: string;
  subject: string;
  message: string;
  send_to_all: boolean;
};

type Props = {
  data: NotificationEmailFormData;
  setData: <K extends keyof NotificationEmailFormData>(
    key: K,
    value: NotificationEmailFormData[K]
  ) => void;
  errors: Partial<Record<keyof NotificationEmailFormData, string>>;
  processing: boolean;
  onSubmit: (e: React.FormEvent) => void;
  /** The recipient field, or a description of who the email goes to. */
  children: React.ReactNode;
};

/**
 * The subject, message and send button shared by the mass email and the
 * equipment email forms. Unless "send to everyone" is ticked, the email goes
 * only to the sender, so they can check it first; the button says which.
 */
const NotificationEmailForm = ({
  data,
  setData,
  errors,
  processing,
  onSubmit,
  children,
}: Props) => (
  <form onSubmit={onSubmit}>
    <Stack spacing={3}>
      {children}

      <TextField
        label="Subject"
        name="subject"
        value={data.subject}
        onChange={(e) => setData('subject', e.target.value)}
        fullWidth
        required
        error={!!errors.subject}
        helperText={errors.subject}
      />

      <TextField
        label="Message"
        name="message"
        value={data.message}
        onChange={(e) => setData('message', e.target.value)}
        fullWidth
        required
        multiline
        minRows={6}
        error={!!errors.message}
        helperText={
          errors.message ||
          'The email is addressed to each member (e.g. "Hi Sam") and ends with the standard signature; your message goes in between.'
        }
      />

      <Alert severity="warning">
        The email is sent as soon as you click send, and this form doesn&apos;t save drafts: keep a
        copy of anything long elsewhere.
      </Alert>

      <FormControlLabel
        control={
          <Checkbox
            name="send_to_all"
            checked={data.send_to_all}
            onChange={(e) => setData('send_to_all', e.target.checked)}
          />
        }
        label="Send the message to everyone, not just yourself"
      />

      <div>
        <Button
          type="submit"
          variant="contained"
          color="primary"
          size="large"
          startIcon={<SendIcon />}
          disabled={processing}
        >
          {data.send_to_all ? 'Send to everyone' : 'Send a test to yourself'}
        </Button>
      </div>
    </Stack>
  </form>
);

export default NotificationEmailForm;
