import React from "react";
import {
    TextField,
    Button,
    FormControl,
    FormHelperText,
    Grid2,
    InputLabel,
    Select,
    MenuItem,
    FormControlLabel,
    Switch,
    Typography,
    Divider,
    Box,
} from "@mui/material";
import MarkdownTextField from "./MarkdownTextField";

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

type FormData = {
    name: string;
    slug: string;
    description: string;
    format: string;
    format_description: string;
    frequency: string;
    frequency_description: string;
    wait_time: string;
    training_organisation_description: string;
    schedule_url: string;
    quiz_url: string;
    request_induction_url: string;
    equipment: number[];
    paused: boolean;
};

type Props = {
    data: FormData;
    setData: (key: string, value: any) => void;
    formatOptions: Record<string, string>;
    frequencyOptions: Record<string, string>;
    equipment: Equipment[];
    onSubmit: (e: React.FormEvent) => void;
    processing: boolean;
    errors: Record<string, string>;
    submitLabel?: string;
};

const CourseForm = ({
    data,
    setData,
    formatOptions,
    frequencyOptions,
    equipment,
    onSubmit,
    processing,
    errors,
    submitLabel = "Save",
}: Props) => {

    const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const name = e.target.value;
        setData("name", name);
        // Auto-generate slug from name
        if (!data.slug || data.slug === generateSlug(data.name)) {
            setData("slug", generateSlug(name));
        }
    };

    const generateSlug = (text: string) => {
        return text.toLowerCase().replace(/[^\w\s]/gi, "").replace(/\s+/g, "-");
    };

    return (
        <form onSubmit={onSubmit}>
            <Grid2 container spacing={3}>
                {/* Basic Information Section */}
                <Grid2 size={12}>
                    <Typography variant="h6" component="h3" gutterBottom>
                        Basic Information
                    </Typography>
                </Grid2>

                <Grid2 size={12}>
                    <TextField
                        label="Name"
                        value={data.name}
                        onChange={handleNameChange}
                        fullWidth
                        required
                        error={!!errors.name}
                        helperText={errors.name}
                    />
                </Grid2>

                <Grid2 size={12}>
                    <TextField
                        label="Slug"
                        value={data.slug}
                        onChange={(e) => setData("slug", e.target.value)}
                        fullWidth
                        required
                        error={!!errors.slug}
                        helperText={
                            errors.slug ||
                            "URL-friendly identifier, auto-generated from name"
                        }
                    />
                </Grid2>

                <Grid2 size={12}>
                    <MarkdownTextField
                        label="Description"
                        value={data.description}
                        onChange={(e) => setData("description", e.target.value)}
                        required
                        error={!!errors.description}
                        helperText={errors.description || "Brief introduction to the induction, what it covers, how the session will be run, etc. Supports markdown formatting."}
                        rows={3}
                    />
                </Grid2>

                {/* Training Format & Schedule Section */}
                <Grid2 size={12} sx={{ mt: 3 }}>
                    <Divider />
                    <Box sx={{ mt: 2 }}>
                        <Typography variant="h6" component="h3" gutterBottom>
                            Training Format & Schedule
                        </Typography>
                    </Box>
                </Grid2>

                <Grid2 size={{ xs: 12, md: 6 }}>
                    <FormControl fullWidth error={!!errors.format}>
                        <InputLabel>Format</InputLabel>
                        <Select
                            value={data.format}
                            label="Format"
                            onChange={(e) => setData("format", e.target.value)}
                        >
                            {Object.entries(formatOptions).map(([value, label]) => (
                                <MenuItem key={value} value={value}>
                                    {label}
                                </MenuItem>
                            ))}
                        </Select>
                        {errors.format && (
                            <FormHelperText>{errors.format}</FormHelperText>
                        )}
                    </FormControl>
                </Grid2>

                <Grid2 size={{ xs: 12, md: 6 }}>
                    <FormControl fullWidth error={!!errors.frequency}>
                        <InputLabel>Frequency</InputLabel>
                        <Select
                            value={data.frequency}
                            label="Frequency"
                            onChange={(e) => setData("frequency", e.target.value)}
                        >
                            {Object.entries(frequencyOptions).map(([value, label]) => (
                                <MenuItem key={value} value={value}>
                                    {label}
                                </MenuItem>
                            ))}
                        </Select>
                        {errors.frequency && (
                            <FormHelperText>{errors.frequency}</FormHelperText>
                        )}
                    </FormControl>
                </Grid2>

                <Grid2 size={12}>
                    <MarkdownTextField
                        label="Format Description"
                        value={data.format_description}
                        onChange={(e) => setData("format_description", e.target.value)}
                        error={!!errors.format_description}
                        helperText={errors.format_description || "e.g. 'In-person workshop', 'Online webinar', etc. Supports markdown formatting."}
                        rows={2}
                    />
                </Grid2>

                <Grid2 size={12}>
                    <MarkdownTextField
                        label="Frequency Description"
                        value={data.frequency_description}
                        onChange={(e) => setData("frequency_description", e.target.value)}
                        error={!!errors.frequency_description}
                        helperText={errors.frequency_description || "e.g. 'Weekly on Mondays at 7pm'. Supports markdown formatting."}
                        rows={2}
                    />
                </Grid2>

                <Grid2 size={12}>
                    <TextField
                        label="Wait Time"
                        value={data.wait_time}
                        onChange={(e) => setData("wait_time", e.target.value)}
                        fullWidth
                        required
                        error={!!errors.wait_time}
                        helperText={errors.wait_time || 'e.g. "1-2 weeks"'}
                    />
                </Grid2>

                {/* Training Organization Section */}
                <Grid2 size={12} sx={{ mt: 3 }}>
                    <Divider />
                    <Box sx={{ mt: 2 }}>
                        <Typography variant="h6" component="h3" gutterBottom>
                            How to Get Trained
                        </Typography>
                    </Box>
                </Grid2>

                <Grid2 size={12}>
                    <MarkdownTextField
                        label="How to Organise Training"
                        value={data.training_organisation_description}
                        onChange={(e) => setData("training_organisation_description", e.target.value)}
                        error={!!errors.training_organisation_description}
                        helperText={errors.training_organisation_description || "Instructions to members on how to become trained. E.g. Whether to ask on forum or Telegram, any prerequisites they need to fulfil, etc. Supports markdown formatting."}
                        rows={3}
                    />
                </Grid2>

                <Grid2 size={12}>
                    <TextField
                        label="Schedule URL"
                        value={data.schedule_url}
                        onChange={(e) => setData("schedule_url", e.target.value)}
                        fullWidth
                        type="url"
                        error={!!errors.schedule_url}
                        helperText={errors.schedule_url || "Link to forum thread, category, or google sheet where training slots are announced (optional)"}
                    />
                </Grid2>

                <Grid2 size={12}>
                    <TextField
                        label="Quiz URL"
                        value={data.quiz_url}
                        onChange={(e) => setData("quiz_url", e.target.value)}
                        fullWidth
                        type="url"
                        error={!!errors.quiz_url}
                        helperText={errors.quiz_url || "Link to online quiz if applicable (optional)"}
                    />
                </Grid2>

                <Grid2 size={12}>
                    <TextField
                        label="Request Training URL"
                        value={data.request_induction_url}
                        onChange={(e) => setData("request_induction_url", e.target.value)}
                        fullWidth
                        type="url"
                        error={!!errors.request_induction_url}
                        helperText={errors.request_induction_url || "Link to forum thread for members to request training sessions. We prefer the forum over Telegram, as not every member has or is comfortable using Telegram. (optional)"}
                    />
                </Grid2>

                {/* Equipment & Status Section */}
                <Grid2 size={12} sx={{ mt: 3 }}>
                    <Divider />
                    <Box sx={{ mt: 2 }}>
                        <Typography variant="h6" component="h3" gutterBottom>
                            Equipment & Status
                        </Typography>
                    </Box>
                </Grid2>

                <Grid2 size={12}>
                    <FormControl fullWidth error={!!errors.equipment}>
                        <InputLabel id="equipment-label">Equipment</InputLabel>
                        <Select
                            labelId="equipment-label"
                            multiple
                            value={data.equipment}
                            label="Equipment"
                            onChange={(e) => {
                                const value = e.target.value as unknown as number[];
                                setData("equipment", value);
                            }}
                        >
                            {equipment.map((item) => (
                                <MenuItem key={item.id} value={item.id}>
                                    {item.name}
                                </MenuItem>
                            ))}
                        </Select>
                        {errors.equipment && (
                            <FormHelperText>{errors.equipment}</FormHelperText>
                        )}
                        <FormHelperText>
                            Select equipment that requires this induction
                        </FormHelperText>
                    </FormControl>
                </Grid2>

                <Grid2 size={12}>
                    <FormControlLabel
                        control={
                            <Switch
                                checked={data.paused}
                                onChange={(e) => setData("paused", e.target.checked)}
                            />
                        }
                        label="Pause this course"
                    />
                    {errors.paused && (
                        <FormHelperText error>{errors.paused}</FormHelperText>
                    )}
                </Grid2>

                <Grid2 size={12} sx={{ mt: 4 }}>
                    <Button
                        type="submit"
                        variant="contained"
                        color="primary"
                        disabled={processing}
                        size="large"
                    >
                        {submitLabel}
                    </Button>
                </Grid2>
            </Grid2>
        </form>
    );
};

export default CourseForm;