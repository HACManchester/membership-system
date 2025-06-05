import React, { useState } from "react";
import {
    TextField,
    Button,
    FormControl,
    FormLabel,
    RadioGroup,
    Radio,
    FormControlLabel,
    FormHelperText,
    Grid2,
    InputLabel,
    Select,
    MenuItem,
    Box,
    Typography,
    Divider,
    Switch,
    FormGroup,
} from "@mui/material";
import { useForm } from "@inertiajs/react";
import CourseSummary from "./CourseSummary";

type Equipment = {
    id: number;
    name: string;
    slug: string;
    urls: {
        show: string;
    };
};

type CourseData = {
    id?: number;
    name: string;
    slug: string;
    description: string;
    format: string | { label: string; value: string };
    format_description: string;
    frequency: string | { label: string; value: string };
    frequency_description: string;
    wait_time: string;
    urls?: {
        show: string;
    };
};

type Props = {
    course?: {
        data: CourseData;
        equipment: number[];
    };
    formatOptions: Record<string, string>;
    frequencyOptions: Record<string, string>;
    equipment: Equipment[];
    submitUrl: string;
    method?: 'post' | 'put';
    submitLabel?: string;
    onSuccess?: () => void;
};

const CourseForm = ({
    course,
    formatOptions,
    frequencyOptions,
    equipment,
    submitUrl,
    method = 'post',
    submitLabel = "Save",
    onSuccess,
}: Props) => {
    // Initialize form data based on whether we have course data or not
    const initialData = course ? {
        name: course.data.name,
        slug: course.data.slug,
        description: course.data.description,
        format: typeof course.data.format === 'object' ? course.data.format.value : course.data.format,
        format_description: course.data.format_description,
        frequency: typeof course.data.frequency === 'object' ? course.data.frequency.value : course.data.frequency,
        frequency_description: course.data.frequency_description,
        wait_time: course.data.wait_time,
        equipment: course.equipment,
    } : {
        name: "",
        slug: "",
        description: "",
        format: "",
        format_description: "",
        frequency: "",
        frequency_description: "",
        wait_time: "",
        equipment: [] as number[],
    };

    const { data, setData, post, put, processing, errors } = useForm(initialData);
    const [preview, setPreview] = useState(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        
        if (method === 'post') {
            post(submitUrl, {
                onSuccess: onSuccess,
            });
        } else {
            put(submitUrl, {
                onSuccess: onSuccess,
            });
        }
    };

    const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const name = e.target.value;
        setData("name", name);
        // Auto-generate slug from name if the slug is empty or was auto-generated previously
        if (!data.slug || data.slug === data.name.toLowerCase().replace(/[^\w\s]/gi, "").replace(/\s+/g, "-")) {
            setData(
                "slug",
                name
                    .toLowerCase()
                    .replace(/[^\w\s]/gi, "")
                    .replace(/\s+/g, "-")
            );
        }
    };

    return (
        <form onSubmit={handleSubmit}>
            <Grid2 container spacing={3}>
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
                    <TextField
                        label="Description"
                        value={data.description}
                        onChange={(e) => setData("description", e.target.value)}
                        fullWidth
                        multiline
                        rows={3}
                        required
                        error={!!errors.description}
                        helperText={errors.description}
                    />
                </Grid2>

                <Grid2 size={12}>
                    <FormControl component="fieldset" error={!!errors.format} fullWidth>
                        <FormLabel component="legend">Format</FormLabel>
                        <Grid2 container spacing={2}>
                            {Object.entries(formatOptions).map(([value, label]) => (
                                <Grid2 key={value} size={12 / Object.keys(formatOptions).length}>
                                    <FormControlLabel
                                        value={value}
                                        control={
                                            <Radio 
                                                checked={data.format === value}
                                                onChange={(e) => setData("format", e.target.value)}
                                            />
                                        }
                                        label={label}
                                    />
                                </Grid2>
                            ))}
                        </Grid2>
                        {errors.format && (
                            <FormHelperText>{errors.format}</FormHelperText>
                        )}
                    </FormControl>
                </Grid2>

                <Grid2 size={12}>
                    <TextField
                        label="Format Description"
                        value={data.format_description}
                        onChange={(e) => setData("format_description", e.target.value)}
                        fullWidth
                        error={!!errors.format_description}
                        helperText={errors.format_description}
                    />
                </Grid2>

                <Grid2 size={12}>
                    <FormControl component="fieldset" error={!!errors.frequency} fullWidth>
                        <FormLabel component="legend">Frequency</FormLabel>
                        <Grid2 container spacing={2}>
                            {Object.entries(frequencyOptions).map(([value, label]) => (
                                <Grid2 key={value} size={12 / Object.keys(frequencyOptions).length}>
                                    <FormControlLabel
                                        value={value}
                                        control={
                                            <Radio 
                                                checked={data.frequency === value}
                                                onChange={(e) => setData("frequency", e.target.value)}
                                            />
                                        }
                                        label={label}
                                    />
                                </Grid2>
                            ))}
                        </Grid2>
                        {errors.frequency && (
                            <FormHelperText>{errors.frequency}</FormHelperText>
                        )}
                    </FormControl>
                </Grid2>

                <Grid2 size={12}>
                    <TextField
                        label="Frequency Description"
                        value={data.frequency_description}
                        onChange={(e) => setData("frequency_description", e.target.value)}
                        fullWidth
                        error={!!errors.frequency_description}
                        helperText={errors.frequency_description}
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
                    <Button
                        type="submit"
                        variant="contained"
                        color="primary"
                        disabled={processing}
                        sx={{ mt: 2 }}
                    >
                        {submitLabel}
                    </Button>
                </Grid2>

                <Grid2 size={12}>
                    <FormGroup>
                        <FormControlLabel
                            control={
                                <Switch
                                    checked={preview}
                                    onChange={(e) => setPreview(e.target.checked)}
                                />
                            }
                            label="Enable Preview"
                        />
                    </FormGroup>
                </Grid2>

                {preview && (
                    <Grid2 size={12}>
                        <Divider sx={{ my: 2 }} />
                        <Typography variant="h6">Course Preview</Typography>
                        <CourseSummary
                            course={{
                                id: course?.data.id || 0,
                                name: data.name,
                                slug: data.slug,
                                description: data.description,
                                format: {
                                    label: formatOptions[data.format] || '',
                                    value: data.format
                                },
                                format_description: data.format_description,
                                frequency: {
                                    label: frequencyOptions[data.frequency] || '',
                                    value: data.frequency
                                },
                                frequency_description: data.frequency_description,
                                wait_time: data.wait_time,
                                equipment: equipment.filter(item => 
                                    data.equipment.includes(item.id)
                                ),
                                urls: course?.data.urls
                            }}
                        />
                    </Grid2>
                )}
            </Grid2>
        </form>
    );
};

export default CourseForm;