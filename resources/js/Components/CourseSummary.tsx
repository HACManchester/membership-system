import React, { useState } from "react";
import {
    Typography,
    Box,
    Paper,
    Tabs,
    Tab,
    Card,
    CardHeader,
    CardContent,
    CardActions,
    Button,
    Chip,
    Divider,
    Avatar,
    List,
    ListItem,
    ListItemText,
    ListItemIcon,
    Grid2,
} from "@mui/material";
import { Link } from "@inertiajs/react";

// Import MUI icons
import AccessTimeIcon from '@mui/icons-material/AccessTime';
import EventIcon from '@mui/icons-material/Event';
import FormatListBulletedIcon from '@mui/icons-material/FormatListBulleted';
import BuildIcon from '@mui/icons-material/Build';
import ArrowForwardIcon from '@mui/icons-material/ArrowForward';
import SchoolIcon from '@mui/icons-material/School';
import InfoIcon from '@mui/icons-material/Info';

type Equipment = {
    id: number;
    name: string;
    slug: string;
    urls: {
        show: string;
    };
};

type CourseProps = {
    id: number;
    name: string;
    slug: string;
    description: string;
    format: { label: string; value: string };
    format_description: string;
    frequency: { label: string; value: string };
    frequency_description: string;
    wait_time: string;
    equipment: Equipment[];
    urls?: {
        show: string;
    };
};

interface TabPanelProps {
    children?: React.ReactNode;
    index: number;
    value: number;
}

function TabPanel(props: TabPanelProps) {
    const { children, value, index, ...other } = props;

    return (
        <div
            role="tabpanel"
            hidden={value !== index}
            id={`course-tabpanel-${index}`}
            aria-labelledby={`course-tab-${index}`}
            {...other}
        >
            {value === index && <Box sx={{ py: 3 }}>{children}</Box>}
        </div>
    );
}

const CourseSummary = ({ course }: { course: CourseProps }) => {
    const [tabValue, setTabValue] = useState(0);

    const handleTabChange = (event: React.SyntheticEvent, newValue: number) => {
        setTabValue(newValue);
    };

    // Get first letter of course name for the avatar
    const avatarLetter = course.name.charAt(0);

    return (
        <Card elevation={3} sx={{ mb: 4, overflow: "visible" }}>
            <CardHeader
                avatar={
                    <Avatar sx={{ bgcolor: "primary.main" }}>
                        {avatarLetter}
                    </Avatar>
                }
                title={
                    <Typography variant="h5" component="h2">
                        {course.name}
                    </Typography>
                }
                subheader={
                    <Chip 
                        icon={<AccessTimeIcon />} 
                        label={`Wait time: ${course.wait_time}`} 
                        size="small"
                        sx={{ mt: 1 }}
                    />
                }
            />
            
            <Divider />
            
            <Box sx={{ borderBottom: 1, borderColor: 'divider' }}>
                <Tabs 
                    value={tabValue} 
                    onChange={handleTabChange}
                    variant="fullWidth"
                    textColor="primary"
                    indicatorColor="primary"
                >
                    <Tab icon={<InfoIcon />} iconPosition="start" label="Overview" />
                    <Tab icon={<BuildIcon />} iconPosition="start" label="Equipment" />
                </Tabs>
            </Box>
            
            <CardContent sx={{ pb: 0 }}>
                <TabPanel value={tabValue} index={0}>
                    <Typography variant="body1" paragraph>
                        {course.description}
                    </Typography>
                    <Box sx={{ display: 'flex', gap: 2, flexWrap: 'wrap', mb: 3 }}>
                        <Box sx={{ 
                            flex: '1 0 200px', 
                            p: 2, 
                            bgcolor: 'background.paper', 
                            borderRadius: 1,
                            border: '1px solid',
                            borderColor: 'divider'
                        }}>
                            <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                                <SchoolIcon color="primary" sx={{ mr: 1 }} />
                                <Typography variant="subtitle2" color="text.secondary">
                                    Format
                                </Typography>
                            </Box>
                            <Typography variant="h6" gutterBottom>
                                {course.format.label}
                            </Typography>
                            <Typography variant="body2" color="text.secondary">
                                {course.format_description}
                            </Typography>
                        </Box>
                        <Box sx={{ 
                            flex: '1 0 200px', 
                            p: 2, 
                            bgcolor: 'background.paper', 
                            borderRadius: 1,
                            border: '1px solid',
                            borderColor: 'divider'
                        }}>
                            <Box sx={{ display: 'flex', alignItems: 'center', mb: 1 }}>
                                <EventIcon color="primary" sx={{ mr: 1 }} />
                                <Typography variant="subtitle2" color="text.secondary">
                                    Frequency
                                </Typography>
                            </Box>
                            <Typography variant="h6" gutterBottom>
                                {course.frequency.label}
                            </Typography>
                            <Typography variant="body2" color="text.secondary">
                                {course.frequency_description}
                            </Typography>
                        </Box>
                    </Box>
                    
                    <Box sx={{ 
                        p: 2, 
                        bgcolor: 'background.paper', 
                        borderRadius: 1,
                        border: '1px solid',
                        borderColor: 'divider',
                        display: 'flex',
                        alignItems: 'flex-start'
                    }}>
                        <AccessTimeIcon color="primary" sx={{ mr: 1, mt: 0.5 }} />
                        <Box>
                            <Typography variant="subtitle2" color="text.secondary" gutterBottom>
                                Wait Time
                            </Typography>
                            <Typography variant="h6">
                                {course.wait_time}
                            </Typography>
                        </Box>
                    </Box>
                </TabPanel>
                
                <TabPanel value={tabValue} index={1}>
                    <Typography paragraph variant="body1">
                        Completing this induction will allow you to use the following equipment:
                    </Typography>
                    <List>
                        {course.equipment.map((equipment) => (
                            <ListItem 
                                key={equipment.id}
                                component={course.urls ? Link : "div"}
                                href={course.urls?.show}
                                sx={{ 
                                    borderRadius: 1, 
                                    '&:hover': { 
                                        bgcolor: 'action.hover',
                                    },
                                    mb: 1
                                }}
                            >
                                <ListItemIcon>
                                    <BuildIcon color="primary" />
                                </ListItemIcon>
                                <ListItemText 
                                    primary={equipment.name}
                                />
                            </ListItem>
                        ))}
                    </List>
                </TabPanel>
            </CardContent>
            
            {course.urls && (
                <CardActions sx={{ p: 2, pt: 0 }}>
                    <Button 
                        variant="outlined" 
                        component={Link} 
                        href={course.urls.show}
                        endIcon={<ArrowForwardIcon />}
                    >
                        View Full Details
                    </Button>
                </CardActions>
            )}
        </Card>
    );
};

export default CourseSummary;
