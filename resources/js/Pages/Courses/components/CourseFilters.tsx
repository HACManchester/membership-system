import React from "react";
import {
    Paper,
    Stack,
    FormControl,
    InputLabel,
    Select,
    MenuItem,
    FormControlLabel,
    Switch,
} from "@mui/material";

interface CourseFiltersProps {
    filters: {
        showPaused: boolean;
        hideCompleted: boolean;
        hidePending: boolean;
        selectedRoom: string;
        selectedFormat: string;
    };
    options: {
        rooms: string[];
        formats: Array<{ value: string; label: string }>;
    };
    counts: {
        paused: number;
        completed: number;
        pending: number;
    };
    onFilterChange: <K extends keyof CourseFiltersProps["filters"]>(
        key: K,
        value: CourseFiltersProps["filters"][K]
    ) => void;
}

const CourseFilters: React.FC<CourseFiltersProps> = ({
    filters,
    options,
    counts,
    onFilterChange,
}) => {
    return (
        <Paper sx={{ p: 2, mb: 4 }}>
            <Stack
                direction="row"
                spacing={2}
                alignItems="center"
                flexWrap="wrap"
                useFlexGap
            >
                <FormControl size="small" sx={{ minWidth: 200 }}>
                    <InputLabel>Filter by Room</InputLabel>
                    <Select
                        value={filters.selectedRoom}
                        label="Filter by Room"
                        onChange={(e) =>
                            onFilterChange("selectedRoom", e.target.value)
                        }
                    >
                        <MenuItem value="all">All Rooms</MenuItem>
                        {options.rooms.map((room) => (
                            <MenuItem key={room} value={room}>
                                {room}
                            </MenuItem>
                        ))}
                        <MenuItem value="ungrouped">Ungrouped Courses</MenuItem>
                    </Select>
                </FormControl>

                <FormControl size="small" sx={{ minWidth: 200 }}>
                    <InputLabel>Filter by Format</InputLabel>
                    <Select
                        value={filters.selectedFormat}
                        label="Filter by Format"
                        onChange={(e) =>
                            onFilterChange("selectedFormat", e.target.value)
                        }
                    >
                        <MenuItem value="all">All Formats</MenuItem>
                        {options.formats.map((format) => (
                            <MenuItem key={format.value} value={format.value}>
                                {format.label}
                            </MenuItem>
                        ))}
                    </Select>
                </FormControl>

                {counts.paused > 0 && (
                    <FormControlLabel
                        control={
                            <Switch
                                checked={filters.showPaused}
                                onChange={(e) =>
                                    onFilterChange("showPaused", e.target.checked)
                                }
                            />
                        }
                        label={`Show unavailable (${counts.paused})`}
                    />
                )}

                {counts.completed > 0 && (
                    <FormControlLabel
                        control={
                            <Switch
                                checked={filters.hideCompleted}
                                onChange={(e) =>
                                    onFilterChange("hideCompleted", e.target.checked)
                                }
                            />
                        }
                        label={`Hide completed (${counts.completed})`}
                    />
                )}

                {counts.pending > 0 && (
                    <FormControlLabel
                        control={
                            <Switch
                                checked={filters.hidePending}
                                onChange={(e) =>
                                    onFilterChange("hidePending", e.target.checked)
                                }
                            />
                        }
                        label={`Hide non-live (${counts.pending})`}
                    />
                )}
            </Stack>
        </Paper>
    );
};

export default CourseFilters;