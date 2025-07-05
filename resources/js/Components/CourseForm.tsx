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
} from "@mui/material";

type Equipment = {
    id: number;
    name: string;
    slug: string;
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
    equipment: number[];
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
            </Grid2>
        </form>
    );
};

export default CourseForm;