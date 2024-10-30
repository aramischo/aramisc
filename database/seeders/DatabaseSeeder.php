<?php

namespace Database\Seeders;

use App\AramiscSchool;
use App\AramiscAcademicYear;
use Illuminate\Database\Seeder;
use Database\Seeders\AramiscSchoolSeeder;
use Database\Seeders\ContinentsTableSeeder;
use Database\Seeders\Lesson\SmTopicsTableSeeder;
use Database\Seeders\Admin\AramiscVisitorsTableSeeder;
use Database\Seeders\Exam\AramiscExamTypesTableSeeder;
use Database\Seeders\Lesson\SmLessonsTableSeeder;
use Database\Seeders\Fees\AramiscFeesAssignTableSeeder;
use Database\Seeders\Fees\AramiscFeesGroupsTableSeeder;
use Database\Seeders\Admin\AramiscComplaintsTableSeeder;
use Database\Seeders\Fees\AramiscFeesPaymentTableSeeder;
use Database\Seeders\Leave\AramiscLeaveTypesTableSeeder;
use Database\Seeders\Transport\AramiscRoutesTableSeeder;
use Database\Seeders\Academics\AramiscClassesTableSeeder;
use Database\Seeders\Fees\AramiscFeesDiscountTableSeeder;
use Database\Seeders\FrontendCMS\AramiscEventTableSeeder;
use Database\Seeders\Academics\AramiscSectionsTableSeeder;
use Database\Seeders\Academics\AramiscSubjectsTableSeeder;
use Database\Seeders\Communicate\SmSmTodoTableSeeder;
use Database\Seeders\Exam\AramiscExamSchedulesTableSeeder;
use Database\Seeders\FrontendCMS\AramiscCourseTableSeeder;
use Database\Seeders\HomeWork\AramiscHomeworksTableSeeder;
use Database\Seeders\Inventory\AramiscSupplierTableSeeder;
use Database\Seeders\Lesson\SmLessonPlansTableSeeder;
use Database\Seeders\Transport\AramiscVehiclesTableSeeder;
use Database\Seeders\Admin\AramiscPostalReceiveTableSeeder;
use Database\Seeders\Dormitory\AramiscRoomListsTableSeeder;
use Database\Seeders\Dormitory\AramiscRoomTypesTableSeeder;
use Database\Seeders\HumanResources\StaffsTableSeeder;
use Database\Seeders\Inventory\AramiscItemStoreTableSeeder;
use Database\Seeders\Academics\AramiscClassRoomsTableSeeder;
use Database\Seeders\Accounts\AramiscIncomeHeadsTableSeeder;
use Database\Seeders\Admin\AramiscPostalDispatchTableSeeder;
use Database\Seeders\Exam\AramiscExamAttendancesTableSeeder;
use Database\Seeders\Student\AramiscStudentGroupTableSeeder;
use Database\Seeders\Accounts\AramiscBankAccountsTableSeeder;
use Database\Seeders\Accounts\AramiscExpenseHeadsTableSeeder;
use Database\Seeders\Admin\AramiscContactMessagesTableSeeder;
use Database\Seeders\Fees\AramiscFeesCarryForwardTableSeeder;
use Database\Seeders\OnlineExam\AramiscOnlineExamTableSeeder;
use Database\Seeders\Student\SmOptionSubjectTableSeeder;
use Database\Seeders\FrontendCMS\SpeechSliderTableSeeder;
use Database\Seeders\Library\AramiscBookCategoriesTableSeeder;
use Database\Seeders\SystemSettings\AramiscHolidayTableSeeder;
use Database\Seeders\Academics\AramiscAcademicYearsTableSeeder;
use Database\Seeders\Communicate\AramiscNoticeBoardTableSeeder;
use Database\Seeders\Communicate\AramiscSendMessageTableSeeder;
use Database\Seeders\Exam\AramiscExamMarksRegistersTableSeeder;
use Database\Seeders\Fees\AramiscFeesAssignDiscountTableSeeder;
use Database\Seeders\Academics\AramiscAssignSubjectsTableSeeder;
use Database\Seeders\Admin\AramiscStudentCertificateTableSeeder;
use Database\Seeders\Communicate\AramiscEmailSmsLogsTableSeeder;
use Database\Seeders\Dormitory\AramiscDormitoryListsTableSeeder;
use Database\Seeders\FrontendCMS\SmPhotoGalleryTableSeeder;
use Database\Seeders\Inventory\AramiscItemCategoriesTableSeeder;
use Database\Seeders\Transport\AramiscAssignVehiclesTableSeeder;
use Modules\Fees\Database\Seeders\FmFeesInvoiceTableSeeder;
use Database\Seeders\HomeWork\AramiscHomeworkStudentsTableSeeder;
use Database\Seeders\OnlineExam\AramiscQuestionGroupsTableSeeder;
use Database\Seeders\Student\AramiscStudentAttendanceTableSeeder;
use Database\Seeders\Student\AramiscStudentCategoriesTableSeeder;
use Database\Seeders\HumanResources\AramiscDesignationsTableSeeder;
use Database\Seeders\UploadContent\SmUploadContentTableSeeder;
use Database\Seeders\Academics\AramiscAssignClassTeacherTableSeeder;
use Database\Seeders\Academics\AramiscClassRoutineUpdatesTableSeeder;
use Database\Seeders\HumanResources\AramiscStaffAttendancesTableSeeder;
use Database\Seeders\FrontSettings\AramiscBackgroundSettingsTableSeeder;
use Database\Seeders\FrontSettings\AramiscFrontendPermissionTableSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

