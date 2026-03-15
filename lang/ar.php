<?php
/**
 * Arabic Language File
 */

return [
    // General
    'dir' => 'rtl',
    'lang_code' => 'ar',
    'lang_name' => 'العربية',
    'site_title' => 'بوابة التحقق من التوقيعات المعتمدة',
    'site_subtitle' => 'بوابة التوقيعات الرسمية للشركة',
    'copyright' => '© %s جميع الحقوق محفوظة.',
    'switch_lang' => 'English',
    'switch_lang_code' => 'en',

    // Navigation
    'home' => 'الرئيسية',
    'dashboard' => 'لوحة التحكم',
    'signatories' => 'المفوضون بالتوقيع',
    'companies' => 'الشركات',
    'access_logs' => 'سجل الدخول',
    'logout' => 'تسجيل الخروج',
    'settings' => 'الإعدادات',

    // Login
    'login' => 'تسجيل الدخول',
    'username' => 'اسم المستخدم',
    'password' => 'كلمة المرور',
    'login_btn' => 'دخول',
    'login_error' => 'اسم المستخدم أو كلمة المرور غير صحيحة.',
    'login_locked' => 'تم قفل الحساب بسبب محاولات دخول متعددة. حاول مرة أخرى بعد %d دقيقة.',
    'login_required' => 'يرجى تسجيل الدخول للمتابعة.',
    'admin_login' => 'دخول المسؤول',
    'company_login' => 'دخول الشركة',

    // Dashboard
    'total_signatories' => 'إجمالي المفوضين',
    'active_signatories' => 'المفوضون النشطون',
    'total_companies' => 'إجمالي الشركات',
    'active_links' => 'الروابط النشطة',
    'recent_access' => 'آخر عمليات الدخول',
    'statistics' => 'الإحصائيات',
    'welcome_admin' => 'مرحباً بك في لوحة التحكم',

    // Signatories
    'add_signatory' => 'إضافة مفوض',
    'edit_signatory' => 'تعديل مفوض',
    'delete_signatory' => 'حذف مفوض',
    'signatory_name' => 'الاسم الكامل',
    'signatory_position' => 'المنصب',
    'signature_image' => 'صورة التوقيع',
    'signatory_status' => 'الحالة',
    'active' => 'نشط',
    'inactive' => 'غير نشط',
    'created_at' => 'تاريخ الإنشاء',
    'actions' => 'الإجراءات',
    'save' => 'حفظ',
    'cancel' => 'إلغاء',
    'edit' => 'تعديل',
    'delete' => 'حذف',
    'activate' => 'تفعيل',
    'deactivate' => 'تعطيل',
    'confirm_delete' => 'هل أنت متأكد من الحذف؟',
    'signatory_added' => 'تمت إضافة المفوض بنجاح.',
    'signatory_updated' => 'تم تحديث المفوض بنجاح.',
    'signatory_deleted' => 'تم حذف المفوض بنجاح.',
    'signatory_status_updated' => 'تم تحديث حالة المفوض.',
    'upload_png_only' => 'يرجى رفع صورة بصيغة PNG فقط.',
    'upload_error' => 'حدث خطأ أثناء رفع الصورة.',
    'no_signatories' => 'لا يوجد مفوضون بالتوقيع حالياً.',

    // Companies
    'add_company' => 'إضافة شركة',
    'edit_company' => 'تعديل شركة',
    'delete_company' => 'حذف شركة',
    'company_name' => 'اسم الشركة',
    'company_username' => 'اسم المستخدم',
    'company_password' => 'كلمة المرور',
    'company_token' => 'رابط الوصول',
    'expires_at' => 'تاريخ الانتهاء',
    'expiration_hours' => 'مدة الصلاحية (ساعات)',
    'generate_credentials' => 'إنشاء بيانات الدخول',
    'company_added' => 'تمت إضافة الشركة بنجاح.',
    'company_deleted' => 'تم حذف الشركة بنجاح.',
    'copy_link' => 'نسخ الرابط',
    'link_copied' => 'تم نسخ الرابط!',
    'token_expired' => 'انتهت صلاحية رابط الوصول.',
    'token_invalid' => 'رابط الوصول غير صالح.',
    'no_companies' => 'لا توجد شركات مسجلة.',
    'access_link' => 'رابط الوصول',
    'status_label' => 'الحالة',
    'expired' => 'منتهي',
    'valid' => 'صالح',
    'credentials_info' => 'معلومات بيانات الدخول',
    'regenerate' => 'إعادة إنشاء',

    // Signature Display
    'authorized_signatures' => 'التوقيعات المعتمدة',
    'authorized_signatories_title' => 'المفوضون المعتمدون بالتوقيع',
    'download_signature' => 'تحميل التوقيع',
    'view_signature' => 'عرض التوقيع',
    'official_signature' => 'التوقيع الرسمي',
    'company_seal' => 'ختم الشركة',
    'signature_valid' => 'هذا التوقيع صالح ومعتمد.',
    'verification_notice' => 'تم التحقق من صحة هذه التوقيعات.',
    'session_expires' => 'تنتهي الجلسة في',

    // Access Logs
    'log_company' => 'الشركة',
    'log_ip' => 'عنوان IP',
    'log_time' => 'وقت الدخول',
    'no_logs' => 'لا توجد سجلات دخول.',
    'clear_logs' => 'مسح السجلات',
    'logs_cleared' => 'تم مسح السجلات بنجاح.',

    // Errors
    'error_404' => 'الصفحة غير موجودة',
    'error_403' => 'غير مصرح لك بالوصول',
    'error_general' => 'حدث خطأ. حاول مرة أخرى.',
    'field_required' => 'هذا الحقل مطلوب.',
    'invalid_request' => 'طلب غير صالح.',

    // QR Code
    'qr_verification' => 'رمز التحقق QR',
    'scan_to_verify' => 'امسح للتحقق من التوقيع',

    // Positions (examples)
    'general_manager' => 'المدير العام',
    'finance_manager' => 'المدير المالي',
    'hr_manager' => 'مدير الموارد البشرية',
    'ceo' => 'الرئيس التنفيذي',
];
