<?php

namespace Database\Seeders;

use App\User;
use App\AramiscBook;
use App\AramiscItem;
use App\AramiscNews;
use App\AramiscPage;
use App\AramiscClass;
use App\AramiscRoute;
use App\AramiscStaff;
use App\AramiscStyle;
use App\AramiscCourse;
use App\AramiscParent;
use App\AramiscSchool;
use App\AramiscSection;
use App\AramiscStudent;
use App\AramiscSubject;
use App\AramiscVehicle;
use App\AramiscVisitor;
use App\AramiscWeekend;
use Carbon\Carbon;
use App\AramiscExamType;
use App\AramiscFeesType;
use App\AramiscHomework;
use App\AramiscNewsPage;
use App\AramiscRoomList;
use App\AramiscRoomType;
use App\AramiscSupplier;
use App\AramiscAboutPage;
use App\AramiscAddIncome;
use App\AramiscBaseSetup;
use App\AramiscBookIssue;
use App\AramiscClassRoom;
use App\AramiscComplaint;
use App\AramiscExamSetup;
use App\AramiscFeesGroup;
use App\AramiscItemStore;
use App\AramiscLeaveType;
use App\AramiscAddExpense;
use App\AramiscCoursePage;
use App\AramiscCustomLink;
use App\AramiscFeesMaster;
use App\AramiscMarksGrade;
use App\AramiscSetupAdmin;
use App\AramiscBankAccount;
use App\AramiscDesignation;
use App\AramiscLeaveDefine;
use App\AramiscTestimonial;
use App\LibrarySubject;
use App\AramiscAcademicYear;
use App\AramiscBookCategory;
use App\AramiscClassTeacher;
use App\AramiscFeesDiscount;
use App\AramiscItemCategory;
use App\AramiscLeaveRequest;
use App\AramiscNewsCategory;
use App\AramiscStudentGroup;
use App\AramiscAssignSubject;
use App\AramiscAssignVehicle;
use App\AramiscDormitoryList;
use App\AramiscLibraryMember;
use App\AramiscPostalReceive;
use App\AramiscAdmissionQuery;
use App\AramiscChartOfAccount;
use App\AramiscContactMessage;
use App\AramiscCourseCategory;
use App\AramiscExamAttendance;
use App\AramiscPaymentMethhod;
use App\AramiscPostalDispatch;
use App\AramiscGeneralSettings;
use App\AramiscHomePageSetting;
use App\AramiscHomeworkStudent;
use App\AramiscHumanDepartment;
use App\AramiscStaffAttendence;
use App\AramiscStudentCategory;
use App\AramiscBackgroundSetting;
use App\AramiscHeaderMenuManager;
use App\AramiscHrPayrollGenerate;
use App\AramiscStudentAttendance;
use App\AramiscAssignClassTeacher;
use App\AramiscClassRoutineUpdate;
use App\AramiscStudentCertificate;
use App\AramiscTeacherUploadContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Lesson\Entities\AramiscLesson;
use Modules\RolePermission\Entities\AramiscRole;
use Modules\RolePermission\Entities\AramiscPermissionAssign;
use Modules\Saas\Entities\SaasSchoolModulePermissionAssign;

class AramiscSchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        AramiscSchool::factory()->times(1)->create()->each(
            function ($school) {
                //school admin user
                User::factory()->times(1)->create([
                    'school_id' => $school->id,
                    'username' => $school->email,
                    'email' => $school->email,
                    'role_id' => 1,
                ])->each(function ($user) {
                    AramiscStaff::factory()->times(1)->create([
                        'role_id' => 1,
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'full_name' => $user->full_name,
                    ]);
                });
                AramiscDesignation::factory()->times(10)->create([
                    'school_id' => $school->id,
                ]);
                AramiscHumanDepartment::factory()->times(10)->create([
                    'school_id' => $school->id,
                ]);
                AramiscBaseSetup::factory()->times(10)->create([
                    'school_id' => $school->id,
                ]);

                AramiscAcademicYear::factory()->times(1)->create([
                    'school_id' => $school->id,
                ])->each(function ($academic_year) use ($school) {
                    $school_academic = [
                        'school_id' => $school->id,
                        'academic_id' => $academic_year->id,
                    ];
                    AramiscGeneralSettings::factory()->times(1)->create(array_merge([
                        'session_id' => $academic_year->id,
                        'email' => $school->email,
                        'school_name' => $school->school_name,
                    ],$school_academic));
                    $aramiscRoute=AramiscRoute::factory()->times(5)->create($school_academic);
                    AramiscSetupAdmin::factory()->times(20)->create($school_academic);
                    AramiscStudentCategory::factory()->times(10)->create($school_academic);
                    AramiscStudentGroup::factory()->times(10)->create($school_academic);
                    //staff user
                    User::factory()->times(10)->create([
                        'school_id' => $school->id,
                    ])->each(function ($userStaff) use ($school,$school_academic,$aramiscRoute) {
                        $staffs=AramiscStaff::factory()->times(1)->create([
                            'user_id' => $userStaff->id,
                            'email' => $userStaff->email,
                            'full_name' => $userStaff->full_name,
                            'school_id' => $school->id,
                        ])->each(function ($staffs) use ($school_academic,$aramiscRoute) {
                            if ($staffs->role_id == 9) {
                                AramiscVehicle::factory()->times(1)->create(array_merge([
                                   'driver_id' =>$staffs->id, 
                                ],$school_academic))->each(function ($aramiscVehicle) use ($school_academic,$aramiscRoute){
                                    AramiscAssignVehicle::factory()->times(1)->create(array_merge([
                                        'vehicle_id' =>$aramiscVehicle->id,
                                        'route_id' =>null,
                                    ],$school_academic));
                                });
                            }
                            AramiscHrPayrollGenerate::factory()->times(1)->create(array_merge([
                                'staff_id'=>$staffs->id,
                            ],$school_academic));
                        });
                    });
                    $teacher_id = AramiscStaff::where('role_id', 4)->where('school_id', $school->id)->first()->id;
                    //subject seeder
                    $sections = AramiscSection::factory()->times(5)->create($school_academic);
                    $subjects = AramiscSubject::factory()->times(2)->create($school_academic);
                    //Class room Seeder
                    $rooms = AramiscClassRoom::factory()->times(10)->create($school_academic);
                    //class seeder
                    $classes=AramiscClass::factory()->times(2)->create($school_academic)->each(function ($class) use ($sections, $school_academic, $school, $subjects, $teacher_id, $rooms, $academic_year) {
                        $class_sections = [];
                        foreach ($sections as $section) {
                            $class_sections[] = array_merge(['class_id' => $class->id, 'section_id' => $section->id], $school_academic);
                        }

                        $class_sections = $class->classSection()->createMany($class_sections)->each(function ($classSections) use ($school_academic, $school, $teacher_id, $rooms, $subjects, $academic_year) {
                           
                            AramiscAssignClassTeacher::factory()->times(1)->create($school_academic)->each(function ($assignClassTeacher) use ($school_academic, $teacher_id) {
                                AramiscClassTeacher::factory()->times(1)->create(array_merge([
                                    'assign_class_teacher_id' => $assignClassTeacher->id,
                                    'teacher_id' => $teacher_id,
                                ], $school_academic));
                            });

                            AramiscTeacherUploadContent::factory()->times(10)->create(array_merge([
                                'class' => $classSections->class_id,
                                'section' => $classSections->section_id,
                            ], $school_academic));

                        }); /* data insert into aramisc_class_section_table */

                        //assign subject
                        foreach ($class_sections as $data) {
                            foreach ($subjects as $subject) {
                                DB::table('aramisc_assign_subjects')->insert(array_merge([
                                    'class_id' => $data->class_id,
                                    'section_id' => $data->section_id,
                                    'teacher_id' => $teacher_id,
                                    'subject_id' => $subject->id,
                                ], $school_academic));
                            }

                            //student  & parent insert

                            for ($i = 1; $i <= 5; $i++) {                                
                                User::factory()->times(1)->create([
                                    'role_id' =>2,
                                    'email'=>'student_'.$data->class_id.'_'.$data->section_id.'_'.$i.'@aramiscdu.com', 
                                    'username'=>'student_'.$data->class_id.'_'.$data->section_id.'_'.$i.'@aramiscdu.com',
                                    'school_id' =>$school->id,
                                ]);
                                User::factory()->times(1)->create([
                                    'role_id' =>3,
                                    'email'=>'guardian_'.$data->class_id.'_'.$data->section_id.'_'.$i.'@aramiscdu.com', 
                                    'username'=>'guardian_'.$data->class_id.'_'.$data->section_id.'_'.$i.'@aramiscdu.com',
                                    'school_id' =>$school->id,
                                ]);
                                $studentUser=User::where('school_id',$school->id)->where('role_id',2)->latest('id')->first();
                                $parentUser=User::where('school_id',$school->id)->where('role_id',3)->latest('id')->first();

                                AramiscParent::factory()->times(1)->create(array_merge([
                                    'user_id' => $parentUser->id,
                                    'guardians_email' => $parentUser->email,                                 
                                ], $school_academic));

                                $parent=AramiscParent::where('school_id',$school->id)->where('academic_id',$academic_year->id)->latest('id')->first();

                               AramiscStudent::factory()->times(1)->create(array_merge([
                                   
                                    'session_id' => $academic_year->id,
                                    'user_id' => $studentUser->id,
                                    'parent_id' => $parent->id,
                                    'class_id' => $data->class_id,
                                    'section_id' => $data->section_id,
                                    'email' => 'student_'.$data->class_id.'_'.$data->section_id.'_'.$i.'@aramiscdu.com',                                
                                ], $school_academic));                                       
                    
                                
                            }
                        }
                        //end assign subject
                        AramiscAdmissionQuery::factory()->times(10)->create(array_merge(['class' => $class->id], $school_academic));

                        //end class id
                    });
                    $classes = AramiscClass::where('school_id', $school->id)->get(['id', 'class_name']);
                    //class routine
                    $classSectionSubjects=AramiscAssignSubject::where('school_id',$school->id)->where('academic_id',$academic_year->id)->get();
                    AramiscWeekend::factory()->times(7)->create($school_academic)->each(function ($day) use ($school_academic,$classSectionSubjects) {
                        
                        foreach($classSectionSubjects as  $classSectionSubject){                  
                                                     
                         AramiscClassRoutineUpdate::factory()->times(1)->create(array_merge([
                            'day' => $day->id,
                            'class_id' => $classSectionSubject->class_id,
                            'section_id' => $classSectionSubject->section_id,
                            'subject_id' => $classSectionSubject->subject_id,
                        ], $school_academic));  
                
                        
                    }

 
                    });
                    $classSectionSubjects=AramiscAssignSubject::where('school_id',$school->id)->where('academic_id',$academic_year->id)->get();
                    foreach($classSectionSubjects as  $classSectionSubject){ 
                        $s = new AramiscHomework();
                        $s->class_id =  $classSectionSubject->class_id;
                        $s->section_id = $classSectionSubject->section_id;
                        $s->subject_id = $classSectionSubject->subject_id;
                        $s->homework_date = date('Y-m-d');
                        $s->submission_date = date('Y-m-d');
                        $s->evaluation_date = date('Y-m-d');
                        $s->evaluated_by = 1;
                        $s->marks = rand(10, 15);
                        $s->description = 'Test';
                        $s->created_at = date('Y-m-d h:i:s');
                        $s->school_id = $school->id;
                        $s->academic_id = $academic_year->id;
                        $s->save();
                     }
                    //end class routine
                    $homeworks = AramiscHomework::where('school_id',$school->id)->first();
                    $students = AramiscStudent::where('school_id',$school->id)->get(['id','user_id']);
                    foreach ($students as $student) {                      
                      
                            $s = new AramiscHomeworkStudent();
                            $s->student_id = $student->id;
                            $s->homework_id = $homeworks->id;
                            $s->marks = rand(5, 10);
                            $s->teacher_comments = 'faker';
                            $s->complete_status = 'C';
                            $s->school_id = $school->id;
                            $s->academic_id = $academic_year->id;
                            $s->created_at = date('Y-m-d h:i:s');
                            $s->save();
                      
                        $AramiscLibraryMember = new AramiscLibraryMember();
                        $AramiscLibraryMember->member_ud_id = rand(10,100000000000000); 
                        $AramiscLibraryMember->member_type = rand(1,8);     
                        $AramiscLibraryMember->student_staff_id = $student->id;
                        $AramiscLibraryMember->active_status = 1;                      
                        $AramiscLibraryMember->school_id = $school->id;
                        $AramiscLibraryMember->academic_id = $academic_year->id;
                        $AramiscLibraryMember->created_at = date('Y-m-d h:i:s');
                        $AramiscLibraryMember->save();
                    }
                    //admission Query
                    AramiscAdmissionQuery::factory()->times(10)->create($school_academic);
                    //visitor
                    AramiscVisitor::factory()->times(10)->create($school_academic);
                    AramiscComplaint::factory()->times(10)->create($school_academic);
                    AramiscPostalReceive::factory()->times(10)->create($school_academic);
                    AramiscPostalDispatch::factory()->times(10)->create($school_academic);
                    //phonelog
                    AramiscPostalDispatch::factory()->times(10)->create($school_academic);
                    AramiscStudentCertificate::factory()->times(1)->create($school_academic);
                    //student id card


                    //fees Collection
                    $discount = AramiscFeesDiscount::factory()->times(10)->create($school_academic);
                    AramiscFeesGroup::factory()->times(5)->create($school_academic)->each(function ($feesGroup) use ($school_academic) {
                        AramiscFeesType::factory()->times(5)->create(array_merge([
                            'fees_group_id' => $feesGroup->id,
                        ], $school_academic))->each(function ($feesTypes) use ($school_academic) {
                            AramiscFeesMaster::factory()->times(1)->create(array_merge([
                                'fees_group_id' => $feesTypes->fees_group_id,
                                'fees_type_id' => $feesTypes->id,
                            ], $school_academic));
                        });
                    });

                    //end fess collection

                    //
                    //Examination
                    $assignSubjects = AramiscAssignSubject::where('school_id', $school->id)->where('academic_id', $academic_year->id)->get();
                    AramiscMarksGrade::factory()->times(7)->create($school_academic);
                    AramiscExamType::factory()->times(3)->create($school_academic)->each(function ($examTerm) use ($assignSubjects, $school, $academic_year) {
                        foreach ($assignSubjects as $classSectionSubject) {
                            $s = new AramiscExamSetup();
                            $s->class_id = $classSectionSubject->class_id;
                            $s->section_id = $classSectionSubject->section_id;
                            $s->subject_id = $classSectionSubject->subject_id;
                            $s->exam_term_id = $examTerm->id;
                            $s->exam_title = 'Exam';
                            $s->exam_mark = 100;
                            $s->school_id = $school->id;
                            $s->academic_id = $academic_year->id;
                            $s->save();

                            $store= new AramiscExamAttendance();
                            $store->exam_id=$examTerm->id;
                            $store->subject_id=$classSectionSubject->subject_id;
                            $store->class_id=$classSectionSubject->class_id;
                            $store->section_id=$classSectionSubject->section_id;                           
                            $store->created_at = date('Y-m-d h:i:s');
                            $store->school_id = $school->id;
                            $store->academic_id = $academic_year->id;
                            $store->save();
                        }

                    });

                    $days = cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
                    $students = AramiscStudent::where('school_id', $school->id)->where('academic_id', $academic_year->id)->get(['id', 'user_id']);
                    $staffs = AramiscStaff::whereIn('role_id', [4, 5, 6, 7, 8, 9])->where('school_id', $school->id)->get(['id', 'user_id']);

                    for ($i = 1; $i <= $days; $i++) {
                        foreach ($students as $student) {
                            if ($i <= 9) {
                                $d = '0' . $i;
                            }
                            $date = date('Y') . '-' . date('m') . '-' . $d;
                            $sa = new AramiscStudentAttendance();
                            $sa->student_id = $student->id;
                            $sa->attendance_type = 'P';
                            $sa->notes = 'Sample Attendance for Student';
                            $sa->attendance_date = $date;
                            $sa->school_id = $school->id;
                            $sa->academic_id = $academic_year->id;
                            $sa->save();
                        }
                        foreach ($staffs as $staff) {
                            if ($i <= 9) {
                                $d = '0' . $i;
                            }
                            $date = date('Y') . '-' . date('m') . '-' . $d;

                            $sa = new AramiscStaffAttendence();
                            $sa->staff_id = $staff->id;
                            $sa->attendence_type = 'P';
                            $sa->notes = 'Sample Attendance for Staff';
                            $sa->attendence_date = $date;
                            $sa->school_id = $school->id;
                            $sa->academic_id = $academic_year->id;
                            $sa->save();
                        }
                    }
                    $rules = AramiscRole::where('active_status', '=', '1')->where('id', '!=', 1) /* ->where('id', '!=', 2) */->where('id', '!=', 3)->where('id', '!=', 10)->get();
                    $staffs = AramiscStaff::where('role_id', 4)->where('school_id', $school->id)->get();
                    AramiscLeaveType::factory()->times(5)->create($school_academic)->each(function ($leaveTypes) use ($rules, $school, $academic_year, $staffs) {
                        foreach ($rules as $key => $value) {
                            $users = User::where('role_id', $value->id)->get();
                            foreach ($users as $user) {
                                $store = new AramiscLeaveDefine();
                                $store->role_id = $value->id;
                                $store->user_id = $user->id;
                                $store->type_id = $leaveTypes->id;
                                $store->days = $leaveTypes->total_days;
                                $store->school_id = $school->id;
                                $store->academic_id = $academic_year->id;
                                $store->save();
                            }
                        }
                        foreach ($staffs as $staff) {

                            $store = new AramiscLeaveRequest();
                            $store->type_id = $leaveTypes->id;
                            $store->leave_define_id = 1;
                            $store->staff_id = $staff->id;
                            $store->role_id = 4;
                            $store->apply_date = Carbon::now()->format('Y-m-d');
                            $store->leave_from = Carbon::now()->format('Y-m-d');
                            $store->leave_to = Carbon::now()->addDays(2)->format('Y-m-d');
                            $store->reason = 'Seeder Leave';
                            $store->note = 'Seeder Leave';
                            $store->file = "public/uploads/leave_request/sample.pdf";
                            $store->approve_status = "P";
                            $store->school_id = $school->id;
                            $store->academic_id = $academic_year->id;
                            $store->save();
                        }

                    });

                    //Accounts
                    AramiscBankAccount::factory()->times(10)->create($school_academic);
                    AramiscChartOfAccount::factory()->times(5)->create($school_academic)->each(function ($aramiscChartOfAccount) use($school_academic){
                        if($aramiscChartOfAccount=='I'){
                            AramiscAddIncome::factory()->times(5)->create(array_merge([
                                'income_head_id'=>$aramiscChartOfAccount->id,
                            ],$school_academic));
                        }
                        if($aramiscChartOfAccount=='E'){
                            AramiscAddExpense::factory()->times(5)->create(array_merge([
                                'income_head_id'=>$aramiscChartOfAccount->id,
                            ],$school_academic));
                        }
                    });


                    AramiscRoomType::factory()->times(5)->create($school_academic);
                    $aramiscRoomTypes=AramiscRoomType::where('school_id',$school->id)->where('academic_id',$academic_year->id)->get();
                    AramiscDormitoryList::factory()->times(5)->create($school_academic)->each(function ($AramiscDormitoryLists) use ($aramiscRoomTypes,$school_academic){
                        foreach($aramiscRoomTypes as $room) {
                            AramiscRoomList::factory()->times(1)->create(array_merge([
                                'dormitory_id'=>$AramiscDormitoryLists->id,
                                'room_type_id'=>$room->id,
                            ],$school_academic));
                        }
                    });
                    AramiscItemCategory::factory()->times(10)->create($school_academic)->each(function ($itemCategory) use($school_academic){
                        AramiscItem::factory()->times(5)->create(array_merge([
                            'item_category_id' =>$itemCategory->id,
                        ],$school_academic));
                    });
                    AramiscItemStore::factory()->times(10)->create($school_academic);
                    AramiscSupplier::factory()->times(10)->create($school_academic);
                    AramiscBookCategory::factory()->times(10)->create($school_academic)->each(function($bookCategory) use ($school_academic,$students,$school,$academic_year){
                        LibrarySubject::factory()->times(10)->create(array_merge([
                            'sb_category_id' => $bookCategory->id,
                        ],$school_academic));
                        AramiscBook::factory()->times(1)->create(array_merge([
                                'book_category_id' => $bookCategory->id,
                            ],$school_academic))->each(function ($smBooks) use ($students,$school,$academic_year){
                                foreach ($students as $student) {
                                    $store = new AramiscBookIssue();
                                    $store->member_id = $student->id;
                                    $store->book_id = $smBooks->id;
                                    $store->quantity = rand(1, 5);
                                    $store->given_date = Carbon::now()->format('Y-m-d');                                   
                                    $store->issue_status = "I"; 
                                    $store->school_id = $school->id;
                                    $store->academic_id = $academic_year->id;
                                    $store->save();
                                }
                            });
                        
                    });


                });
                //end academic id
                AramiscHomePageSetting::factory()->times(1)->create([
                    'school_id' =>$school->id,
                ]);
                AramiscNewsPage::factory()->times(1)->create([
                    'school_id' =>$school->id,
                ]);
                AramiscNewsCategory::factory()->times(1)->create([
                    'school_id' =>$school->id,
                ])->each(function($newsCategory) use ($school){
                    AramiscNews::factory()->times(1)->create([
                        'category_id' =>$newsCategory->id,
                        'school_id' =>$school->id,
                    ]);
                });
                AramiscCoursePage::factory()->times(1)->create([
                    'school_id' =>$school->id,
                ]);
                AramiscCourseCategory::factory()->times(1)->create([
                    'school_id' =>$school->id,
                ])->each(function ($courseCategory) use ($school){
                    AramiscCourse::factory()->times(1)->create([
                        'category_id' =>$courseCategory->id,
                        'school_id' =>$school->id,
                    ]);
                });

                AramiscTestimonial::factory()->times(1)->create([
                    'school_id' =>$school->id,
                ]);
                AramiscBackgroundSetting::factory()->times(1)->create([
                    'school_id' =>$school->id,
                ]);
                AramiscContactMessage::factory()->times(1)->create([
                    'school_id' =>$school->id,
                ]);
                AramiscAboutPage::factory()->times(1)->create([
                    'school_id' =>$school->id,
                ]);
                


                if (moduleStatusCheck('RazorPay') == true) {
                    $payment_methods = ['Cash', 'Cheque', 'Bank', 'Stripe', 'Paystack', 'PayPal', 'RazorPay'];
                    DB::table('aramisc_payment_gateway_settings')->insert([
                        [
                            'gateway_name' => 'RazorPay',
                            'gateway_username' => 'demo@gmail.com',
                            'gateway_password' => '12334589',
                            'gateway_client_id' => '',
                            'gateway_secret_key' => '',
                            'gateway_publisher_key' => '',
                            'school_id' => $school->id,
                        ],
        
                    ]);
                } else {
                    $payment_methods = ['Cash', 'Cheque', 'Bank', 'Stripe', 'Paystack', 'PayPal'];
                }
        
                foreach ($payment_methods as $payment_method) {
                    $method = new AramiscPaymentMethhod();
                    $method->method = $payment_method;
                    $method->type = 'System';
                    $method->school_id = $school->id;
                    $method->save();
                }
        
                // End if razorpay enable
        
                DB::table('aramisc_payment_gateway_settings')->insert([
                    [
                        'gateway_name' => 'Stripe',
                        'gateway_username' => 'demo@strip.com',
                        'gateway_password' => '12334589',
                        'gateway_client_id' => '',
                        'gateway_secret_key' => 'AVZdghanegaOjiL6DPXd0XwjMGEQ2aXc58z1-isWmBFnw1h2j',
                        'gateway_secret_word' => 'AVZdghanegaOjiL6DPXd0XwjMGEQ2aXc58z1',
                        'school_id' => $school->id,
                    ],
                ]);
        
                DB::table('aramisc_payment_gateway_settings')->insert([
                    [
                        'gateway_name' => 'Paystack',
                        'gateway_username' => 'demo@gmail.com',
                        'gateway_password' => '12334589',
                        'gateway_client_id' => '',
                        'gateway_secret_key' => 'sk_live_2679322872013c265e161bc8ea11efc1e822bce1',
                        'gateway_publisher_key' => 'pk_live_e5738ce9aade963387204f1f19bee599176e7a71',
                        'school_id' => $school->id,
                    ],
        
                ]);
        
                DB::table('aramisc_payment_gateway_settings')->insert([
                    [
                        'gateway_name' => 'PayPal',
                        'gateway_username' => 'demo@paypal.com',
                        'gateway_password' => '12334589',
                        'gateway_client_id' => 'AaCPtpoUHZEXCa3v006nbYhYfD0HIX-dlgYWlsb0fdoFqpVToATuUbT43VuUE6pAxgvSbPTspKBqAF0x69',
                        'gateway_secret_key' => 'EJ6q4h8w0OanYO1WKtNbo9o8suDg6PKUkHNKv-T6F4APDiq2e19OZf7DfpL5uOlEzJ_AMgeE0L2PtTEj69',
                        'gateway_publisher_key' => '',
                        'school_id' => $school->id,
                    ],
        
                ]);
        
                DB::table('aramisc_payment_gateway_settings')->insert([
                    [
                        'gateway_name' => 'Bank',
                        'school_id' => $school->id,
                    ],
        
                ]);
        
                DB::table('aramisc_payment_gateway_settings')->insert([
                    [
                        'gateway_name' => 'Cheque',
                        'school_id' => $school->id,
                    ],
        
                ]);
        
                DB::table('aramisc_sms_gateways')->insert([
                    [
                        'gateway_name' => 'Clickatell',
                        'clickatell_username' => 'demo1',
                        'clickatell_password' => '122334',
                        'school_id' => $school->id,
                    ],
                    [
                        'gateway_name' => 'Twilio',
                        'clickatell_username' => 'demo2',
                        'clickatell_password' => '12336',
                        'school_id' => $school->id,
                    ],
                    [
                        'gateway_name' => 'Msg91',
                        'clickatell_username' => 'demo3',
                        'clickatell_password' => '23445',
                        'school_id' => $school->id,
                    ],
                ]);
                
                for ($i = 2; $i <= 21; ++$i) {

                    $assign = new SaasSchoolModulePermissionAssign();
                    $assign->module_id = $i;
                    $assign->created_by = 1;
                    $assign->updated_by = 1;
                    $assign->school_id = $school->id;
                    $assign->save();
                }
        
                for ($j = 1; $j <= 541; $j++) {
                    $permission = new AramiscPermissionAssign();
                    $permission->module_id = $j;
                    $permission->role_id = 5;
                    $permission->school_id = $school->id;
                    $permission->save();
                }
        
                $admins = [800, 801, 802, 803, 804, 805, 806, 807, 808, 809, 810, 811, 812, 813, 814, 815, 900, 901, 902, 903, 904];
        
                foreach ($admins as $key => $value) {
                    $permission = new AramiscPermissionAssign();
                    $permission->module_id = $value;
                    $permission->role_id = 5;
                    $permission->school_id = $school->id;
                    $permission->save();
                }
        
                $ids = [399, 400, 401, 402, 403, 404, 428, 429, 430, 431, 456, 457, 458, 459, 460, 461, 462, 463, 478, 482, 483, 484, 549];
                foreach ($ids as $id) {
                    $permission = AramiscPermissionAssign::where('school_id', $school->id)->where('role_id', 5)->where('module_id', $id)->first();
                    if ($permission) {
                        $permission->delete();
                    }
                }
        
                // for teacher
                $teachers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 61, 62, 63, 64, 65, 66, 67, 68, 69, 70, 71, 72, 73, 74, 75, 76, 77, 79, 80, 81, 82, 83, 84, 85, 86, 533, 534, 535, 536, 87, 88, 89, 90, 91, 92, 93, 94, 95, 96, 97, 98, 99, 100, 101, 102, 103, 104, 105, 106, 107, 160, 161, 162, 163, 164, 165, 166, 167, 168, 169, 170, 171, 172, 173, 174, 175, 176, 177, 178, 179, 180, 181, 182, 183, 184, 185, 186, 187, 188, 189, 190, 191, 192, 193, 194, 195, 196, 197, 198, 199, 200, 201, 202, 203, 204, 205, 206, 207, 208, 209, 210, 211, 214, 215, 216, 217, 218, 219, 225, 226, 227, 228, 229, 230, 231, 232, 233, 234, 235, 236, 237, 238, 239, 240, 241, 242, 243, 244, 245, 246, 247, 248, 249, 250, 251, 252, 253, 254, 255, 256, 257, 258, 259, 260, 261, 262, 263, 264, 265, 266, 267, 268, 269, 270, 271, 272, 273, 274, 275, 276, 537, 286, 287, 288, 289, 290, 291, 292, 293, 294, 295, 296, 297, 298, 299, 300, 301, 302, 303, 304, 305, 306, 307, 308, 309, 310, 311, 312, 313, 314, 348, 349, 350, 351, 352, 353, 354, 355, 356, 357, 358, 359, 360, 361, 362, 363, 364, 365, 366, 367, 368, 369, 370, 371, 372, 373, 374, 375, 277, 278, 279, 280, 281, 282, 283, 284, 285, 800, 801, 802, 803, 804, 805, 806, 807, 808, 809, 833, 834, 900, 901, 902, 903, 904];
        
                foreach ($teachers as $key => $value) {
        
                    $permission = new AramiscPermissionAssign();
                    $permission->module_id = $value;
                    $permission->role_id = 4;
                    $permission->school_id = $school->id;
                    $permission->save();
                }
        
                // for receiptionists
                $receiptionists = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 64, 65, 66, 67, 83, 84, 85, 86, 160, 161, 162, 163, 164, 188, 193, 194, 195, 376, 377, 378, 379, 380, 900, 901, 902, 903, 904];
        
                foreach ($receiptionists as $key => $value) {
        
                    $permission = new AramiscPermissionAssign();
                    $permission->module_id = $value;
                    $permission->role_id = 7;
                    $permission->school_id = $school->id;
                    $permission->save();
                }
        
                // for librarians
                $librarians = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 61, 64, 65, 66, 67, 83, 84, 85, 86, 160, 161, 162, 163, 164, 188, 193, 194, 195, 298, 299, 300, 301, 302, 303, 304, 305, 306, 307, 308, 309, 310, 311, 312, 313, 314, 376, 377, 378, 379, 380, 900, 901, 902, 903, 904];
        
                foreach ($librarians as $key => $value) {
        
                    $permission = new AramiscPermissionAssign();
                    $permission->module_id = $value;
                    $permission->role_id = 8;
                    $permission->school_id = $school->id;
                    $permission->save();
                }
        
                // for drivers
                $drivers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 188, 193, 194, 19, 900, 901, 902, 903, 904];
        
                foreach ($drivers as $key => $value) {
        
                    $permission = new AramiscPermissionAssign();
                    $permission->module_id = $value;
                    $permission->role_id = 9;
                    $permission->school_id = $school->id;
                    $permission->save();
                }
        
                // for accountants
                $accountants = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 64, 65, 66, 67, 68, 69, 70, 83, 84, 85, 86, 108, 109, 110, 111, 112, 113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123, 124, 125, 126, 127, 128, 129, 130, 131, 132, 133, 134, 135, 136, 160, 161, 162, 163, 164, 165, 166, 167, 168, 169, 170, 171, 172, 173, 174, 175, 176, 177, 178, 179, 188, 193, 194, 195, 376, 377, 378, 379, 380, 381, 382, 383, 900, 901, 902, 903, 904];
        
                foreach ($accountants as $key => $value) {
        
                    $permission = new AramiscPermissionAssign();
                    $permission->module_id = $value;
                    $permission->role_id = 6;
                    $permission->school_id = $school->id;
                    $permission->save();
                }
        
                // student
                for ($j = 1; $j <= 55; $j++) {
                    $permission = new AramiscPermissionAssign();
                    $permission->module_id = $j;
                    $permission->role_id = 2;
                    $permission->school_id = $school->id;
                    $permission->save();
                }
        
                $students = [800, 810, 815, 900, 901, 902, 903, 904];
                foreach ($students as $key => $value) {
                    $permission = new AramiscPermissionAssign();
                    $permission->module_id = $value;
                    $permission->role_id = 2;
                    $permission->school_id = $school->id;
                    $permission->save();
                }
        
                // parent
                for ($j = 56; $j <= 99; $j++) {
                    $permission = new AramiscPermissionAssign();
                    $permission->module_id = $j;
                    $permission->role_id = 3;
                    $permission->school_id = $school->id;
                    $permission->save();
                }
                // chat module
                $parents = [910, 911, 912, 913, 914];
                foreach ($parents as $key => $value) {
                    $permission = new AramiscPermissionAssign();
                    $permission->module_id = $value;
                    $permission->role_id = 3;
                    $permission->school_id = $school->id;
                    $permission->save();
                }
                
                $s = new AramiscStyle();
                $s->style_name = 'Default';
                $s->path_main_style = 'style.css';
                $s->path_aramisc_style = 'aramisc.css';
                $s->primary_color = 'var(--base_color)';
                $s->primary_color2 = '#7c32ff';
                $s->title_color = '#222222';
                $s->text_color = '#828bb2';
                $s->white = '#ffffff';
                $s->black = '#000000';
                $s->sidebar_bg = '#e7ecff';
                $s->barchart1 = '#8a33f8';
                $s->barchart2 = '#f25278';
                $s->barcharttextcolor = 'var(--base_color)';
                $s->barcharttextfamily = '"poppins", sans-serif';
                $s->areachartlinecolor1 = 'rgba(124, 50, 255, 0.5)';
                $s->areachartlinecolor2 = 'rgba(242, 82, 120, 0.5)';
                $s->dashboardbackground = '';
                $s->is_active = 1;
                $s->school_id = $school->id;
                $s->save();
        
                $s = new  AramiscStyle();
                $s->style_name = 'Lawn Green';
                $s->path_main_style = 'lawngreen_version/style.css';
                $s->path_aramisc_style = 'lawngreen_version/aramisc.css';
                $s->primary_color = 'var(--base_color)';
                $s->primary_color2 = '#03e396';
                $s->title_color = '#222222';
                $s->text_color = '#828bb2';
                $s->white = '#ffffff';
                $s->black = '#000000';
                $s->sidebar_bg = '#e7ecff';
        
                $s->barchart1 = 'var(--base_color)';
                $s->barchart2 = '#03e396';
        
                $s->barcharttextcolor = '#03e396';
                $s->barcharttextfamily = '"Cerebri Sans", Helvetica, Arial, sans-serif';
        
                $s->areachartlinecolor1 = 'var(--base_color)';
                $s->areachartlinecolor2 = '#03e396';
                $s->dashboardbackground = '#e7ecff';
                $s->school_id = $school->id;
                $s->save();
        

                $store = new AramiscHeaderMenuManager();
               
                $store->type = 'sPages';
                $store->element_id = 1;
                $store->title = 'Home';
                $store->link = '/';
                $store->school_id = $school->id;
                $store->save();

                $store = new AramiscHeaderMenuManager();
              
                $store->type = 'sPages';
                $store->element_id = 2;
                $store->title = 'About';
                $store->link = '/about';
                $store->school_id = $school->id;
                $store->save();

                $store = new AramiscHeaderMenuManager();
               
                $store->type = 'sPages';
                $store->element_id = 3;
                $store->title = 'Course';
                $store->link = '/course';
                $store->school_id = $school->id;
                $store->save();

                $store = new AramiscHeaderMenuManager();
             
                $store->type = 'sPages';
                $store->element_id = 4;
                $store->title = 'News';
                $store->link = '/news-page';
                $store->school_id = $school->id;
                $store->save();

                $store = new AramiscHeaderMenuManager();
              
                $store->type = 'sPages';
                $store->element_id = 5;
                $store->title = 'Contact';
                $store->link = '/contact';
                $store->school_id = $school->id;
                $store->save();

                $store = new AramiscHeaderMenuManager();
              
                $store->type = 'sPages';
                $store->element_id = 6;
                $store->title = 'Login';
                $store->link = '/login';
                $store->school_id = $school->id;
                $store->save();

                DB::table('aramisc_social_media_icons')->insert([
                    [
                        'url' => 'https://www.facebook.com/Spondonit',
                        'icon' => 'fa fa-facebook',
                        'status' => 1,
                        'school_id' => $school->id,
                    ],
                    [
                        'url' => 'https://www.facebook.com/Spondonit',
                        'icon' => 'fa fa-twitter',
                        'status' => 1,
                        'school_id' => $school->id,
                    ],
                    [
                        'url' => 'https://www.facebook.com/Spondonit',
                        'icon' => 'fa fa-dribbble',
                        'status' => 1,
                        'school_id' => $school->id,
                    ],
                    [
                        'url' => 'https://www.facebook.com/Spondonit',
                        'icon' => 'fa fa-linkedin',
                        'status' => 1,
                        'school_id' => $school->id,
                    ],
                ]);

                $store = new AramiscPage();
               
                $store->title = 'Home';
                $store->slug = '/';
                $store->active_status = 1;
                $store->is_dynamic = 0;
                $store->school_id = $school->id;
                $store->save();
        
                $store = new AramiscPage();
               
                $store->title = 'About';
                $store->slug = '/about';
                $store->active_status = 1;
                $store->is_dynamic = 0;
                $store->school_id = $school->id;
                $store->save();
        
                $store = new AramiscPage();
              
                $store->title = 'Course';
                $store->slug = '/course';
                $store->active_status = 1;
                $store->is_dynamic = 0;
                $store->school_id = $school->id;
                $store->save();
        
                $store = new AramiscPage();
             
                $store->title = 'News';
                $store->slug = '/news-page';
                $store->active_status = 1;
                $store->is_dynamic = 0;
                $store->school_id = $school->id;
                $store->save();
        
                $store = new AramiscPage();
              
                $store->title = 'Contact';
                $store->slug = '/contact';
                $store->active_status = 1;
                $store->is_dynamic = 0;
                $store->school_id = $school->id;
                $store->save();
        
                $store = new AramiscPage();
              
                $store->title = 'Login';
                $store->slug = '/login';
                $store->active_status = 1;
                $store->is_dynamic = 0;
                $store->school_id = $school->id;
                $store->save();

                $s         = new AramiscCustomLink();
                $s->title1 = 'Departments';
                $s->title2 = 'Health Care';
                $s->title3 = 'About Our System';
                $s->title4 = 'Resources';
        
                $s->link_label1 = 'About Aramisc';
                $s->link_href1  = 'http://aramiscdu.com';
        
                $s->link_label2 = 'Aramisc Home';
                $s->link_href2  = 'http://aramiscdu.com/home';
        
                $s->link_label3 = 'Business';
                $s->link_href3  = 'http://aramiscdu.com';
        
                $s->link_label4 = 'link_label4';
                $s->link_href4  = 'http://aramiscdu.com';
        
                $s->link_label5 = 'link_label5';
                $s->link_href5  = 'http://aramiscdu.com';
        
                $s->link_label6 = 'link_label6';
                $s->link_href6  = 'http://aramiscdu.com';
        
                $s->link_label7 = 'link_label7';
                $s->link_href7  = 'http://aramiscdu.com';
        
                $s->link_label8 = 'link_label8';
                $s->link_href8  = 'http://aramiscdu.com';
        
                $s->link_label9  = 'Home';
                $s->link_href9   = 'http://aramiscdu.com/home';
        
                $s->link_label10 = 'About';
                $s->link_href10  = 'http://aramiscdu.com/about';
        
        
                $s->link_label11 = 'Contact';
                $s->link_href11  = 'http://aramiscdu.com/contact';
        
                $s->link_label12 = 'link_label12';
                $s->link_href12  = 'http://aramiscdu.com';
        
                $s->link_label13 = 'link_label13';
                $s->link_href13  = 'http://aramiscdu.com';
        
                $s->link_label14 = 'link_label14';
                $s->link_href14  = 'http://aramiscdu.com';
        
                $s->link_label15 = 'link_label15';
                $s->link_href15  = 'http://aramiscdu.com';
        
                $s->link_label16 = 'link_label16';
                $s->link_href16  = 'http://aramiscdu.com';
                $s->school_id = $school->id; 
                $s->save();

            }
        );
    }
}
