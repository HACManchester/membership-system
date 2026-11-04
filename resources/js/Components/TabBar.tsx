import { Box, Stack } from '@mui/material';
import RouterLink from './RouterLink';

export type TabBarTab = {
  label: string;
  href: string;
  active?: boolean;
};

type Props = {
  tabs: TabBarTab[];
  'aria-label': string;
};

/**
 * Links between related pages, styled as tabs with the current one underlined;
 * the Inertia counterpart of the Blade `#mainTabBar`. They're plain links in a
 * nav, not ARIA tabs, since each goes to another page. The controller says
 * which is current (it knows the route), and the bar goes in PageTitle's
 * children.
 */
const TabBar = ({ tabs, 'aria-label': ariaLabel }: Props) => {
  return (
    <Box component="nav" aria-label={ariaLabel} sx={{ overflowX: 'auto' }}>
      <Stack component="ul" direction="row" sx={{ m: 0, p: 0, listStyle: 'none' }}>
        {tabs.map((tab) => (
          <li key={tab.href}>
            <Box
              component={RouterLink}
              href={tab.href}
              aria-current={tab.active ? 'page' : undefined}
              sx={{
                display: 'block',
                px: 2,
                py: 1.5,
                color: 'common.black',
                fontWeight: 500,
                fontSize: '0.875rem',
                textTransform: 'uppercase',
                textDecoration: 'none',
                whiteSpace: 'nowrap',
                borderBottom: 4,
                borderColor: tab.active ? 'common.black' : 'transparent',
                '&:hover, &:focus-visible': { borderColor: 'common.black' },
              }}
            >
              {tab.label}
            </Box>
          </li>
        ))}
      </Stack>
    </Box>
  );
};

export default TabBar;
