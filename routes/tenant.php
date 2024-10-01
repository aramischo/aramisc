<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use App\Http\Controllers\Theme\Edulia\FrontendController;




Route::get('logout', '\App\Http\Controllers\Auth\LoginController@logout');
Route::post('login', 'Auth\LoginController@login');

Route::group(['middleware' => []], function () {

    if (config('app.app_sync') and !session('domain')) {
        Route::get('/', 'LandingController@index')->name('/');
    } else {
        if (moduleStatusCheck('Saas') == TRUE) {
            Route::get('login', 'Auth\LoginController@loginFormTwo')->name('login');
        }
         Route::get('/', 'AramiscFrontendController@index')->name('/');
    }

    Route::get('login', 'Auth\LoginController@loginFormTwo')->name('login');

    if(!moduleStatusCheck('Saas')){
        Route::get('/', 'AramiscFrontendController@index')->name('/');
    }


    if (activeTheme() != 'edulia') {

        Route::get('home', 'AramiscFrontendController@index');
        Route::get('about', 'AramiscFrontendController@about');
        Route::get('course', 'AramiscFrontendController@course');
        Route::post('load-more-course', 'AramiscFrontendController@loadMoreCourse')->name('load-more-course');
        Route::get('course-Details/{id}', 'AramiscFrontendController@courseDetails')->name('course-Details')->where('id', '[0-9]+');
        Route::get('news-page', 'AramiscFrontendController@newsPage');
        Route::post('load-more-news', 'AramiscFrontendController@loadMoreNews')->name('load-more-news');
        Route::get('news-details/{id}', 'AramiscFrontendController@newsDetails')->name('news-Details')->where('id', '[0-9]+');
        Route::get('contact', 'AramiscFrontendController@contact');
        Route::get('exam-result', 'AramiscFrontendController@examResult')->name('examResult');
        Route::get('class-exam-routine', 'AramiscFrontendController@classExamRoutine')->name('class-exam-routine');
    }
    //front pages without auth
    // Route::group(['middleware' => ['ThemeCheckMiddleware']], function () {

    // });


    Route::get('change-password', 'HomeController@updatePassowrd')->name('updatePassowrd');
    Route::get('/academic_years', 'HomeController@academicUpdate');
    Route::get('/class_updates', 'HomeController@classUpdate');
    Route::get('/section_updates', 'HomeController@sectionUpdate');
    Route::get('/class_section_updates', 'HomeController@sectionClassUpdate');
    Route::get('/new_updates', 'HomeController@classSectionAllUpdate');
    Route::get('/db_update_new', 'HomeController@dbUpdate');
    Route::get('/student_update', 'HomeController@studentUpdate');
    Route::get('/class_update_new', 'HomeController@classUpdateNew');

    Route::get('/after-login', 'HomeController@dashboard');
    Route::get('/dashboard', 'HomeController@dashboard')->name('dashboard');
    Route::get('ajax-get-login-access', 'AramiscAuthController@getLoginAccess');



    Route::get('view/single/notification/{id}', 'AramiscNotificationController@viewSingleNotification')->name('view-single-notification')->where('id', '[0-9]+');

    Route::get('view/all/notification/{id}', 'AramiscNotificationController@viewAllNotification')->name('view/all/notification')->where('id', '[0-9]+');
    Route::get('notification-show/{id}', 'AramiscNotificationController@udpateNotification')->name('notification-show');
    Route::get('all-notification', 'AramiscNotificationController@allNotification')->name('all-notification');

    Route::get('view/notice/{id}', 'HomeController@viewNotice')->where('id', '[0-9]+')->where('id', '[0-9]+')->name('view-notice');
    // update password


    Route::post('admin-change-password', 'HomeController@updatePassowrdStore')->name('updatePassowrdStore'); //InfixPro Version

    Route::get('download-uploaded-content/{id}/{student_id}', 'Student\AramiscStudentPanelController@downloadHomeWorkContent')->name('downloadHomeWorkContent');

    Route::post('/pay-with-paystack', 'Student\AramiscFeesController@redirectToGateway')->name('pay-with-paystack');

    Route::get('/payment/callback', 'Student\AramiscFeesController@handleGatewayCallback')->name('handleGatewayCallback');

    //customer panel

    Route::group(['middleware' => ['CustomerMiddleware']], function () {
        Route::get('customer-dashboard', ['as' => 'customer_dashboard', 'uses' => 'Customer\AramiscCustomerPanelController@customerDashboard']);
        Route::get('customer-purchases', 'Customer\AramiscCustomerPanelController@customerPurchases');
    });

    Route::get('student-transport-view-modal/{r_id}/{v_id}', ['as' => 'student_transport_view_modal', 'uses' => 'Student\AramiscStudentPanelController@studentTransportViewModal']);

    //Install for Demo
    // Route::post('/verified-code', 'InstallController@verifiedCodeStore')->name('verifiedCodeStore');

    //for localization
    Route::get('locale/{locale}', 'Admin\SystemSettings\SmSystemSettingController@changeLocale');
    Route::get('change-language/{id}', 'Admin\SystemSettings\SmSystemSettingController@changeLanguage')->name('change-language');


    Route::get('verify/', 'VerifyController@index');
    Route::put('/verify/storePurchasecode/{id}', 'VerifyController@storePurchasecode');
    Route::put('/verify/storePurchasecode/{id}', 'VerifyController@storePurchasecode');


    Route::get('/news', 'AramiscNewsController@index')->name('news_index');
    Route::post('/news-store', 'AramiscNewsController@store')->name('store_news')->middleware('userRolePermission:store_news');
    Route::post('/news-update', 'AramiscNewsController@update')->name('update_news')->middleware('userRolePermission:edit-news');
    Route::get('newsDetails/{id}', 'AramiscNewsController@newsDetails')->name('newsDetails')->middleware('userRolePermission:newsDetails');
    Route::get('for-delete-news/{id}', 'AramiscNewsController@forDeleteNews')->name('for-delete-news')->middleware('userRolePermission:for-delete-news');
    Route::get('news-comment-list', 'Admin\FrontSettings\AramiscNewsController@commentList')->name('news-comment-list')->middleware('userRolePermission:news-comment-list');
    Route::get('news-comment-list-datatable', 'Admin\FrontSettings\AramiscNewsController@commentListDatatable')->name('news-comment-list-datatable');
    Route::post('news-comment-update', 'Admin\FrontSettings\AramiscNewsController@commentUpdate')->name('news-comment-update');
    Route::post('news-comment-delete', 'Admin\FrontSettings\AramiscNewsController@commentDelete')->name('news-comment-delete')->middleware('userRolePermission:news-comment-delete');
    Route::get('news-comment-status/{id}/{news_id}/{type}', 'Admin\FrontSettings\AramiscNewsController@commentStatus')->name('news-comment-status')->middleware('userRolePermission:news-comment-status');
    Route::get('news-comment-status-backend/{id}', 'Admin\FrontSettings\AramiscNewsController@commentStatusBackend')->name('news-comment-status-backend')->middleware('userRolePermission:news-comment-status');
    Route::get('delete-news/{id}', 'AramiscNewsController@delete')->name('delete-news');
    Route::get('edit-news/{id}', 'AramiscNewsController@edit')->name('edit-news')->middleware('userRolePermission:edit-news');

    Route::get('news-category', 'AramiscNewsController@newsCategory')->name('news-category')->middleware('userRolePermission:news-category');
    Route::post('/news-category-store', 'AramiscNewsController@storeCategory')->name('store_news_category')->middleware('userRolePermission:store_news_category');
    Route::post('/news-category-update', 'AramiscNewsController@updateCategory')->name('update_news_category')->middleware('userRolePermission:edit-news-category');
    Route::get('for-delete-news-category/{id}', 'AramiscNewsController@forDeleteNewsCategory')->name('for-delete-news-category')->middleware('userRolePermission:for-delete-news-category');
    Route::get('delete-news-category/{id}', 'AramiscNewsController@deleteCategory')->name('delete-news-category');
    Route::get('edit-news-category/{id}', 'AramiscNewsController@editCategory')->name('edit-news-category')->middleware('userRolePermission:edit-news-category');

    Route::get('view-news-category/{id}', 'AramiscNewsController@viewNewsCategory')->name('view-news-category');

    //For course module
    Route::get('course-category', 'AramiscCourseController@courseCategory')->name('course-category')->middleware('userRolePermission:course-category');
    Route::post('store-course-category', 'AramiscCourseController@storeCourseCategory')->name('store-course-category')->middleware('userRolePermission:store-course-category');
    Route::get('edit-course-category/{id}', 'AramiscCourseController@editCourseCategory')->name('edit-course-category')->middleware('userRolePermission:edit-course-category');
    Route::post('update-course-category', 'AramiscCourseController@updateCourseCategory')->name('update-course-category')->middleware('userRolePermission:edit-course-category');
    Route::post('delete-course-category/{id}', 'AramiscCourseController@deleteCourseCategory')->name('delete-course-category')->middleware('userRolePermission:delete-course-category');

    Route::get('view-course-category/{id}', 'AramiscCourseController@viewCourseCategory')->name('view-course-category');

    Route::get('course-list', 'AramiscCourseController@index')->name('course-list')->middleware('userRolePermission:course-list');
    Route::post('/course-store', 'AramiscCourseController@store')->name('store_course')->middleware('userRolePermission:store_course');
    Route::post('/course-update', 'AramiscCourseController@update')->name('update_course')->middleware('userRolePermission:edit-course');
    Route::get('for-delete-course/{id}', 'AramiscCourseController@forDeleteCourse')->name('for-delete-course')->middleware('userRolePermission:for-delete-course');
    Route::get('delete-course/{id}', 'AramiscCourseController@destroy')->name('delete-course')->middleware('userRolePermission:delete-course');
    Route::get('edit-course/{id}', 'AramiscCourseController@edit')->name('edit-course')->middleware('userRolePermission:edit-course');
    Route::get('course-Details-admin/{id}', 'AramiscCourseController@courseDetails')->name('course-Details-admin')->middleware('userRolePermission:course-Details-admin');

    //for testimonial

    Route::get('/testimonial', 'AramiscTestimonialController@index')->name('testimonial_index')->middleware('userRolePermission:testimonial_index');
    Route::post('/testimonial-store', 'AramiscTestimonialController@store')->name('store_testimonial')->middleware('userRolePermission:store_testimonial');
    Route::post('/testimonial-update', 'AramiscTestimonialController@update')->name('update_testimonial')->middleware('userRolePermission:edit-testimonial');
    Route::get('testimonial-details/{id}', 'AramiscTestimonialController@testimonialDetails')->name('testimonial-details')->middleware('userRolePermission:testimonial-details');
    Route::get('for-delete-testimonial/{id}', 'AramiscTestimonialController@forDeleteTestimonial')->name('for-delete-testimonial')->middleware('userRolePermission:for-delete-testimonial');
    Route::get('delete-testimonial/{id}', 'AramiscTestimonialController@delete')->name('delete-testimonial');
    Route::get('edit-testimonial/{id}', 'AramiscTestimonialController@edit')->name('edit-testimonial')->middleware('userRolePermission:edit-testimonial');

    // Contact us
    Route::get('contact-page', 'AramiscFrontendController@conpactPage')->name('conpactPage')->middleware('userRolePermission:conpactPage');
    Route::get('contact-page/edit', 'AramiscFrontendController@contactPageEdit')->name('contactPageEdit');
    Route::post('contact-page/update', 'AramiscFrontendController@contactPageStore')->name('contactPageStore');

    // contact message
    Route::get('contact-message', 'AramiscFrontendController@contactMessage')->name('contactMessage')->middleware('userRolePermission:contactMessage');
    Route::get('delete-message/{id}', 'AramiscFrontendController@deleteMessage')->name('delete-message')->middleware('userRolePermission:delete-message');

    // News route start
    // Route::get('news-heading-update', 'AramiscFrontendController@newsHeading')->name('news-heading-update')->middleware('userRolePermission:news-heading-update');
    // Route::post('news-heading-update', 'AramiscFrontendController@newsHeadingUpdate')->name('news-heading-update')->middleware('userRolePermission:news-heading-update');

    // Course route start
    // Route::get('course-heading-update', 'AramiscFrontendController@courseHeading')->name('course-heading-update')->middleware('userRolePermission:course-heading-update');
    // Route::post('course-heading-update', 'AramiscFrontendController@courseHeadingUpdate')->name('course-heading-update')->middleware('userRolePermission:course-heading-update');

    // Course Details route start
    Route::get('course-details-heading', 'AramiscFrontendController@courseDetailsHeading')->name('course-details-heading')->middleware('userRolePermission:course-details-heading');
    Route::post('course-heading-details-update', 'AramiscFrontendController@courseDetailsHeadingUpdate')->name('course-details-heading-update')->middleware('userRolePermission:course-details-heading-update');

    Route::get('about-page', 'AramiscFrontendController@aboutPage')->name('about-page')->middleware('userRolePermission:about-page');
    Route::get('about-page/edit', 'AramiscFrontendController@aboutPageEdit')->name('about-page/edit');
    Route::post('about-page/update', 'AramiscFrontendController@aboutPageStore')->name('about-page/update');

    Route::post('send-message', 'AramiscFrontendController@sendMessage');
    Route::get('edulia-send-message', 'AramiscFrontendController@sendMessage');

    Route::get('custom-links', 'Admin\SystemSettings\SmSystemSettingController@customLinks')->name('custom-links')->middleware('userRolePermission:custom-links');
    Route::post('custom-links-update', 'Admin\SystemSettings\SmSystemSettingController@customLinksUpdate')->name('custom-links-update')->middleware('userRolePermission:custom-links-update');

    // admin-home-page
    Route::get('admin-home-page', 'Admin\SystemSettings\SmSystemSettingController@homePageBackend')->name('admin-home-page')->middleware('userRolePermission:admin-home-page');
    Route::post('admin-home-page-update', 'Admin\SystemSettings\SmSystemSettingController@homePageUpdate')->name('admin-home-page-update')->middleware('userRolePermission:admin-home-page-update');

    // social media
    Route::get('social-media', 'AramiscFrontendController@socialMedia')->name('social-media')->middleware('userRolePermission:social-media');
    Route::post('social-media-store', 'AramiscFrontendController@socialMediaStore')->name('social-media-store');
    Route::get('social-media-edit/{id}', 'AramiscFrontendController@socialMediaEdit')->name('social-media-edit');
    Route::post('social-media-update', 'AramiscFrontendController@socialMediaUpdate')->name('social-media-update');
    Route::get('social-media-delete/{id}', 'AramiscFrontendController@socialMediaDelete')->name('social-media-delete');

    // Header Menu Manager
    Route::get('header-menu-manager', 'AramiscFrontendController@headerMenuManager')->name('header-menu-manager')->middleware('userRolePermission:header-menu-manager');
    Route::post('add-element', 'AramiscFrontendController@addElement')->name('add-element')->middleware('userRolePermission:add-element');
    Route::post('reordering', 'AramiscFrontendController@reordering')->name('reordering');
    Route::post('element-update', 'AramiscFrontendController@elementUpdate')->name('element-update')->middleware('userRolePermission:element-update');
    Route::post('delete-element', 'AramiscFrontendController@deleteElement')->name('delete-element')->middleware('userRolePermission:delete-element');

    // Pages
    Route::get('create-page', 'AramiscFrontendController@createPage')->name('create-page')->middleware('userRolePermission:656');
    Route::post('save-page-data', 'AramiscFrontendController@savePageData')->name('save-page-data')->middleware('userRolePermission:save-page-data');
    Route::get('edit-page/{id}', 'AramiscFrontendController@editPage')->name('edit-page')->middleware('userRolePermission:edit-page');
    Route::post('update-page-data', 'AramiscFrontendController@updatePageData')->name('update-page-data')->middleware('userRolePermission:edit-page');
    Route::get('view-page/{slug}', 'AramiscFrontendController@viewPage')->name('view-page');
    Route::get('page-list', 'AramiscFrontendController@pageList')->name('page-list')->middleware('userRolePermission:page-list');
    Route::post('delete-page/{id}', 'AramiscFrontendController@deletePage')->name('delete-page')->middleware('userRolePermission:delete-page');
    Route::get('download-header-image/{file_name}', function ($file_name = null) {
        $file = public_path() . '/uploads/pages/' . $file_name;
        if (file_exists($file)) {
            return Response::download($file);
        }
    })->name('download-header-image')->middleware('userRolePermission:659');

    // admin-home-page
    Route::get('admin-data-delete', 'Admin\SystemSettings\SmSystemSettingController@tableEmpty');
    Route::post('database-delete', 'Admin\SystemSettings\SmSystemSettingController@databaseDelete')->name('database-delete');
    Route::get('database-restore', 'Admin\SystemSettings\SmSystemSettingController@databaseRestory');
    Route::post('database-restore', 'Admin\SystemSettings\SmSystemSettingController@databaseRestory');

    Route::get('change-website-btn-status', 'Admin\SystemSettings\SmSystemSettingController@changeWebsiteBtnStatus');
    Route::get('change-dashboard-btn-status', 'Admin\SystemSettings\SmSystemSettingController@changeDashboardBtnStatus');
    Route::get('change-report-btn-status', 'Admin\SystemSettings\SmSystemSettingController@changeReportBtnStatus');

    Route::get('change-style-btn-status', 'Admin\SystemSettings\SmSystemSettingController@changeStyleBtnStatus');
    Route::get('change-ltl_rtl-btn-status', 'Admin\SystemSettings\SmSystemSettingController@changeLtlRtlBtnStatus');
    Route::get('change-language-btn-status', 'Admin\SystemSettings\SmSystemSettingController@changeLanguageBtnStatus');
    Route::post('update-website-url', 'Admin\SystemSettings\SmSystemSettingController@updateWebsiteUrl')->name('update-website-url')->middleware('userRolePermission:update-website-url');

    Route::get('update-created-date', 'Admin\SystemSettings\SmSystemSettingController@updateCreatedDate');

    Route::get('preloader-setting', 'Admin\SystemSettings\PreloaderSettingController@index')
        ->name('setting.preloader');

    Route::post('preloader-setting', 'Admin\SystemSettings\PreloaderSettingController@store');

    // manage currency

    Route::get('manage-currency', 'Admin\SystemSettings\SmSystemSettingController@manageCurrency')->name('manage-currency')->middleware('userRolePermission:manage-currency');
    Route::post('currency-store', 'Admin\SystemSettings\SmSystemSettingController@storeCurrency')->name('currency-store')->middleware('userRolePermission:currency-store');
    Route::post('currency-update', 'Admin\SystemSettings\SmSystemSettingController@storeCurrencyUpdate')->name('currency-update')->middleware('userRolePermission:currency_edit');
    Route::get('manage-currency/edit/{id}', 'Admin\SystemSettings\SmSystemSettingController@manageCurrencyEdit')->name('currency_edit')->middleware('userRolePermission:currency_edit');
    Route::get('manage-currency/delete/{id}', 'Admin\SystemSettings\SmSystemSettingController@manageCurrencyDelete')->name('currency_delete')->middleware('userRolePermission:currency_delete');
    Route::get('system-destroyed-by-authorized', 'Admin\SystemSettings\SmSystemSettingController@systemDestroyedByAuthorized')->name('systemDestroyedByAuthorized');

    Route::post('student-update-pic/{id}', ['as' => 'student_update_pic', 'uses' => 'AramiscStudentAdmissionController@studentUpdatePic']);
    Route::post('student-document-delete', ['as' => 'student_document_delete', 'uses' => 'AramiscStudentAdmissionController@deleteStudentDocument']);
    Route::post('staff-document-delete', ['as' => 'staff-document-delete', 'uses' => 'AramiscStaffController@deleteStaffDoc']);
    Route::get('view-leave-details-apply/{id}', 'Admin\Leave\AramiscLeaveRequestController@viewLeaveDetails')->name('view-leave-details-apply');

    Route::group(['middleware' => ['auth']], function () {
        Route::get('theme-style-active', 'Admin\SystemSettings\SmSystemSettingController@themeStyleActive');
        Route::get('theme-style-rtl', 'Admin\SystemSettings\SmSystemSettingController@themeStyleRTL');
        Route::get('/user-language-change', 'Admin\SystemSettings\SmSystemSettingController@ajaxUserLanguageChange');
        Route::get('change-academic-year', 'Admin\SystemSettings\SmSystemSettingController@sessionChange');
    });

    Route::get('/academic_years', 'HomeController@academicUpdate');
    Route::get('/class_updates', 'HomeController@classUpdate');
    Route::get('/section_updates', 'HomeController@sectionUpdate');
    Route::get('/class_section_updates', 'HomeController@sectionClassUpdate');
    Route::get('/new_updates', 'HomeController@classSectionAllUpdate');
    Route::get('/db_update_new', 'HomeController@dbUpdate');
    Route::get('/student_update', 'HomeController@studentUpdate');
    Route::get('/class_update_new', 'HomeController@classUpdateNew');

    Route::get('developer-tool/{purpose}', 'AramiscFrontendController@developerTool')->name('developerTool');

    Route::group(['middleware' => ['XSS']], function () {
        Route::get('update-system', 'Admin\SystemSettings\SmSystemSettingController@UpdateSystem');
        // Route::get('/verified-code', 'InstallController@verifiedCode');
        // Route::post('system-verify', 'InstallController@systemVerifyPurchases')->name('systemVerifyPurchases');
        // Route::get('module-verify', 'InstallController@ModuleVerify')->name('ModuleVerify');
        // Route::post('module-verify-purchase', 'InstallController@ModuleverifyPurchases')->name('ModuleverifyPurchases');
        Route::get('institution-privacy-policy', 'AramiscFrontendController@institutionPrivacyPolicy')->name('institution-privacy-policy');
        Route::get('institution-terms-service', 'AramiscFrontendController@institutionTermServices')->name('institution-terms-service');

        //payment Gateway
        Route::any('payment_gateway_success_callback/{method}', 'PaymentGatewayCallbackController@successCallBack')->name('payment.success');
        Route::get('payment_gateway_cancel_callback/{method}', 'PaymentGatewayCallbackController@cancelCallback')->name('payment.cancel');;
        Route::post('makeFeesPayment', 'GatewayPaymentController@makeFeesPayment')->name('makeFeesPayment');

        Route::get('db-upgrade', 'Admin\SystemSettings\SmSystemSettingController@DbUpgrade');
        Route::get('academicIdUpdated', 'Admin\SystemSettings\SmSystemSettingController@academicIdUpdated');



        //USER REGISTER SECTION
        Route::get('register', 'AramiscFrontendController@register')->name('register');
        Route::post('register', 'AramiscFrontendController@customer_register')->name('customer_register');


        Route::get('error-404', function () {
            return view('auth.error');
        })->name('error-404');
        Route::get('notification-api', 'Admin\SystemSettings\SmSystemSettingController@notificationApi')->name('notificationApi');



        /* ::::::::::::::::::::::::: START SEARCH ROUTES :::::::::::::::::::::::::: */

        // Route::get('moduleAddOnsEnable/{name}', 'AramiscAddOnsController@moduleAddOnsEnable')->name('moduleAddOnsEnable');
        Route::post('/search', 'AramiscSearchController@search')->name('search');
        Route::post('/dashboard-student-search', 'AramiscSearchController@dashboardStudentSearch')->name('dashboard-student-search');

        /* ::::::::::::::::::::::::: START SEARCH ROUTES :::::::::::::::::::::::::: */

        Route::group(['middleware' => ['CheckUserMiddleware']], function () {
            Route::get('recovery/password', 'AramiscAuthController@recoveryPassord')->name('recoveryPassord');
            Route::post('email/verify', 'AramiscAuthController@emailVerify')->name('email/verify');
            Route::get('/reset/password/{email}/{code}', 'AramiscAuthController@resetEmailConfirtmation')->name('resetEmailConfirtmation');
            Route::post('/store/new/password', 'AramiscAuthController@storeNewPassword')->name('storeNewPassword');
            Route::get('login-2', 'Auth\LoginController@loginFormTwo')->name('loginFormTwo');
            Route::get('news', 'Admin\SystemSettings\SmSystemSettingController@news')->name('news')->middleware('userRolePermission:news');
        });

        Route::get('/dashboard', 'HomeController@dashboard')->name('dashboard');
        Route::get('ajax-get-login-access', 'AramiscAuthController@getLoginAccess');

        Route::get('class-routine/print/{class_id}/{section_id}', 'Admin\Academics\AramiscClassRoutineNewController@classRoutinePrint')->name('classRoutinePrint');
    });

    Route::get('paypal-return-status', 'AramiscCollectFeesByPaymentGateway@getPaymentStatus');
    Route::get('/ajaxGetVehicle', 'Admin\StudentInfo\AramiscStudentAjaxController@ajaxGetVehicle');
    Route::get('/ajaxRoomDetails', 'Admin\StudentInfo\AramiscStudentAjaxController@ajaxRoomDetails');

    Route::get('/ajax-get-class-academicyear', 'Admin\StudentInfo\AramiscStudentAjaxController@getClasAcademicyear');
    Route::get('/ajax-get-classes', 'Admin\StudentInfo\AramiscStudentAjaxController@getClasses');
    Route::get('/ajax-get-sections', 'Admin\StudentInfo\AramiscStudentAjaxController@getSection');

    //Exam result Page

    Route::get('exam-result-search', 'AramiscFrontendController@examResultSearch')->name('examResultSearch');

    // class/exam routine page

    Route::get('class-exam-routine-search', 'AramiscFrontendController@classExamRoutineSearch')->name('class-exam-routine-search');

    // ThemeBased Controller
    Route::controller(FrontendController::class)->as('frontend.')->group(function ($routes) {
        $routes->get('course-details/{id}', 'singleCourseDetails')->name('course-details')->where('id', '[0-9]+');
        $routes->get('news-details/{id}', 'singleNewsDetails')->name('news-details')->where('id', '[0-9]+');
        $routes->get('gallery-details/{id}', 'singleGalleryDetails')->name('gallery-details')->where('id', '[0-9]+');
        $routes->get('indiviual-result', 'indiviualResult')->name('indiviual-result');
        $routes->get('notice-details/{id}', 'singleNoticeDetails')->name('notice-details')->where('id', '[0-9]+');
        $routes->get('news-list', 'allBlogList')->name('blog-list');
        $routes->post('load-more-blog', 'loadMoreBlogs')->name('load-more-blog');
        $routes->get('event-details/{id}', 'singleEventDetails')->name('event-details')->where('id', '[0-9]+');
        $routes->get('blog-list', 'blogList')->name('blog-list');
        $routes->post('load-more-blog-list', 'loadMoreBlogList')->name('load-more-blog-list');
        $routes->get('speech-slider/{id}', 'singleSpeechSlider')->name('speech-slider')->where('id', '[0-9]+');
        $routes->get('all-course-list', 'courseList')->name('all-course-list');
        $routes->get('single-course-details/{id}', 'singleCourseDetail')->name('single-course-details');
        $routes->get('frontend-single-student-details/{id}', 'frontendSingleStudentDetails')->name('frontend-single-student-details');
        $routes->get('archive-list', 'archiveList')->name('archive-list');
        $routes->get('archive-year-filter', 'archiveYearFilter')->name('archive-year-filter');
        $routes->post('load-more-archive-list', 'loadMoreArchiveList')->name('load-more-archive-list');
        $routes->get('book-a-visit', 'bookAVisit')->name('book-a-visit');
        $routes->post('book-a-visit-store', 'bookAVisitStore')->name('book-a-visit-store');
        $routes->get('donor-details/{id}', 'donorDetails')->name('donor-details');
        $routes->get('staff-details/{id}', 'staffDetails')->name('staff-details');
        $routes->middleware(['auth', 'subdomain'])->group(function ($routes) {
            $routes->post('store-news-comment', 'storeNewsComment')->name('store-news-comment');
        });
    });


    
    Route::get('frontend-get-class', 'Admin\StudentInfo\FrontendStudentListController@ajaxFrontendClass');
    Route::get('frontend-get-section', 'Admin\StudentInfo\FrontendStudentListController@ajaxFrontendSection');
    Route::get('frontend-get-students', 'Admin\StudentInfo\FrontendStudentListController@getStudents')->name('frontend-get-students');
});
