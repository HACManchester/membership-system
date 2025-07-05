import React from "react";
import {
    Typography,
    Box,
    Card,
    CardContent,
    Chip,
    Link,
} from "@mui/material";

type Equipment = {
    id: number;
    name: string;
    slug: string;   
    working: boolean;
    permaloan: boolean;
    dangerous: boolean;
    room: string | null;
    room_display: string | null;
    ppe: string[];
    photo_url: string | null;
    urls: {
        show: string;
    };
};

type CourseProps = {
    id?: number;
    name: string;
    slug?: string;
    description: string;
    format: { label: string; value: string } | string;
    format_description?: string;
    frequency: { label: string; value: string } | string;
    frequency_description?: string;
    wait_time: string;
    equipment: Equipment[];
    urls?: {
        show: string;
    };
};

type Props = {
    course: CourseProps;
    formatOptions?: Record<string, string>;
    frequencyOptions?: Record<string, string>;
    clickable?: boolean;
    isPreview?: boolean;
};

const CourseSummary = ({ 
    course, 
    formatOptions = {}, 
    frequencyOptions = {}, 
    clickable = true,
    isPreview = false 
}: Props) => {
    // Handle format/frequency which might be strings or objects
    const formatLabel = typeof course.format === 'object' 
        ? course.format.label 
        : formatOptions[course.format] || course.format;
    
    const frequencyLabel = typeof course.frequency === 'object' 
        ? course.frequency.label 
        : frequencyOptions[course.frequency] || course.frequency;

    return (
        <Box>
            {isPreview && (
                <Typography variant="h6" gutterBottom>
                    Preview (How it will appear on the index page)
                </Typography>
            )}
            <Card 
                component={clickable && course.urls ? Link : 'div'}
                href={clickable && course.urls ? course.urls.show : undefined}
                sx={{ 
                    height: '100%', 
                    display: 'flex', 
                    flexDirection: 'column',
                    ...(clickable && course.urls && {
                        textDecoration: 'none',
                        color: 'inherit',
                        '&:hover': {
                            boxShadow: 3,
                            transform: 'translateY(-2px)',
                            transition: 'all 0.2s ease-in-out'
                        }
                    }),
                    ...(isPreview && {
                        border: '2px dashed',
                        borderColor: 'primary.main',
                        backgroundColor: 'action.hover'
                    })
                }}>
                <CardContent sx={{ flexGrow: 1 }}>
                    <Typography variant="h6" component="h2" gutterBottom>
                        {course.name || "Course Name"}
                    </Typography>
                    
                    <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
                        {course.description 
                            ? (course.description.length > 120 
                                ? `${course.description.substring(0, 120)}...`
                                : course.description)
                            : "Course description will appear here..."
                        }
                    </Typography>
                    
                    <Box sx={{ display: 'flex', flexWrap: 'wrap', gap: 0.5, mb: 2 }}>
                        {formatLabel && (
                            <Chip 
                                label={formatLabel} 
                                size="small" 
                                variant="outlined"
                            />
                        )}
                        {frequencyLabel && (
                            <Chip 
                                label={frequencyLabel} 
                                size="small" 
                                variant="outlined"
                            />
                        )}
                        {course.wait_time && (
                            <Chip 
                                label={`Wait: ${course.wait_time}`} 
                                size="small" 
                                variant="outlined"
                            />
                        )}
                    </Box>
                    
                    {course.equipment.length > 0 && (
                        <Typography variant="body2" color="text.secondary">
                            {course.equipment.length} piece{course.equipment.length !== 1 ? 's' : ''} of equipment
                        </Typography>
                    )}
                    
                    {!course.name && !course.description && isPreview && (
                        <Typography variant="body2" color="text.secondary" sx={{ fontStyle: 'italic' }}>
                            Fill in the form above to see the preview
                        </Typography>
                    )}
                </CardContent>
            </Card>
        </Box>
    );
};

export default CourseSummary;
