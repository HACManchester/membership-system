import React from "react";
import { Box, Stack, Typography } from "@mui/material";

type Props = {
    title: string;
    actionButtons?: React.ReactNode;
    children?: React.ReactNode;
}

const PageTitle = ({ title, actionButtons, children }: Props) => {
    return (
        <Box bgcolor="yellow.main">
            <Stack
                direction="row"
                justifyContent="space-between"
                alignItems="end"
                py={8}
                pb={2}
                px={4}
            >
                <Typography variant="h4" component="h1">
                    {title}
                </Typography>
                {actionButtons && (
                    <Box className="action-buttons">
                        {actionButtons}
                    </Box>
                )}
            </Stack>
            {children && (
                <Stack
                    direction="row"
                    bgcolor="yellow.darker"
                    borderTop="1px solid black"
                    py={1}
                    px={4}
                >
                    {children}
                </Stack>
            )}
        </Box>
    );
}

export default PageTitle;