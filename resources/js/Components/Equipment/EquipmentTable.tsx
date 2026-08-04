import {
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  Chip,
  Stack,
  Link,
  Button,
} from '@mui/material';
import { EquipmentListResource } from '../../types/resources';

type Props = {
  equipment: EquipmentListResource[];
};

const EquipmentTable = ({ equipment }: Props) => {
  return (
    <TableContainer component={Paper}>
      <Table size="small">
        <TableHead>
          <TableRow>
            <TableCell>Name</TableCell>
            <TableCell>Status</TableCell>
            <TableCell>Dangerous</TableCell>
            <TableCell>Lone working</TableCell>
            <TableCell />
          </TableRow>
        </TableHead>
        <TableBody>
          {equipment.map((item) => (
            <TableRow key={item.id} hover>
              <TableCell>
                <Link href={item.urls.show}>{item.name}</Link>
              </TableCell>
              <TableCell>
                <Stack direction="row" spacing={1} useFlexGap flexWrap="wrap">
                  {item.requires_induction && (
                    <Chip label="Induction required" color="info" size="small" />
                  )}
                  {item.requires_induction && !item.accepting_inductions && (
                    <Chip label="Inductions paused" color="warning" size="small" />
                  )}
                  {!item.working && <Chip label="Out of action" color="error" size="small" />}
                  {item.permaloan && <Chip label="Permaloan" color="warning" size="small" />}
                  {item.access_code && (
                    <Chip label={`🔑 ${item.access_code}`} color="success" size="small" />
                  )}
                </Stack>
              </TableCell>
              <TableCell>{item.dangerous ? '⚠️' : ''}</TableCell>
              <TableCell>
                {!item.lone_working && (
                  <Chip label="No lone working" color="error" variant="outlined" size="small" />
                )}
              </TableCell>
              <TableCell align="right">
                {item.can.update && (
                  <Button size="small" href={item.urls.edit}>
                    Edit
                  </Button>
                )}
              </TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
    </TableContainer>
  );
};

export default EquipmentTable;
