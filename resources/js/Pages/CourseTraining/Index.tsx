import React from "react";
import {
    Container,
    Typography,
    Alert,
    Link,
} from "@mui/material";
import MainLayout from "../../Layouts/MainLayout";
import PageTitle from "../../Components/PageTitle";
import PendingSignOffSection from "../../Components/CourseTraining/PendingSignOffSection";
import TrainersManagementSection from "../../Components/CourseTraining/TrainersManagementSection";
import TrainedMembersSection from "../../Components/CourseTraining/TrainedMembersSection";
import { InductionResource, CourseResource, Member } from "../../types/resources";

type Props = {
    course: CourseResource;
    trainers: InductionResource[];
    trainedUsers: InductionResource[];
    usersPendingSignOff: InductionResource[];
    memberList: Member[];
    urls: {
        bulkTrain: string;
        back: string;
    };
};


const TrainingIndex = ({ 
    course, 
    trainers, 
    trainedUsers, 
    usersPendingSignOff,
    memberList,
    urls 
}: Props) => {

    return (
        <>
            <PageTitle title={`Training Management - ${course.name}`} />
            <Container sx={{ mt: 4 }}>
                <Typography
                    variant="body2"
                    color="text.secondary"
                    sx={{ mb: 3 }}
                >
                    <Link href="/courses" color="inherit" underline="hover">
                        Inductions
                    </Link>{" "}
                    /{" "}
                    <Link href={urls.back} color="inherit" underline="hover">
                        {course.name}
                    </Link>{" "}
                    / Training Management
                </Typography>

                {course.is_paused && (
                    <Alert severity="warning" sx={{ mb: 3 }}>
                        This course is currently paused. New inductions cannot be created.
                    </Alert>
                )}

                <PendingSignOffSection usersPendingSignOff={usersPendingSignOff} />
                
                <TrainersManagementSection trainers={trainers} />
                
                <TrainedMembersSection 
                    trainedUsers={trainedUsers}
                    memberList={memberList}
                    bulkTrainUrl={urls.bulkTrain}
                />
            </Container>
        </>
    );
};

TrainingIndex.layout = (page: React.ReactNode) => <MainLayout children={page} />;

export default TrainingIndex;