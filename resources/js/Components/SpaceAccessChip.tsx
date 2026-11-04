import { Chip, Tooltip } from '@mui/material';

type Props = {
  active: boolean;
};

/**
 * Whether a member may use the space (`User::$active`), as a chip. The Inertia
 * counterpart of the `space-access-label` Blade partial.
 */
const SpaceAccessChip = ({ active }: Props) => {
  return active ? (
    <Tooltip title="You are allowed to use the space">
      <Chip label="Access to the space" color="success" size="small" />
    </Tooltip>
  ) : (
    <Tooltip title="You do not have permission to use the space">
      <Chip label="No access to the space" color="error" size="small" />
    </Tooltip>
  );
};

export default SpaceAccessChip;
