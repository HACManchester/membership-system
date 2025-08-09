import React, { useState } from "react";
import {
    Typography,
    Container,
    Paper,
    Button,
    Link,
    Alert,
    Grid2,
    Stack,
    Collapse,
} from "@mui/material";
import MainLayout from "../../Layouts/MainLayout";
import PageTitle from "../../Components/PageTitle";
import CourseSummary from "../../Components/CourseSummary";
import CourseFilters from "./components/CourseFilters";
import { useCourseFilters } from "./hooks/useCourseFilters";
import { useCourseGrouping } from "./hooks/useCourseGrouping";
import { CourseResource, EquipmentResource } from "../../types/resources";
import { TransitionGroup } from "react-transition-group";

const CourseGroup = ({
    title,
    courses,
    canSeeNonLiveCourses,
}: {
    title: string;
    courses: CourseResource[];
    canSeeNonLiveCourses: boolean;
}) => {
    if (courses.length === 0) return null;

    return (
        <Stack spacing={2}>
            <Typography variant="h5" gutterBottom>
                {title}
            </Typography>
            <Grid2 container spacing={3}>
                {courses.map((course) => {
                    return (
                        <Grid2 key={course.id} size={{ xs: 12, md: 6, lg: 4 }}>
                            <CourseSummary
                                course={course}
                                height="100%"
                                canSeeNonLiveCourses={canSeeNonLiveCourses}
                            />
                        </Grid2>
                    );
                })}
            </Grid2>
        </Stack>
    );
};

type Props = {
    courses: CourseResource[];
    equipmentWithoutLiveCourse: EquipmentResource[];
    canSeeNonLiveCourses: boolean;
    can?: {
        create: boolean;
    };
    urls: {
        create: string;
    };
};

const Index = ({
    courses,
    equipmentWithoutLiveCourse,
    canSeeNonLiveCourses,
    can = { create: false },
    urls,
}: Props) => {
    const [equipmentExpanded, setEquipmentExpanded] = useState<boolean>(false);
    
    const {
        filters,
        filteredCourses,
        filterOptions,
        counts,
        updateFilter,
    } = useCourseFilters(courses);

    const { groupedByRoom, ungroupedCourses } = useCourseGrouping(filteredCourses);

    const actionButtons = (
        <Stack direction="row" justifyContent="flex-end">
            {can.create && (
                <Link href={urls.create} underline="none">
                    <Button variant="contained" color="primary">
                        Create induction
                    </Button>
                </Link>
            )}
        </Stack>
    );

    return (
        <>
            <PageTitle title="Inductions" actionButtons={actionButtons} />
            <Container sx={{ mt: 4, pb: 4 }}>
                <Stack spacing={4}>
                    <Stack spacing={2}>
                        <Paper sx={{ p: 3, mb: 4 }}>
                            <Stack spacing={2}>
                                <Typography>
                                    Certain equipment in the Hackspace require
                                    members to complete an induction before use.
                                    These inductions will help you learn how to
                                    use the equipment safely and effectively,
                                    according to our risk assessments and safety
                                    protocols.
                                </Typography>
                                <Typography>
                                    <strong>
                                        If you have prior experience with a
                                        particular tool:
                                    </strong>{" "}
                                    You must still complete the induction to
                                    ensure you are familiar with our procedures
                                    and protocols. Due to insurance
                                    requirements, we are unable to accept or
                                    recognise external training or
                                    certifications of any kind.
                                </Typography>
                                <Typography>
                                    Our inductions are not intended to teach or
                                    develop new skills; for skills development
                                    please subscribe to our{" "}
                                    <a
                                        href="https://list.hacman.org.uk/c/events/12"
                                        target="_blank"
                                    >
                                        events forum
                                    </a>{" "}
                                    to find out about future workshops and
                                    classes.
                                </Typography>

                                {canSeeNonLiveCourses && (
                                    <Alert severity="info">
                                        <Typography variant="body2">
                                            As an admin, area coordinator, or
                                            maintainer, you can see courses that
                                            are not yet live. These appear with
                                            special indicators and are not
                                            visible to regular members until
                                            made live.
                                        </Typography>
                                    </Alert>
                                )}
                            </Stack>

                            {equipmentWithoutLiveCourse.length > 0 && (
                                <Alert severity="warning" sx={{ mt: 3 }}>
                                    <Stack spacing={2}>
                                        <Typography>
                                            The following pieces of equipment
                                            have not been migrated to the new
                                            induction courses system. Please
                                            visit their separate equipment pages
                                            for induction information.
                                        </Typography>
                                        <Stack spacing={0.5}>
                                            <TransitionGroup>
                                                {equipmentWithoutLiveCourse
                                                    .slice(
                                                        0,
                                                        equipmentExpanded
                                                            ? Number.POSITIVE_INFINITY
                                                            : 5
                                                    )
                                                    .map((equipment) => (
                                                        <Collapse
                                                            key={equipment.id}
                                                        >
                                                            <Typography
                                                                key={
                                                                    equipment.id
                                                                }
                                                            >
                                                                •{" "}
                                                                <Link
                                                                    href={
                                                                        equipment
                                                                            .urls
                                                                            .show
                                                                    }
                                                                    target="_blank"
                                                                    rel="noopener"
                                                                >
                                                                    {
                                                                        equipment.name
                                                                    }
                                                                </Link>
                                                                {equipment.room_display && (
                                                                    <Typography
                                                                        component="span"
                                                                        color="text.secondary"
                                                                        sx={{
                                                                            ml: 1,
                                                                        }}
                                                                    >
                                                                        (
                                                                        {
                                                                            equipment.room_display
                                                                        }
                                                                        )
                                                                    </Typography>
                                                                )}
                                                            </Typography>
                                                        </Collapse>
                                                    ))}
                                            </TransitionGroup>
                                        </Stack>

                                        <Button
                                            onClick={() =>
                                                setEquipmentExpanded(
                                                    !equipmentExpanded
                                                )
                                            }
                                            variant="text"
                                            color="secondary"
                                            sx={{ alignSelf: "flex-start" }}
                                        >
                                            {equipmentExpanded
                                                ? `Show less`
                                                : `Show more (${
                                                      equipmentWithoutLiveCourse.length -
                                                      5
                                                  })`}
                                        </Button>
                                    </Stack>
                                </Alert>
                            )}
                        </Paper>

                        <CourseFilters
                            filters={filters}
                            options={filterOptions}
                            counts={counts}
                            onFilterChange={updateFilter}
                        />
                    </Stack>
                    {Object.entries(groupedByRoom).map(
                        ([areaName, areaCourses]) => (
                            <CourseGroup
                                key={areaName}
                                title={areaName}
                                courses={areaCourses}
                                canSeeNonLiveCourses={canSeeNonLiveCourses}
                            />
                        )
                    )}

                    <CourseGroup
                        title="Ungrouped"
                        courses={ungroupedCourses}
                        canSeeNonLiveCourses={canSeeNonLiveCourses}
                    />
                </Stack>
            </Container>
        </>
    );
};

Index.layout = (page: React.ReactNode) => <MainLayout children={page} />;

export default Index;
