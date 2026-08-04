import React from 'react';
import { Typography, Container, Paper, Button, Link, Stack } from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';
import EquipmentTable from '../../Components/Equipment/EquipmentTable';
import { useEquipmentGrouping } from './hooks/useEquipmentGrouping';
import { EquipmentListResource } from '../../types/resources';

type Props = {
  equipment: EquipmentListResource[];
  can?: {
    create: boolean;
  };
  urls: {
    create: string;
  };
};

const Index = ({ equipment, can = { create: false }, urls }: Props) => {
  const groups = useEquipmentGrouping(equipment);

  const actionButtons = (
    <Stack direction="row" spacing={1} justifyContent="flex-end">
      {can.create && (
        <Link href={urls.create} underline="none">
          <Button variant="contained" color="primary">
            Record a new item
          </Button>
        </Link>
      )}
    </Stack>
  );

  return (
    <>
      <PageTitle title="Tools & Equipment" actionButtons={actionButtons} />
      <Container sx={{ mt: 4, pb: 4 }}>
        <Stack spacing={4}>
          <Paper sx={{ p: 3 }}>
            <Typography variant="h6" component="h2" gutterBottom>
              View tools, manuals, and book inductions
            </Typography>
            <Typography color="text.secondary">
              If any information on our equipment pages is out-of-date or needs updating, please
              contact the relevant <Link href="/equipment_area">area coordinator</Link>.
            </Typography>
          </Paper>

          {groups.map((group) => (
            <Stack key={group.room} spacing={2}>
              <Typography variant="h5" component="h2">
                {group.room}
              </Typography>
              <EquipmentTable equipment={group.equipment} />
            </Stack>
          ))}

          {groups.length === 0 && (
            <Typography color="text.secondary">No equipment recorded yet.</Typography>
          )}
        </Stack>
      </Container>
    </>
  );
};

Index.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Index;