//        $this->call(AramiscSchoolSeeder::class);
        $schools = AramiscSchool::query()->get();
       
        foreach ($schools as $school) {
            $params = [];
            $params['school_id'] = $school->id;

            $this->callWith(AramiscVisitorsTableSeeder::class, array_merge($params, ['count' => 10]));

            $this->callWith(AramiscDesignationsTableSeeder::class, array_merge($params, ['count' => 5]));
            $this->callWith(AramiscVehiclesTableSeeder::class, array_merge($params, ['count' => 5]));

            // $this->callWith(AramiscAcademicYearsTableSeeder::class, array_merge($params, ['count' => 1]));

            $this->callWith(AramiscExpenseHeadsTableSeeder::class, array_merge($params, ['count' => 10]));
            $this->callWith(AramiscIncomeHeadsTableSeeder::class, array_merge($params, ['count' => 10]));
            $this->callWith(AramiscBankAccountsTableSeeder::class, array_merge($params, ['count' => 10]));
            // $this->callWith(AramiscBookCategoriesTableSeeder::class, array_merge($params, ['count' => 10]));
            $this->callWith(AramiscContactMessagesTableSeeder::class, array_merge($params, ['count' => 10]));
            $this->callWith(AramiscDormitoryListsTableSeeder::class, array_merge($params, ['count' => 7]));
            $this->callWith(AramiscRoomTypesTableSeeder::class, array_merge($params, ['count' => 6]));
            $this->callWith(AramiscRoomListsTableSeeder::class, array_merge($params, ['count' => 10]));


            $this->callWith(AramiscStudentCategoriesTableSeeder::class, array_merge($params, ['count' => 6]));

            $this->callWith(StaffsTableSeeder::class, array_merge($params, ['count' => 5]));
            $this->callWith(AramiscBackgroundSettingsTableSeeder::class, array_merge($params, ['count' => 2]));
            $this->callWith(AramiscFrontendPermissionTableSeeder::class, array_merge($params, ['count' => 2]));
            $this->callWith(SmPhotoGalleryTableSeeder::class, array_merge($params, ['count' => 4]));
            $this->callWith(SpeechSliderTableSeeder::class, array_merge($params, ['count' => 3]));
            $this->callWith(AramiscEventTableSeeder::class, array_merge($params, ['count' => 4]));
            $this->callWith(AramiscCourseTableSeeder::class, array_merge($params, ['count' => 2]));


            $academicYears = AramiscAcademicYear::where('school_id', $school->id)->get();


            foreach ($academicYears as $academicYear) {
                $params['academic_id'] = $academicYear->id;
                $this->callWith(AramiscStudentGroupTableSeeder::class, array_merge($params, ['count' => 6]));
                $this->callWith(AramiscSectionsTableSeeder::class, array_merge($params, ['count' => 5]));

                $this->callWith(AramiscSubjectsTableSeeder::class, array_merge($params, ['count' => 10]));
                $this->callWith(AramiscClassesTableSeeder::class, array_merge($params, ['count' => 10]));

                // $this->callWith(AramiscStudentAttendanceTableSeeder::class, array_merge($params, ['count' => 10]));

                $this->callWith(AramiscRoutesTableSeeder::class, array_merge($params, ['count' => 10]));
                $this->callWith(AramiscClassRoomsTableSeeder::class, array_merge($params, ['count' => 10]));
                $this->callWith(AramiscComplaintsTableSeeder::class, array_merge($params, ['count' => 10]));
                $this->callWith(AramiscComplaintsTableSeeder::class, array_merge($params, ['count' => 10]));
                $this->callWith(AramiscEmailSmsLogsTableSeeder::class, array_merge($params, ['count' => 10]));


                 $this->callWith(AramiscExamTypesTableSeeder::class, array_merge($params, ['count' => 3]));
                // $this->callWith(AramiscStaffAttendancesTableSeeder::class, array_merge($params, ['count' => 1]));
                $this->callWith(AramiscAssignSubjectsTableSeeder::class, array_merge($params, ['count' => 1]));
                $this->callWith(AramiscAssignVehiclesTableSeeder::class, array_merge($params, ['count' => 1]));
                $this->callWith(AramiscClassRoutineUpdatesTableSeeder::class, array_merge($params, ['count' => 1]));
                $this->callWith(AramiscExamSchedulesTableSeeder::class, array_merge($params, ['count' => 1]));
                $this->callWith(AramiscExamAttendancesTableSeeder::class, array_merge($params, ['count' => 1]));
                // $this->callWith(AramiscExamMarksRegistersTableSeeder::class, array_merge($params, ['count' => 1]));

                $this->callWith(AramiscFeesGroupsTableSeeder::class, array_merge($params, ['count' => 5]));

                $this->callWith(AramiscFeesDiscountTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(AramiscFeesAssignDiscountTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(AramiscFeesAssignTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(AramiscFeesPaymentTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(AramiscFeesCarryForwardTableSeeder::class, array_merge($params, ['count' => 5]));

                $this->callWith(FmFeesInvoiceTableSeeder::class, array_merge($params, ['count' => 5]));

                $this->callWith(AramiscLeaveTypesTableSeeder::class, array_merge($params, ['count' => 5]));

                $this->callWith(SmLessonsTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(SmTopicsTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(SmLessonPlansTableSeeder::class, array_merge($params, ['count' => 5]));

                $this->callWith(AramiscHomeworksTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(AramiscHomeworkStudentsTableSeeder::class, array_merge($params, ['count' => 5]));

                $this->callWith(AramiscItemCategoriesTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(AramiscItemStoreTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(AramiscSupplierTableSeeder::class, array_merge($params, ['count' => 5]));

                $this->callWith(AramiscQuestionGroupsTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(AramiscQuestionGroupsTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(AramiscOnlineExamTableSeeder::class, array_merge($params, ['count' => 5]));

                $this->callWith(AramiscHolidayTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(AramiscNoticeBoardTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(AramiscPostalDispatchTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(AramiscPostalReceiveTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(AramiscSendMessageTableSeeder::class, array_merge($params, ['count' => 5]));

                $this->callWith(SmUploadContentTableSeeder::class, array_merge($params, ['count' => 5]));

                // $this->callWith(AramiscStudentCertificateTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(SmSmTodoTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(SmOptionSubjectTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(AramiscAssignClassTeacherTableSeeder::class, array_merge($params, ['count' => 5]));

            }
        }
        
    }
}