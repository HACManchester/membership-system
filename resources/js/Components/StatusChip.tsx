import { Chip, ChipProps, Tooltip } from '@mui/material';

export type MemberStatus =
  | 'setting-up'
  | 'active'
  | 'payment-warning'
  | 'leaving'
  | 'on-hold'
  | 'left'
  | 'honorary'
  | 'suspended';

const statuses: Record<
  MemberStatus,
  { label: string; color: ChipProps['color']; description?: string }
> = {
  'setting-up': {
    label: 'Setting Up',
    color: 'warning',
    description: 'We are waiting for a subscription to be setup',
  },
  active: { label: 'Active', color: 'success' },
  'payment-warning': {
    label: 'Payment Warning',
    color: 'error',
    description: 'There is something wrong with your subscription',
  },
  leaving: {
    label: 'Leaving',
    color: 'default',
    description: 'Your leaving and will loose access when your payment expires',
  },
  'on-hold': { label: 'On Hold', color: 'default' },
  left: { label: 'Left', color: 'default' },
  honorary: { label: 'Honorary', color: 'default' },
  suspended: {
    label: 'Suspended',
    color: 'default',
    description:
      'Your payment has failed, please make a manual payment to reactivate your account.',
  },
};

type Props = {
  status: MemberStatus;
};

/**
 * A member's status as a chip, with a tooltip explaining the statuses that need
 * one. The Inertia counterpart of the `status-label` Blade partial; it shows
 * nothing for a status it doesn't know, as the partial does.
 */
const StatusChip = ({ status }: Props) => {
  const config = statuses[status];

  if (!config) {
    return null;
  }

  const chip = <Chip label={config.label} color={config.color} size="small" />;

  return config.description ? <Tooltip title={config.description}>{chip}</Tooltip> : chip;
};

export default StatusChip;
