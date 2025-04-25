import React, { useState } from 'react';
import {
    Typography,
    Container,
    Box,
    Paper,
    Button,
    TextField,
    IconButton,
    Stack,
    Divider,
    Grid,
    Card,
    CardContent,
    CardActions,
    CardHeader,
    FormControlLabel,
    Switch,
    Checkbox,
    Radio,
    RadioGroup,
    FormControl,
    FormLabel,
    Select,
    MenuItem,
    InputLabel,
    Slider,
    Alert,
    AlertTitle,
    Chip,
    Avatar,
    List,
    ListItem,
    ListItemText,
    ListItemIcon,
    ListItemButton,
    Tabs,
    Tab,
    Accordion,
    AccordionSummary,
    AccordionDetails,
    Badge,
    Tooltip,
    SelectChangeEvent
} from '@mui/material';
import MainLayout from '../../Layouts/MainLayout';
import PageTitle from '../../Components/PageTitle';

// Icons
import DeleteIcon from '@mui/icons-material/Delete';
import SendIcon from '@mui/icons-material/Send';
import HomeIcon from '@mui/icons-material/Home';
import PersonIcon from '@mui/icons-material/Person';
import ExpandMoreIcon from '@mui/icons-material/ExpandMore';
import SettingsIcon from '@mui/icons-material/Settings';
import NotificationsIcon from '@mui/icons-material/Notifications';

type TabPanelProps = {
    children?: React.ReactNode;
    value: number;
    index: number;
}

function TabPanel(props: TabPanelProps) {
    const { children, value, index, ...other } = props;

    return (
        <div
            role="tabpanel"
            hidden={value !== index}
            id={`simple-tabpanel-${index}`}
            aria-labelledby={`simple-tab-${index}`}
            {...other}
        >
            {value === index && (
                <Box sx={{ p: 3 }}>
                    <Typography>{children}</Typography>
                </Box>
            )}
        </div>
    );
}

