import { ComponentProps } from 'react';
import { InertiaLinkProps, Link } from '@inertiajs/react';

type Props = ComponentProps<'a'> & { href: string };

/**
 * Inertia's Link with an anchor's props, for MUI's `component` prop
 * (`<Button component={RouterLink} href="...">`), which can't type Inertia's
 * Link directly. Navigates without a full page load.
 */
const RouterLink = (props: Props) => <Link {...(props as InertiaLinkProps)} />;

export default RouterLink;
