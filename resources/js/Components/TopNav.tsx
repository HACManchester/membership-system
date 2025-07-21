import {
    IconButton,
    Stack,
    Link,
    Paper,
    useMediaQuery,
    Theme,
} from "@mui/material";
import MenuIcon from "@mui/icons-material/Menu";

type AuthUser = {
    name: string;
    account_path: string;
};

type Auth = {
    user: AuthUser;
};

type TopNavProps = {
    handleDrawerToggle: () => void;
    auth: Auth | null;
};

export default function TopNav({ handleDrawerToggle, auth }: TopNavProps) {
    const isMobile = useMediaQuery((theme: Theme) =>
        theme.breakpoints.down("md")
    );

    return (
        <Paper
            square
            elevation={0}
            sx={{
                borderBottom: "1px solid black",
                zIndex: (theme) => theme.zIndex.drawer + 1,
            }}
        >
            <Stack
                direction="row"
                justifyContent="space-between"
                alignItems="center"
                spacing={2}
                p={2}
            >
                <Stack
                    direction="row"
                    spacing={isMobile ? 1 : 2}
                    alignItems="center"
                    sx={{ overflow: "hidden" }}
                >
                    {isMobile && (
                        <IconButton
                            color="inherit"
                            aria-label="open drawer"
                            edge="start"
                            onClick={handleDrawerToggle}
                            sx={{ mr: 0 }}
                        >
                            <MenuIcon />
                        </IconButton>
                    )}

                    <Link
                        href="https://hacman.org.uk"
                        target="_blank"
                        rel="noopener"
                        sx={{
                            whiteSpace: "nowrap",
                            overflow: "hidden",
                            textOverflow: "ellipsis",
                            minWidth: 0,
                        }}
                    >
                        Website
                    </Link>
                    <Link
                        href="https://list.hacman.org.uk"
                        target="_blank"
                        rel="noopener"
                        sx={{
                            whiteSpace: "nowrap",
                            overflow: "hidden",
                            textOverflow: "ellipsis",
                            minWidth: 0,
                        }}
                    >
                        Forum
                    </Link>
                    <Link
                        href="https://docs.hacman.org.uk"
                        target="_blank"
                        rel="noopener"
                        sx={{
                            whiteSpace: "nowrap",
                            overflow: "hidden",
                            textOverflow: "ellipsis",
                            minWidth: 0,
                        }}
                    >
                        Documentation
                    </Link>
                </Stack>

                <Stack direction={isMobile ? "column" : "row"} spacing={isMobile ? 0 : 2} alignItems="center" justifyContent="center">
                    {auth ? (
                        <>
                            <Link href={auth.user.account_path}>
                                {auth.user.name}
                            </Link>
                            <Link href="/logout" fontSize="small">Logout</Link>
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
