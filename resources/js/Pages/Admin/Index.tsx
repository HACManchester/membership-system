import React from 'react';
import {
  Typography,
  Container,
  Card,
  CardContent,
  Grid2,
  List,
  ListItem,
  Link,
} from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';

type Props = {
  urls: {
    accounts: string;
    recentMembers: string;
    logs: string;
    roles: string;
    payments: string;
    subCharges: string;
    accessLockdown: string;
  };
};

const Index = ({ urls }: Props) => {
  const sections = [
    {
      title: 'Manage Members',
      links: [
        { label: 'Search, find, view accounts', href: urls.accounts },
        { label: 'Recent Members', href: urls.recentMembers },
      ],
    },
    {
      title: 'View Logs',
      links: [{ label: "See what's been going on", href: urls.logs }],
    },
    {
      title: 'Manage Roles & Teams',
      links: [{ label: 'Move people in and out of roles', href: urls.roles }],
    },
    {
      title: 'Payments',
      links: [
        { label: 'All payments', href: urls.payments },
        { label: 'Subscription Charges', href: urls.subCharges },
      ],
    },
    {
      title: 'Space Access Lockdown',
      links: [{ label: 'Shut the space to general membership', href: urls.accessLockdown }],
    },
  ];

  return (
    <>
      <PageTitle title="Admin Area" />
      <Container sx={{ mt: 4, pb: 4 }}>
        <Grid2 container spacing={3}>
          {sections.map((section) => (
            <Grid2 key={section.title} size={{ xs: 12, md: 6 }}>
              <Card sx={{ height: '100%' }}>
                <CardContent>
                  <Typography variant="h6" component="h2" gutterBottom>
                    {section.title}
                  </Typography>
                  <List dense>
                    {section.links.map((link) => (
                      <ListItem key={link.href} disableGutters>
                        <Link href={link.href}>{link.label}</Link>
                      </ListItem>
                    ))}
                  </List>
                </CardContent>
              </Card>
            </Grid2>
          ))}
        </Grid2>
      </Container>
    </>
  );
};

Index.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Index;
