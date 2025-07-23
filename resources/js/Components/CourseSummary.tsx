import StickyNote2Icon from "@mui/icons-material/StickyNote2";
import ScheduleIcon from "@mui/icons-material/Schedule";
import HourglassBottomIcon from "@mui/icons-material/HourglassBottom";
import PauseIcon from "@mui/icons-material/Pause";
import CheckCircleIcon from "@mui/icons-material/CheckCircle";
import ReactMarkdown from "react-markdown";

import {
    Typography,
    Box,
    Card,
    CardContent,
    Chip,
    Link,
    Stack,
    Alert,
} from "@mui/material";
import { ComponentProps } from "react";

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
    induction_category: string | null;
    urls: {
        show: string;
    };
};

type Induction = {
    key: string;
    trained: string;
    is_trainer: boolean;
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
    paused_at?: string | null;
    is_paused?: boolean;
    equipment: Equipment[];
    urls?: {
        show: string;
    };
};

type Props = ComponentProps<typeof Box> & {
    course: CourseProps;
    formatOptions?: Record<string, string>;
    frequencyOptions?: Record<string, string>;
    clickable?: boolean;
    isPreview?: boolean;
    userInductions?: Induction[];
};

const CourseSummary = ({
    course,
    formatOptions = {},
    frequencyOptions = {},
    clickable = true,
    isPreview = false,
    userInductions = [],
    ...rest
}: Props) => {
    // Handle format/frequency which might be strings or objects
    const formatLabel =
        typeof course.format === "object"
            ? course.format.label
            : formatOptions[course.format] || course.format;

    const frequencyLabel =
        typeof course.frequency === "object"
            ? course.frequency.label
            : frequencyOptions[course.frequency] || course.frequency;

    const isUserTrained =
        course.equipment.length > 0 &&
        course.equipment.every((equipment) => {
            return userInductions.some(
                (induction) => induction.key === equipment.induction_category
            );
        });

    return (
        <Box {...rest}>
            {isPreview && (
                <Typography variant="h6" gutterBottom>
                    Preview (How it will appear on the index page)
                </Typography>
            )}
            <Card
                component={clickable && course.urls ? Link : "div"}
                href={clickable && course.urls ? course.urls.show : undefined}
                sx={{
                    height: "100%",
                    display: "flex",
                    flexDirection: "column",
                    ...(clickable &&
                        course.urls && {
                            textDecoration: "none",
                            color: "inherit",
                            "&:hover": {
                                boxShadow: 3,
                                transform: "translateY(-2px)",
                                transition: "all 0.2s ease-in-out",
                            },
                        }),
                    ...(isPreview && {
                        border: "2px dashed",
                        borderColor: "primary.main",
                        backgroundColor: "action.hover",
                    }),
                }}
            >
                <CardContent sx={{ flexGrow: 1 }}>
                    <Box
                        sx={{
                            display: "flex",
                            alignItems: "flex-start",
                            justifyContent: "space-between",
                            mb: 1,
                        }}
                    >
                        <Typography variant="h6" component="h2">
                            {course.name || "Induction Name"}
                        </Typography>
                        {isUserTrained && (
                            <CheckCircleIcon color="success" sx={{ ml: 1 }} />
                        )}
                    </Box>

                    <Stack spacing={2}>
                        {course.is_paused && (
                            <Box>
                                <Chip
                                    color="warning"
                                    variant="outlined"
                                    icon={<PauseIcon />}
                                    label="Unavailable for enrollment"
                                    size="small"
                                />
                            </Box>
                        )}

                        <Typography color="text.secondary" sx={{ mb: 2 }}>
                            {course.description ? (
                                <ReactMarkdown
                                    components={{
                                        p: ({ children }) => children,
                                        h1: ({ children }) => children,
                                        h2: ({ children }) => children,
                                        h3: ({ children }) => children,
                                        h4: ({ children }) => children,
                                        h5: ({ children }) => children,
                                        h6: ({ children }) => children,
                                        strong: ({ children }) => children,
                                        em: ({ children }) => children,
                                        a: ({ children }) => children,
                                        code: ({ children }) => children,
                                        pre: ({ children }) => children,
                                        blockquote: ({ children }) => children,
                                        ul: ({ children }) => children,
                                        ol: ({ children }) => children,
                                        li: ({ children }) => children,
                                        br: () => " ",
                                        hr: () => "",
                                    }}
                                >
                                    {course.description}
                                </ReactMarkdown>
                            ) : (
                                "Induction description will appear here..."
                            )}
                        </Typography>

                        <Stack spacing={1}>
                            <Stack
                                direction="row"
                                spacing={1}
                                justifyContent="space-between"
                            >
                                {formatLabel && (
                                    <Chip
                                        icon={<StickyNote2Icon />}
                                        label={formatLabel}
                                        variant="outlined"
                                        sx={{ width: "100%" }}
                                    />
                                )}
                                {frequencyLabel && (
                                    <Chip
                                        icon={<ScheduleIcon />}
                                        label={frequencyLabel}
                                        variant="outlined"
                                        sx={{ width: "100%" }}
                                    />
                                )}
                                {course.wait_time && (
                                    <Chip
                                        icon={<HourglassBottomIcon />}
                                        label={course.wait_time}
                                        variant="outlined"
                                        sx={{ width: "100%" }}
                                    />
                                )}
                            </Stack>
                        </Stack>

                        {course.equipment.length > 0 && (
                            <Stack spacing={0.5}>
                                {course.equipment.map((equipment) => (
                                    <Chip
                                        label={equipment.name}
                                        size="small"
                                        icon={
                                            userInductions.some(
                                                (induction) =>
                                                    induction.key ===
                                                    equipment.induction_category
                                            ) ? (
                                                <CheckCircleIcon />
                                            ) : undefined
                                        }
                                    />
                                ))}
                            </Stack>
                        )}

                        {!course.name && !course.description && isPreview && (
                            <Typography
                                variant="body2"
                                color="text.secondary"
                                sx={{ fontStyle: "italic" }}
                            >
                                Fill in the form above to see the preview
                            </Typography>
                        )}
                    </Stack>
                </CardContent>
            </Card>
        </Box>
    );
};

export default CourseSummary;
