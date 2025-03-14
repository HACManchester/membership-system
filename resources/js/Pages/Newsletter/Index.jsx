import React, { useState } from 'react';
import {
    Typography,
    Container,
    Box,
    Paper,
    Button,
    List,
    ListItem,
    Stack
} from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';

const Index = ({ activeMemberEmails, newsletterRecipientEmails }) => {
    const [buttonTexts, setButtonTexts] = useState({
        'newsletter-recipients': 'Copy to clipboard',
        'active-members': 'Copy to clipboard'
    });

    const copyToClipboard = async (contentId) => {
        let copyText = 'Copied!';

        const data = contentId === 'newsletter-recipients'
            ? newsletterRecipientEmails.join(' ')
            : activeMemberEmails.join(' ');

        try {
            await navigator.clipboard.writeText(data);
        } catch (err) {
            console.error('Failed to copy to clipboard', err);
            copyText = 'Failed to copy';
        }

        setButtonTexts(prev => ({
            ...prev,
            [contentId]: copyText
        }));

        setTimeout(() => {
            setButtonTexts(prev => ({
                ...prev,
                [contentId]: 'Copy to clipboard'
            }));
        }, 2000);
    };

    return (
        <>
            <PageTitle title="Newsletter recipients" />
            <Stack spacing={4} p={4}>
                <Paper elevation={1} sx={{ p: 3 }}>
                    <Typography variant="h5" component="h2" gutterBottom>
                        Newsletter recipients ({newsletterRecipientEmails.length} members)
                    </Typography>

                    <Typography>
                        These e-mail addresses represent members who:
                    </Typography>

                    <List>
                        <ListItem>
                            Have not opted out from newsletter emails
                        </ListItem>
                        <ListItem>
                            Are currently active members, or members who have lapsed within the last 6 months
                        </ListItem>
                    </List>

                    <Typography>
                        This list is intended to send regular news &amp; announcements relating to the space.
                    </Typography>

                    <Paper
                        variant="outlined"
                        component="pre"
                        sx={{
                            p: 2,
                            maxHeight: '200px',
                            overflow: 'auto',
                            whiteSpace: 'pre-wrap',
                            wordBreak: 'break-all',
                            fontFamily: 'monospace'
                        }}
                    >
                        <code id="newsletter-recipients">
                            {newsletterRecipientEmails.join(' ')}
                        </code>
                    </Paper>

                    <Button
                        variant="contained"
                        color="yellow"
                        onClick={() => copyToClipboard('newsletter-recipients')}
                    >
                        {buttonTexts['newsletter-recipients']}
                    </Button>
                </Paper>

                <Paper elevation={1} sx={{ p: 3 }}>
                    <Typography variant="h5" component="h2" gutterBottom>
                        Active members / legitimate interest purposes ({activeMemberEmails.length} members)
                    </Typography>

                    <Typography>
                        These e-mail addresses represent currently active members, and should be emailed for urgent matters relating to membership of the space.
                    </Typography>

                    <Paper
                        variant="outlined"
                        component="pre"
                        sx={{
                            p: 2,
                            maxHeight: '200px',
                            overflow: 'auto',
                            whiteSpace: 'pre-wrap',
                            wordBreak: 'break-all',
                            fontFamily: 'monospace'
                        }}
                    >
                        <code id="active-members">
                            {activeMemberEmails.join(' ')}
                        </code>
                    </Paper>

                    <Button
                        variant="contained"
                        color="yellow"
                        onClick={() => copyToClipboard('active-members')}
                    >
                        {buttonTexts['active-members']}
                    </Button>
                </Paper>
            </Stack>
        </>
    );
}

Index.layout = page => <MainLayout children={page} />

export default Index;