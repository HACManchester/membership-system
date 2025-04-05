import React from "react";
import { Box, Stack, Typography } from "@mui/material";

type Props = {
    title: string;
    children?: React.ReactNode;
}

const PageTitle = ({ title, children }: Props) => {
    return (
        <Box bgcolor="yellow.main">
            <Stack
                justifyContent="end"
                py={2}
                px={4}
                minHeight={180}
            >
                <Typography variant="h4" component="h1">
                    {title}
                </Typography>
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