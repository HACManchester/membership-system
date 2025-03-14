import React from 'react';
import { usePage } from '@inertiajs/react';
import {
    Box,
    Stack
} from '@mui/material';
import SideNav from '../Components/SideNav';
import TopNav from '../Components/TopNav';

const drawerWidth = 240;

export default function MainLayout({ children }) {
    const { auth } = usePage().props;

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
            <TopNav
                handleDrawerToggle={handleDrawerToggle}
                auth={auth}
            />
            <Stack direction="row" minHeight="100vh" flexGrow>
                <SideNav
                    drawerWidth={drawerWidth}
                    mobileOpen={mobileOpen}
                    isClosing={isClosing}
                    handleDrawerClose={handleDrawerClose}
                    handleDrawerTransitionEnd={handleDrawerTransitionEnd}
                />

                <Box component="main" sx={{ flexGrow: 1 }}>
                    {children}
                </Box>
            </Stack>
        </Stack >
    );
}