const ComponentZoo = () => {
    // State for interactive components
    const [checked, setChecked] = useState(true);
    const [radioValue, setRadioValue] = useState('female');
    const [selectValue, setSelectValue] = useState('');
    const [sliderValue, setSliderValue] = useState(30);
    const [tabValue, setTabValue] = useState(0);
    const [expanded, setExpanded] = useState<string | false>(false);

    const handleCheckboxChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        setChecked(event.target.checked);
    };

    const handleRadioChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        setRadioValue(event.target.value);
    };

    const handleSelectChange = (event: SelectChangeEvent) => {
        setSelectValue(event.target.value);
    };

    const handleSliderChange = (event: Event, newValue: number | number[]) => {
        setSliderValue(newValue as number);
    };

    const handleTabChange = (event: React.SyntheticEvent, newValue: number) => {
        setTabValue(newValue);
    };

    const handleAccordionChange = (panel: string) => (event: React.SyntheticEvent, isExpanded: boolean) => {
        setExpanded(isExpanded ? panel : false);
    };

    return (
        <>
            <PageTitle title="Component Zoo" />
            <Container maxWidth="lg" sx={{ my: 4 }}>
                <Typography variant="h4" component="h2" gutterBottom>
                    MUI Component Showcase
                </Typography>
                <Typography paragraph>
                    This page showcases various Material UI components with your theme applied. Use this to preview how theme changes will affect your UI.
                </Typography>

                <Grid container spacing={4}>
                    {/* TYPOGRAPHY */}
                    <Grid item xs={12}>
                        <Paper sx={{ p: 3, mb: 4 }}>
                            <Typography variant="h4" gutterBottom>Typography</Typography>
                            <Divider sx={{ mb: 2 }} />
                            <Typography variant="h1" gutterBottom>h1. Heading</Typography>
                            <Typography variant="h2" gutterBottom>h2. Heading</Typography>
                            <Typography variant="h3" gutterBottom>h3. Heading</Typography>
                            <Typography variant="h4" gutterBottom>h4. Heading</Typography>
                            <Typography variant="h5" gutterBottom>h5. Heading</Typography>
                            <Typography variant="h6" gutterBottom>h6. Heading</Typography>
                            <Typography variant="subtitle1" gutterBottom>subtitle1. Lorem ipsum dolor sit amet, consectetur adipisicing elit.</Typography>
                            <Typography variant="subtitle2" gutterBottom>subtitle2. Lorem ipsum dolor sit amet, consectetur adipisicing elit.</Typography>
                            <Typography variant="body1" gutterBottom>body1. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quos blanditiis tenetur unde suscipit, quam beatae rerum inventore consectetur, neque doloribus.</Typography>
                            <Typography variant="body2" gutterBottom>body2. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Quos blanditiis tenetur unde suscipit, quam beatae rerum inventore consectetur, neque doloribus.</Typography>
                            <Typography variant="button" display="block" gutterBottom>button text</Typography>
                            <Typography variant="caption" display="block" gutterBottom>caption text</Typography>
                            <Typography variant="overline" display="block" gutterBottom>overline text</Typography>
                        </Paper>
                    </Grid>

                    {/* BUTTONS */}
                    <Grid item xs={12} md={6}>
                        <Paper sx={{ p: 3, mb: 4 }}>
                            <Typography variant="h4" gutterBottom>Buttons</Typography>
                            <Divider sx={{ mb: 2 }} />

                            <Box sx={{ mb: 2 }}>
                                <Typography variant="h6" gutterBottom>Contained Buttons</Typography>
                                <Stack direction="row" spacing={2} sx={{ mb: 2 }}>
                                    <Button variant="contained">Default</Button>
                                    <Button variant="contained" color="primary">Primary</Button>
                                    <Button variant="contained" color="secondary">Secondary</Button>
                                </Stack>
                                <Stack direction="row" spacing={2}>
                                    <Button variant="contained" color="error">Error</Button>
                                    <Button variant="contained" color="warning">Warning</Button>
                                    <Button variant="contained" color="info">Info</Button>
                                    <Button variant="contained" color="success">Success</Button>
                                </Stack>
                            </Box>

                            <Box sx={{ mb: 2 }}>
                                <Typography variant="h6" gutterBottom>Outlined Buttons</Typography>
                                <Stack direction="row" spacing={2} sx={{ mb: 2 }}>
                                    <Button variant="outlined">Default</Button>
                                    <Button variant="outlined" color="primary">Primary</Button>
                                    <Button variant="outlined" color="secondary">Secondary</Button>
                                </Stack>
                                <Stack direction="row" spacing={2}>
                                    <Button variant="outlined" color="error">Error</Button>
                                    <Button variant="outlined" color="warning">Warning</Button>
                                    <Button variant="outlined" color="info">Info</Button>
                                    <Button variant="outlined" color="success">Success</Button>
                                </Stack>
                            </Box>

                            <Box sx={{ mb: 2 }}>
                                <Typography variant="h6" gutterBottom>Text Buttons</Typography>
                                <Stack direction="row" spacing={2} sx={{ mb: 2 }}>
                                    <Button>Default</Button>
                                    <Button color="primary">Primary</Button>
                                    <Button color="secondary">Secondary</Button>
                                </Stack>
                                <Stack direction="row" spacing={2}>
                                    <Button color="error">Error</Button>
                                    <Button color="warning">Warning</Button>
                                    <Button color="info">Info</Button>
                                    <Button color="success">Success</Button>
                                </Stack>
                            </Box>

                            <Box>
                                <Typography variant="h6" gutterBottom>Button with Icons</Typography>
                                <Stack direction="row" spacing={2}>
                                    <Button variant="contained" startIcon={<DeleteIcon />}>Delete</Button>
                                    <Button variant="contained" endIcon={<SendIcon />}>Send</Button>
                                    <IconButton aria-label="delete" color="primary">
                                        <DeleteIcon />
                                    </IconButton>
                                    <IconButton aria-label="settings" color="secondary">
                                        <SettingsIcon />
                                    </IconButton>
                                </Stack>
                            </Box>
                        </Paper>
                    </Grid>

                    {/* FORM INPUTS */}
                    <Grid item xs={12} md={6}>
                        <Paper sx={{ p: 3, mb: 4 }}>
                            <Typography variant="h4" gutterBottom>Form Inputs</Typography>
                            <Divider sx={{ mb: 2 }} />

                            <Stack spacing={3}>
                                <TextField label="Standard" variant="standard" />
                                <TextField label="Outlined" variant="outlined" />
                                <TextField label="Filled" variant="filled" />
                                <TextField
                                    label="Multiline"
                                    multiline
                                    rows={2}
                                    placeholder="Type something here..."
                                />
                                <TextField
                                    label="With helper text"
                                    helperText="Some helper text"
                                />
                                <TextField
                                    error
                                    label="Error"
                                    defaultValue="Invalid input"
                                    helperText="Error message"
                                />

                                <FormControl variant="outlined" fullWidth>
                                    <InputLabel id="select-label">Age</InputLabel>
                                    <Select
                                        labelId="select-label"
                                        value={selectValue}
                                        onChange={handleSelectChange}
                                        label="Age"
                                    >
                                        <MenuItem value=""><em>None</em></MenuItem>
                                        <MenuItem value={10}>Ten</MenuItem>
                                        <MenuItem value={20}>Twenty</MenuItem>
                                        <MenuItem value={30}>Thirty</MenuItem>
                                    </Select>
                                </FormControl>

                                <FormControlLabel
                                    control={<Switch checked={checked} onChange={handleCheckboxChange} />}
                                    label="Switch"
                                />

                                <FormControlLabel
                                    control={<Checkbox checked={checked} onChange={handleCheckboxChange} />}
                                    label="Checkbox"
                                />

                                <FormControl component="fieldset">
                                    <FormLabel component="legend">Gender</FormLabel>
                                    <RadioGroup
                                        aria-label="gender"
                                        name="gender1"
                                        value={radioValue}
                                        onChange={handleRadioChange}
                                    >
                                        <FormControlLabel value="female" control={<Radio />} label="Female" />
                                        <FormControlLabel value="male" control={<Radio />} label="Male" />
                                        <FormControlLabel value="other" control={<Radio />} label="Other" />
                                    </RadioGroup>
                                </FormControl>

                                <Box>
                                    <Typography gutterBottom>Slider</Typography>
                                    <Slider
                                        value={sliderValue}
                                        onChange={handleSliderChange}
                                        aria-labelledby="continuous-slider"
                                        valueLabelDisplay="auto"
                                    />
                                </Box>
                            </Stack>
                        </Paper>
                    </Grid>

                    {/* CARDS */}
                    <Grid item xs={12} md={6}>
                        <Paper sx={{ p: 3, mb: 4 }}>
                            <Typography variant="h4" gutterBottom>Cards</Typography>
                            <Divider sx={{ mb: 2 }} />

                            <Grid container spacing={3}>
                                <Grid item xs={12} md={6}>
                                    <Card>
                                        <CardHeader
                                            avatar={
                                                <Avatar sx={{ bgcolor: 'red' }} aria-label="recipe">
                                                    R
                                                </Avatar>
                                            }
                                            title="Card Title"
                                            subheader="September 14, 2023"
                                        />
                                        <CardContent>
                                            <Typography variant="body2" color="text.secondary">
                                                This is a sample card with some content. Cards can be used for various UI components like dashboard widgets, product displays, etc.
                                            </Typography>
                                        </CardContent>
                                        <CardActions>
                                            <Button size="small">Action 1</Button>
                                            <Button size="small">Action 2</Button>
                                        </CardActions>
                                    </Card>
                                </Grid>

                                <Grid item xs={12} md={6}>
                                    <Card>
                                        <CardContent>
                                            <Typography variant="h5" component="div">
                                                Simple Card
                                            </Typography>
                                            <Typography sx={{ mb: 1.5 }} color="text.secondary">
                                                No header
                                            </Typography>
                                            <Typography variant="body2">
                                                A simpler card variation without a header.
                                                <br />
                                                {'"Can be used for simpler content"'}
                                            </Typography>
                                        </CardContent>
                                        <CardActions>
                                            <Button size="small">Learn More</Button>
                                        </CardActions>
                                    </Card>
                                </Grid>
                            </Grid>
                        </Paper>
                    </Grid>

                    {/* ALERTS */}
                    <Grid item xs={12} md={6}>
                        <Paper sx={{ p: 3, mb: 4 }}>
                            <Typography variant="h4" gutterBottom>Alerts</Typography>
                            <Divider sx={{ mb: 2 }} />

                            <Stack spacing={2}>
                                <Alert severity="error">
                                    <AlertTitle>Error</AlertTitle>
                                    This is an error alert — <strong>check it out!</strong>
                                </Alert>
                                <Alert severity="warning">
                                    <AlertTitle>Warning</AlertTitle>
                                    This is a warning alert — <strong>check it out!</strong>
                                </Alert>
                                <Alert severity="info">
                                    <AlertTitle>Info</AlertTitle>
                                    This is an info alert — <strong>check it out!</strong>
                                </Alert>
                                <Alert severity="success">
                                    <AlertTitle>Success</AlertTitle>
                                    This is a success alert — <strong>check it out!</strong>
                                </Alert>
                            </Stack>
                        </Paper>
                    </Grid>

                    {/* LISTS */}
                    <Grid item xs={12} md={6}>
                        <Paper sx={{ p: 3, mb: 4 }}>
                            <Typography variant="h4" gutterBottom>Lists</Typography>
                            <Divider sx={{ mb: 2 }} />

                            <List>
                                <ListItem>
                                    <ListItemIcon>
                                        <HomeIcon />
                                    </ListItemIcon>
                                    <ListItemText primary="Home" secondary="Go to home page" />
                                </ListItem>
                                <ListItem>
                                    <ListItemIcon>
                                        <PersonIcon />
                                    </ListItemIcon>
                                    <ListItemText primary="Profile" secondary="View your profile" />
                                </ListItem>
                                <ListItemButton>
                                    <ListItemIcon>
                                        <SettingsIcon />
                                    </ListItemIcon>
                                    <ListItemText primary="Settings" secondary="Manage your preferences" />
                                </ListItemButton>
                            </List>
                        </Paper>
                    </Grid>

                    {/* TABS */}
                    <Grid item xs={12} md={6}>
                        <Paper sx={{ p: 3, mb: 4 }}>
                            <Typography variant="h4" gutterBottom>Tabs</Typography>
                            <Divider sx={{ mb: 2 }} />

                            <Box sx={{ width: '100%' }}>
                                <Box sx={{ borderBottom: 1, borderColor: 'divider' }}>
                                    <Tabs value={tabValue} onChange={handleTabChange} aria-label="basic tabs example">
                                        <Tab label="Tab 1" />
                                        <Tab label="Tab 2" />
                                        <Tab label="Tab 3" />
                                    </Tabs>
                                </Box>
                                <TabPanel value={tabValue} index={0}>
                                    Content for Tab 1
                                </TabPanel>
                                <TabPanel value={tabValue} index={1}>
                                    Content for Tab 2
                                </TabPanel>
                                <TabPanel value={tabValue} index={2}>
                                    Content for Tab 3
                                </TabPanel>
                            </Box>
                        </Paper>
                    </Grid>

                    {/* ACCORDIONS */}
                    <Grid item xs={12} md={6}>
                        <Paper sx={{ p: 3, mb: 4 }}>
                            <Typography variant="h4" gutterBottom>Accordions</Typography>
                            <Divider sx={{ mb: 2 }} />

                            <Accordion expanded={expanded === 'panel1'} onChange={handleAccordionChange('panel1')}>
                                <AccordionSummary
                                    expandIcon={<ExpandMoreIcon />}
                                    aria-controls="panel1a-content"
                                    id="panel1a-header"
                                >
                                    <Typography>Accordion 1</Typography>
                                </AccordionSummary>
                                <AccordionDetails>
                                    <Typography>
                                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse
                                        malesuada lacus ex, sit amet blandit leo lobortis eget.
                                    </Typography>
                                </AccordionDetails>
                            </Accordion>
                            <Accordion expanded={expanded === 'panel2'} onChange={handleAccordionChange('panel2')}>
                                <AccordionSummary
                                    expandIcon={<ExpandMoreIcon />}
                                    aria-controls="panel2a-content"
                                    id="panel2a-header"
                                >
                                    <Typography>Accordion 2</Typography>
                                </AccordionSummary>
                                <AccordionDetails>
                                    <Typography>
                                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse
                                        malesuada lacus ex, sit amet blandit leo lobortis eget.
                                    </Typography>
                                </AccordionDetails>
                            </Accordion>
                        </Paper>
                    </Grid>

                    {/* CHIPS AND BADGES */}
                    <Grid item xs={12}>
                        <Paper sx={{ p: 3, mb: 4 }}>
                            <Typography variant="h4" gutterBottom>Chips & Badges</Typography>
                            <Divider sx={{ mb: 2 }} />

                            <Stack direction="row" spacing={2} sx={{ mb: 3 }}>
                                <Chip label="Basic Chip" />
                                <Chip label="Clickable" onClick={() => { }} />
                                <Chip label="Deletable" onDelete={() => { }} />
                                <Chip avatar={<Avatar>M</Avatar>} label="With Avatar" />
                                <Chip label="Primary" color="primary" />
                                <Chip label="Success" color="success" />
                            </Stack>

                            <Stack direction="row" spacing={4} sx={{ mb: 3 }}>
                                <Badge badgeContent={4} color="primary">
                                    <MailIcon />
                                </Badge>
                                <Badge badgeContent={100} color="secondary">
                                    <MailIcon />
                                </Badge>
                                <Badge badgeContent={1000} max={999} color="error">
                                    <MailIcon />
                                </Badge>
                                <Tooltip title="Notifications">
                                    <IconButton>
                                        <Badge badgeContent={17} color="error">
                                            <NotificationsIcon />
                                        </Badge>
                                    </IconButton>
                                </Tooltip>
                            </Stack>
                        </Paper>
                    </Grid>
                </Grid>
            </Container>
        </>
    );
}

function MailIcon() {
    return (
        <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24">
            <path d="M0 0h24v24H0z" fill="none" />
            <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V8l8 5 8-5v10zm-8-7L4 6h16l-8 5z" />
        </svg>
    );
}

ComponentZoo.layout = (page: React.ReactNode) => <MainLayout children={page} />

export default ComponentZoo;