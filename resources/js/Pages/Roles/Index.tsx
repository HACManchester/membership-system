import React from 'react';
import {
  Typography,
  Container,
  Paper,
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
import { RoleResource } from '../../types/resources';

type Props = {
  roles: RoleResource[];
};

const Index = ({ roles }: Props) => {
  return (
    <>
      <PageTitle title="Roles & Teams" />
      <Container sx={{ mt: 4, pb: 4 }}>
        <Stack spacing={3}>
          <Paper sx={{ p: 3 }}>
            <Typography>
              Roles grant access and group members into teams. Edit a role to change its details or
              add and remove members.
            </Typography>
          </Paper>

          <TableContainer component={Paper}>
            <Table>
              <TableHead>
                <TableRow>
                  <TableCell>Role</TableCell>
                  <TableCell>Key</TableCell>
                  <TableCell align="right">Members</TableCell>
                </TableRow>
              </TableHead>
              <TableBody>
                {roles.map((role) => (
                  <TableRow key={role.id} hover>
                    <TableCell>
                      <Link href={role.urls.edit}>{role.title || role.name}</Link>
                    </TableCell>
                    <TableCell>
                      <Chip label={role.name} size="small" variant="outlined" />
                    </TableCell>
                    <TableCell align="right">
                      <Chip label={role.member_count} size="small" />
                    </TableCell>
                  </TableRow>
                ))}
                {roles.length === 0 && (
                  <TableRow>
                    <TableCell colSpan={3}>
                      <Typography color="text.secondary">No roles.</Typography>
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
