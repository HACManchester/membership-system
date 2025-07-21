import React from "react";
import { usePage } from "@inertiajs/react";
import { Box, Stack } from "@mui/material";
import SideNav from "../Components/SideNav";
import TopNav from "../Components/TopNav";

const drawerWidth = 240;

type MainLayoutProps = {
    children: React.ReactNode;
};

type PageProps = {
    auth: {
        user: {
            name: string;
            account_path: string;
        };
    } | null;
};

export default function MainLayout({ children }: MainLayoutProps) {
    const { auth } = usePage<PageProps>().props;

    const [mobileOpen, setMobileOpen] = React.useState(false);
    const [isClosing, setIsClosing] = React.useState(false);

    const handleDrawerClose = () => {
        setIsClosing(true);
        setMobileOpen(false);
    };

    const handleDrawerTransitionEnd = () => {
        setIsClosing(false);
    };

    const handleDrawerToggle = () => {
        if (!isClosing) {
            setMobileOpen(!mobileOpen);
        }
    };

    // Mui's Responsive Drawer pattern
    return (
        <Stack direction="column" minHeight="100vh">
            <TopNav handleDrawerToggle={handleDrawerToggle} auth={auth} />
            <Stack direction="row" minHeight="100vh" flexGrow={1}>
                <SideNav
                    drawerWidth={drawerWidth}
                    mobileOpen={mobileOpen}
                    isClosing={isClosing}
                    handleDrawerClose={handleDrawerClose}
                    handleDrawerTransitionEnd={handleDrawerTransitionEnd}
                />

                <Box
                    component="main"
                    width="100%"
                    sx={{
                        flexGrow: 1,
                        backgroundImage:
                            "linear-gradient(to bottom, #eef2f3, #8e9eab)",
                        backgroundAttachment: "fixed",
                        backgroundPosition: "0 16em",
                        backgroundRepeat: "no-repeat",
                        backgroundColor: "#eef2f3",
                    }}
                >
                    {children}
                </Box>
            </Stack>
        </Stack>
    );
}
