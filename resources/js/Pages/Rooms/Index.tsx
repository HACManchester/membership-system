import React from 'react';
import {
  Typography,
  Container,
  Paper,
  Button,
  Link,
  Stack,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Chip,
} from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';
import { RoomResource } from '../../types/resources';

type Props = {
  rooms: RoomResource[];
  can?: {
    create: boolean;
  };
  urls: {
    create: string;
  };
};

const Index = ({ rooms, can = { create: false }, urls }: Props) => {
  const actionButtons = (
    <Stack direction="row" justifyContent="flex-end">
      {can.create && (
        <Link href={urls.create} underline="none">
          <Button variant="contained" color="primary">
            Add a room
          </Button>
        </Link>
      )}
    </Stack>
  );

  return (
    <>
      <PageTitle title="Rooms" actionButtons={actionButtons} />
      <Container sx={{ mt: 4, pb: 4 }}>
        <Stack spacing={3}>
          <Paper sx={{ p: 3 }}>
            <Typography>
              Rooms are the physical locations equipment is grouped by on the Tools &amp; Equipment
              listing. Add or rename them here.
            </Typography>
          </Paper>

          <TableContainer component={Paper}>
            <Table>
              <TableHead>
                <TableRow>
                  <TableCell>Name</TableCell>
                  <TableCell>Slug</TableCell>
                  <TableCell align="right">Equipment</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {rooms.map((room) => (
                  <TableRow key={room.id} hover>
                    <TableCell>
                      <Link href={room.urls.show}>{room.name}</Link>
                    </TableCell>
                    <TableCell>
                      <Typography variant="body2" color="text.secondary">
                        {room.slug}
                      </Typography>
                    </TableCell>
                    <TableCell align="right">
                      <Chip label={room.equipment_count} size="small" />
                    </TableCell>
                  </TableRow>
                ))}
                {rooms.length === 0 && (
                  <TableRow>
                    <TableCell colSpan={3}>
                      <Typography color="text.secondary">No rooms yet.</Typography>
                    </TableCell>
                  </TableRow>
                )}
              </TableBody>
            </Table>
          </TableContainer>
        </Stack>
      </Container>
    </>
  );
};

Index.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;

export default Index;
