import {
    IconButton,
    Stack,
    Link,
    Paper,
    useMediaQuery,
    Theme
} from '@mui/material';
import MenuIcon from '@mui/icons-material/Menu';

type AuthUser = {
    name: string;
    account_path: string;
}

type Auth = {
    user: AuthUser;
}

type TopNavProps = {
    handleDrawerToggle: () => void;
    auth: Auth | null;
}

export default function TopNav({ handleDrawerToggle, auth }: TopNavProps) {
    const isMobile = useMediaQuery((theme: Theme) => theme.breakpoints.down('md'));

    return (
        <Paper
            square
            elevation={0}
            sx={{
                borderBottom: '1px solid black',
                zIndex: (theme) => theme.zIndex.drawer + 1
            }}
        >
            <Stack
                direction="row"
                justifyContent="space-between"
                alignItems="center"
                spacing={2}
                p={2}
            >
                <Stack direction="row" spacing={2} alignItems="center">
                    {isMobile && (
                        <IconButton
                            color="inherit"
                            aria-label="open drawer"
                            edge="start"
                            onClick={handleDrawerToggle}
                        >
                            <MenuIcon />
                        </IconButton>
                    )}

                    <Link href="https://hacman.org.uk" target="_blank" rel="noopener">Website</Link>
                    <Link href="https://list.hacman.org.uk" target="_blank" rel="noopener">Forum</Link>
                    <Link href="https://docs.hacman.org.uk" target="_blank" rel="noopener">Documentation</Link>
                </Stack>

                <Stack direction="row" spacing={2} alignItems="center">
                    {auth ? (
                        <>
                            <Link href={auth.user.account_path}>
                                {auth.user.name}
                            </Link>
                            <Link href="/logout">🔑 Logout</Link>
                        </>
                    ) : (
                        <>
                            <Link href="/login">🔑 Login</Link>
                            <Link href="/register">✔️ Become a Member</Link>
                        </>
                    )}
                </Stack>
            </Stack>
        </Paper>
    );
}