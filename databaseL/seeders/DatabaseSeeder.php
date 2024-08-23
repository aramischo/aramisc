<?php

namespace Database\Seeders;

use App\SmSchool;
use App\AramiscAcademicYear;
use Illuminate\Database\Seeder;
use Database\Seeders\SmSchoolSeeder;
use Database\Seeders\ContinentsTableSeeder;
use Database\Seeders\Lesson\SmTopicsTableSeeder;
use Database\Seeders\Admin\AramiscVisitorsTableSeeder;
use Database\Seeders\Exam\AramiscExamTypesTableSeeder;
use Database\Seeders\Lesson\SmLessonsTableSeeder;
use Database\Seeders\Fees\AramiscFeesAssignTableSeeder;
use Database\Seeders\Fees\AramiscFeesGroupsTableSeeder;
use Database\Seeders\Admin\AramiscComplaintsTableSeeder;
use Database\Seeders\Fees\AramiscFeesPaymentTableSeeder;
use Database\Seeders\Leave\SmLeaveTypesTableSeeder;
use Database\Seeders\Transport\AramiscRoutesTableSeeder;
use Database\Seeders\Academics\SmClassesTableSeeder;
use Database\Seeders\Fees\AramiscFeesDiscountTableSeeder;
use Database\Seeders\FrontendCMS\AramiscEventTableSeeder;
use Database\Seeders\Academics\AramiscSectionsTableSeeder;
use Database\Seeders\Academics\SmSubjectsTableSeeder;
use Database\Seeders\Communicate\SmSmTodoTableSeeder;
use Database\Seeders\Exam\AramiscExamSchedulesTableSeeder;
use Database\Seeders\FrontendCMS\AramiscCourseTableSeeder;
use Database\Seeders\HomeWork\SmHomeworksTableSeeder;
use Database\Seeders\Inventory\SmSupplierTableSeeder;
use Database\Seeders\Lesson\SmLessonPlansTableSeeder;
use Database\Seeders\Transport\SmVehiclesTableSeeder;
use Database\Seeders\Admin\SmPostalReceiveTableSeeder;
use Database\Seeders\Dormitory\AramiscRoomListsTableSeeder;
use Database\Seeders\Dormitory\AramiscRoomTypesTableSeeder;
use Database\Seeders\HumanResources\StaffsTableSeeder;
use Database\Seeders\Inventory\AramiscItemStoreTableSeeder;
use Database\Seeders\Academics\SmClassRoomsTableSeeder;
use Database\Seeders\Accounts\SmIncomeHeadsTableSeeder;
use Database\Seeders\Admin\SmPostalDispatchTableSeeder;
use Database\Seeders\Exam\AramiscExamAttendancesTableSeeder;
use Database\Seeders\Student\AramiscStudentGroupTableSeeder;
use Database\Seeders\Accounts\SmBankAccountsTableSeeder;
use Database\Seeders\Accounts\SmExpenseHeadsTableSeeder;
use Database\Seeders\Admin\SmContactMessagesTableSeeder;
use Database\Seeders\Fees\AramiscFeesCarryForwardTableSeeder;
use Database\Seeders\OnlineExam\SmOnlineExamTableSeeder;
use Database\Seeders\Student\SmOptionSubjectTableSeeder;
use Database\Seeders\FrontendCMS\SpeechSliderTableSeeder;
use Database\Seeders\Library\AramiscBookCategoriesTableSeeder;
use Database\Seeders\SystemSettings\AramiscHolidayTableSeeder;
use Database\Seeders\Academics\AramiscAcademicYearsTableSeeder;
use Database\Seeders\Communicate\SmNoticeBoardTableSeeder;
use Database\Seeders\Communicate\SmSendMessageTableSeeder;
use Database\Seeders\Exam\AramiscExamMarksRegistersTableSeeder;
use Database\Seeders\Fees\AramiscFeesAssignDiscountTableSeeder;
use Database\Seeders\Academics\SmAssignSubjectsTableSeeder;
use Database\Seeders\Admin\AramiscStudentCertificateTableSeeder;
use Database\Seeders\Communicate\SmEmailSmsLogsTableSeeder;
use Database\Seeders\Dormitory\AramiscDormitoryListsTableSeeder;
use Database\Seeders\FrontendCMS\SmPhotoGalleryTableSeeder;
use Database\Seeders\Inventory\AramiscItemCategoriesTableSeeder;
use Database\Seeders\Transport\SmAssignVehiclesTableSeeder;
use Modules\Fees\Database\Seeders\FmFeesInvoiceTableSeeder;
use Database\Seeders\HomeWork\SmHomeworkStudentsTableSeeder;
use Database\Seeders\OnlineExam\SmQuestionGroupsTableSeeder;
use Database\Seeders\Student\AramiscStudentAttendanceTableSeeder;
use Database\Seeders\Student\AramiscStudentCategoriesTableSeeder;
use Database\Seeders\HumanResources\SmDesignationsTableSeeder;
use Database\Seeders\UploadContent\SmUploadContentTableSeeder;
use Database\Seeders\Academics\SmAssignClassTeacherTableSeeder;
use Database\Seeders\Academics\SmClassRoutineUpdatesTableSeeder;
use Database\Seeders\HumanResources\SmStaffAttendancesTableSeeder;
use Database\Seeders\FrontSettings\SmBackgroundSettingsTableSeeder;
use Database\Seeders\FrontSettings\SmFrontendPermissionTableSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

