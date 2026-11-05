import React from 'react';
import {
  Alert,
  Card,
  CardContent,
  Container,
  List,
  ListItemButton,
  ListItemText,
  Stack,
  Typography,
} from '@mui/material';
import OpenInNewIcon from '@mui/icons-material/OpenInNew';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';

type PreviewLink = {
  name: string;
  url: string;
};

type Props = {
  emails: PreviewLink[];
  notifications: PreviewLink[];
};

const PreviewList = ({ title, links }: { title: string; links: PreviewLink[] }) => (
  <Card>
    <CardContent>
      <Typography variant="h6" component="h2" gutterBottom>
        {title}
      </Typography>
      <List disablePadding>
        {links.map((link) => (
          // The previews are the rendered messages, not app pages, so they open in full in a new tab
          <ListItemButton
            key={link.url}
            component="a"
            href={link.url}
            target="_blank"
            rel="noopener"
          >
            <ListItemText primary={link.name} />
            <OpenInNewIcon fontSize="small" sx={{ color: 'text.secondary' }} />
          </ListItemButton>
        ))}
      </List>
    </CardContent>
  </Card>
);

const Index = ({ emails, notifications }: Props) => {
  return (
    <>
      <PageTitle title="Email & Notification Previews" />
      <Container sx={{ py: 4 }}>
        <Stack spacing={3}>
          <Typography>
            Preview the email templates and notifications the system sends. The previews use dummy
            data to show how messages will appear to recipients.
          </Typography>
          <PreviewList title="Email templates" links={emails} />
          <PreviewList title="Notification templates" links={notifications} />
          <Alert severity="info">
            The previews open in a new tab and show the content that would be sent to members.
          </Alert>
        </Stack>
      </Container>
    </>
  );
};

Index.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Index;
