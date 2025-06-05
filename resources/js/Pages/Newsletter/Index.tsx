import { useState } from "react";
import {
    Typography,
    Paper,
    Button,
    List,
    ListItem,
    Stack,
    FormControlLabel,
    RadioGroup,
    Radio,
} from "@mui/material";
import MainLayout from "../../Layouts/MainLayout";
import PageTitle from "../../Components/PageTitle";

type Props = {
    activeMemberEmails: string[];
    newsletterRecipientEmails: string[];
};

type Seperator = " " | "\n" | ",";

type EmailCollectionProps = {
    emailAddresses: string[];
    separator: Seperator;
    title: string;
    children?: React.ReactNode;
};

const EmailCollection = ({
    emailAddresses,
    separator,
    title,
    children,
}: EmailCollectionProps) => {
    const [copyLabel, setCopyLabel] = useState("Copy to clipboard");

    const copyToClipboard = async () => {
        try {
            await navigator.clipboard.writeText(emailAddresses.join(separator));
            setCopyLabel("Copied!");
        } catch (err) {
            console.error("Failed to copy to clipboard", err);
            setCopyLabel("Failed to copy");
        }

        setTimeout(() => {
            setCopyLabel("Copy to clipboard");
        }, 2000);
    };

    return (
        <Paper elevation={1} sx={{ p: 3 }}>
            <Typography variant="h5" component="h2" gutterBottom>
                {title} ({emailAddresses.length})
            </Typography>

            {children}

            <Paper
                variant="outlined"
                component="pre"
                sx={{
                    p: 2,
                    maxHeight: "200px",
                    overflow: "auto",
                    whiteSpace: "pre-wrap",
                    wordBreak: "break-all",
                    fontFamily: "monospace",
                }}
            >
                <code id="newsletter-recipients">
                    {emailAddresses.join(separator)}
                </code>
            </Paper>

            <Button
                variant="contained"
                color="yellow"
                onClick={copyToClipboard}
            >
                {copyLabel}
            </Button>
        </Paper>
    );
};

const Index = ({ activeMemberEmails, newsletterRecipientEmails }: Props) => {
    const [separator, setSeparator] = useState<Seperator>(",");

    return (
        <>
            <PageTitle title="Newsletter recipients" />

            <Stack spacing={4} p={4}>
                <Paper elevation={1} sx={{ p: 3, mb: 2 }}>
                    <Typography variant="h6" component="h2" gutterBottom>
                        Choose separator
                    </Typography>
                    <RadioGroup
                        row
                        value={separator}
                        onChange={(e) =>
                            setSeparator(e.target.value as Seperator)
                        }
                    >
                        <FormControlLabel
                            value=","
                            control={<Radio />}
                            label="Comma (,)"
                        />
                        <FormControlLabel
                            value={"\n"}
                            control={<Radio />}
                            label="New line"
                        />
                        <FormControlLabel
                            value=" "
                            control={<Radio />}
                            label="Space"
                        />
                    </RadioGroup>
                </Paper>
                <EmailCollection
                    emailAddresses={newsletterRecipientEmails}
                    separator={separator}
                    title="Newsletter recipients"
                >
                    <Typography>
                        These e-mail addresses represent members who:
                    </Typography>

                    <List>
                        <ListItem>
                            Have not opted out from newsletter emails
                        </ListItem>
                        <ListItem>
                            Are currently active members, or members who have
                            lapsed within the last 6 months
                        </ListItem>
                    </List>

                    <Typography>
                        This list is intended to send regular news &amp;
                        announcements relating to the space.
                    </Typography>
                </EmailCollection>

                <EmailCollection
                    emailAddresses={activeMemberEmails}
                    separator={separator}
                    title="Active members / legitimate interest purposes"
                >
                    <Typography>
                        These e-mail addresses represent currently active
                        members, and should be emailed for urgent matters
                        relating to membership of the space.
                    </Typography>
                </EmailCollection>
            </Stack>
        </>
    );
};

Index.layout = (page: React.ReactNode) => <MainLayout children={page} />;

export default Index;
