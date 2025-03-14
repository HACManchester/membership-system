import React from 'react';
import {
    Box,
    Drawer,
    List,
    ListItemButton,
    ListItemText,
    Badge,
    ListItemSecondaryAction,
    Stack,
    Link,
    Divider,
    Toolbar,
} from '@mui/material';
import { Link as InertiaLink, usePage } from '@inertiajs/react';

export default function SideNav({ mobileOpen, isClosing, handleDrawerClose, handleDrawerTransitionEnd, drawerWidth }) {
    const { navRoutes } = usePage().props;

    const drawerContent = (
        <>
            <Toolbar sx={{
                display: {
                    xs: 'block',
                    md: 'none'
                },
            }} />
            <Stack divider={<Divider />}>
                <Stack p={4} as={Link} href="/">
                    <img src="/img/logo-new.png" alt="Hackspace Manchester" height="100" style={{ margin: '0 auto' }} />
                </Stack>
                {navRoutes.map((groupedNavItems, i) => (
                    <List key={i}>
                        {groupedNavItems.map((navEntry, index) => {
                            const { label, href, active, highlight, badge, external } = navEntry;
                            return (
                                <ListItemButton
                                    key={index}
                                    href={href}
                                    // todo: Currently no other website links are inertia links
                                    // component={external ? Link : InertiaLink}
                                    selected={active}
                                    sx={{ color: highlight ? 'primary.main' : 'inherit' }}
                                    target={external ? '_blank' : '_self'}
                                >
                                    <ListItemText>
                                        {label}
                                    </ListItemText>
                                    {badge && (
                                        <ListItemSecondaryAction>
                                            <Badge badgeContent={badge} color="error" />
                                        </ListItemSecondaryAction>
                                    )}
                                </ListItemButton>
                            )
                        })}
                    </List>
                ))}
            </Stack>
        </>
    );

    return (
        <Box
            component="nav"
            sx={{
                width: { md: drawerWidth },
                flexShrink: { md: 0 },
            }}
            aria-label="navigation"
        >
            {/* Mobile drawer */}
            <Drawer
                variant="temporary"
                open={mobileOpen}
                onTransitionEnd={handleDrawerTransitionEnd}
                onClose={handleDrawerClose}
                sx={{
                    display: {
                        xs: 'block',
                        md: 'none'
                    },
                    '& .MuiDrawer-paper': {
                        boxSizing: 'border-box',
                        width: drawerWidth,
                        borderRadius: 0,
                    },
                }}
                slotProps={{
                    root: {
                        keepMounted: true, // Better open performance on mobile.
                    },
                }}
            >
                {drawerContent}
            </Drawer>

            {/* Desktop drawer */}
            <Drawer
                variant="permanent"
                sx={{
                    display: {
                        xs: 'none',
                        md: 'block'
                    },
                    height: '100%',
                    '& .MuiDrawer-paper': {
                        boxSizing: 'border-box',
                        width: drawerWidth,
                        borderRadius: 0,
                        position: 'relative',
                        height: '100%',
                    },
                }}
                open
            >
                {drawerContent}
            </Drawer>
        </Box>
    );
}