//        $this->call(SmSchoolSeeder::class);
        $schools = SmSchool::query()->get();
       
        foreach ($schools as $school) {
            $params = [];
            $params['school_id'] = $school->id;

            $this->callWith(AramiscVisitorsTableSeeder::class, array_merge($params, ['count' => 10]));

            $this->callWith(SmDesignationsTableSeeder::class, array_merge($params, ['count' => 5]));
            $this->callWith(SmVehiclesTableSeeder::class, array_merge($params, ['count' => 5]));

            // $this->callWith(AramiscAcademicYearsTableSeeder::class, array_merge($params, ['count' => 1]));

            $this->callWith(SmExpenseHeadsTableSeeder::class, array_merge($params, ['count' => 10]));
            $this->callWith(SmIncomeHeadsTableSeeder::class, array_merge($params, ['count' => 10]));
            $this->callWith(SmBankAccountsTableSeeder::class, array_merge($params, ['count' => 10]));
            // $this->callWith(AramiscBookCategoriesTableSeeder::class, array_merge($params, ['count' => 10]));
            $this->callWith(SmContactMessagesTableSeeder::class, array_merge($params, ['count' => 10]));
            $this->callWith(AramiscDormitoryListsTableSeeder::class, array_merge($params, ['count' => 7]));
            $this->callWith(AramiscRoomTypesTableSeeder::class, array_merge($params, ['count' => 6]));
            $this->callWith(AramiscRoomListsTableSeeder::class, array_merge($params, ['count' => 10]));


            $this->callWith(AramiscStudentCategoriesTableSeeder::class, array_merge($params, ['count' => 6]));

            $this->callWith(StaffsTableSeeder::class, array_merge($params, ['count' => 5]));
            $this->callWith(SmBackgroundSettingsTableSeeder::class, array_merge($params, ['count' => 2]));
            $this->callWith(SmFrontendPermissionTableSeeder::class, array_merge($params, ['count' => 2]));
            $this->callWith(SmPhotoGalleryTableSeeder::class, array_merge($params, ['count' => 4]));
            $this->callWith(SpeechSliderTableSeeder::class, array_merge($params, ['count' => 3]));
            $this->callWith(AramiscEventTableSeeder::class, array_merge($params, ['count' => 4]));
            $this->callWith(AramiscCourseTableSeeder::class, array_merge($params, ['count' => 2]));


            $academicYears = AramiscAcademicYear::where('school_id', $school->id)->get();


            foreach ($academicYears as $academicYear) {
                $params['academic_id'] = $academicYear->id;
                $this->callWith(AramiscStudentGroupTableSeeder::class, array_merge($params, ['count' => 6]));
                $this->callWith(AramiscSectionsTableSeeder::class, array_merge($params, ['count' => 5]));

                $this->callWith(SmSubjectsTableSeeder::class, array_merge($params, ['count' => 10]));
                $this->callWith(SmClassesTableSeeder::class, array_merge($params, ['count' => 10]));

                // $this->callWith(AramiscStudentAttendanceTableSeeder::class, array_merge($params, ['count' => 10]));

                $this->callWith(AramiscRoutesTableSeeder::class, array_merge($params, ['count' => 10]));
                $this->callWith(SmClassRoomsTableSeeder::class, array_merge($params, ['count' => 10]));
                $this->callWith(AramiscComplaintsTableSeeder::class, array_merge($params, ['count' => 10]));
                $this->callWith(AramiscComplaintsTableSeeder::class, array_merge($params, ['count' => 10]));
                $this->callWith(SmEmailSmsLogsTableSeeder::class, array_merge($params, ['count' => 10]));


                 $this->callWith(AramiscExamTypesTableSeeder::class, array_merge($params, ['count' => 3]));
                // $this->callWith(SmStaffAttendancesTableSeeder::class, array_merge($params, ['count' => 1]));
                $this->callWith(SmAssignSubjectsTableSeeder::class, array_merge($params, ['count' => 1]));
                $this->callWith(SmAssignVehiclesTableSeeder::class, array_merge($params, ['count' => 1]));
                $this->callWith(SmClassRoutineUpdatesTableSeeder::class, array_merge($params, ['count' => 1]));
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

                $this->callWith(SmLeaveTypesTableSeeder::class, array_merge($params, ['count' => 5]));

                $this->callWith(SmLessonsTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(SmTopicsTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(SmLessonPlansTableSeeder::class, array_merge($params, ['count' => 5]));

                $this->callWith(SmHomeworksTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(SmHomeworkStudentsTableSeeder::class, array_merge($params, ['count' => 5]));

                $this->callWith(AramiscItemCategoriesTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(AramiscItemStoreTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(SmSupplierTableSeeder::class, array_merge($params, ['count' => 5]));

                $this->callWith(SmQuestionGroupsTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(SmQuestionGroupsTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(SmOnlineExamTableSeeder::class, array_merge($params, ['count' => 5]));

                $this->callWith(AramiscHolidayTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(SmNoticeBoardTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(SmPostalDispatchTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(SmPostalReceiveTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(SmSendMessageTableSeeder::class, array_merge($params, ['count' => 5]));

                $this->callWith(SmUploadContentTableSeeder::class, array_merge($params, ['count' => 5]));

                // $this->callWith(AramiscStudentCertificateTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(SmSmTodoTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(SmOptionSubjectTableSeeder::class, array_merge($params, ['count' => 5]));
                $this->callWith(SmAssignClassTeacherTableSeeder::class, array_merge($params, ['count' => 5]));

            }
        }
        
    }
}