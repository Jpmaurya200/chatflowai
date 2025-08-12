<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use App\Yantrana\Components\Home\Controllers\HomeController;
use App\Yantrana\Components\Page\Controllers\PageController;
use App\Yantrana\Components\User\Controllers\UserController;
use App\Yantrana\Components\Media\Controllers\MediaController;
use App\Yantrana\Components\Vendor\Controllers\VendorController;
use App\Yantrana\Components\Contact\Controllers\ContactController;
use App\Yantrana\Components\BotReply\Controllers\BotFlowController;
use App\Yantrana\Components\BotReply\Controllers\BotReplyController;
use App\Yantrana\Components\Campaign\Controllers\CampaignController;
use App\Yantrana\Components\Dashboard\Controllers\DashboardController;
use App\Yantrana\Components\Contact\Controllers\ContactGroupController;
use App\Yantrana\Components\Vendor\Controllers\VendorSettingsController;
use App\Yantrana\Components\Translation\Controllers\TranslationController;
use App\Yantrana\Components\Subscription\Controllers\SubscriptionController;
use App\Yantrana\Components\Contact\Controllers\ContactCustomFieldController;
use App\Yantrana\Components\Subscription\Controllers\StripeWebhookController;
use App\Yantrana\Components\Configuration\Controllers\ConfigurationController;
use App\Yantrana\Components\Subscription\Controllers\ManualSubscriptionController;
use App\Yantrana\Components\WhatsAppService\Controllers\WhatsAppServiceController;
use App\Yantrana\Components\WhatsAppService\Controllers\WhatsAppTemplateController;
use App\Yantrana\Components\WhatsAppService\Controllers\WhatsAppCommerceController;
use App\Yantrana\Components\WhatsAppService\Controllers\WhatsAppOrderController;
use App\Yantrana\Components\Flows\Controllers\WhatsAppFlowController;
use App\Yantrana\Components\Sheets\Controllers\GoogleSheetScriptController;
use App\Yantrana\Components\Integration\Shopify\Controllers\ShopifyIntegrationController;
use App\Yantrana\Components\Integration\Shopify\Controllers\ShopifyWebhookController;
use App\Yantrana\Components\Integration\WooCommerce\Controllers\WooCommerceIntegrationController;
use App\Yantrana\Components\FacebookService\Controllers\FacebookServiceController;
use App\Yantrana\Components\InstagramService\Controllers\InstagramServiceController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [
    HomeController::class,
    'homePageView',
])->name('landing_page');
// user console
Route::get('/console', function () {
    return hasCentralAccess() ? Redirect::route('central.console') : Redirect::route('vendor.console');
})->name('home');




// authentication routes
require __DIR__ . '/auth.php';

// Instagram routes
require __DIR__ . '/instagram-routes.php';

// Facebook routes
require __DIR__ . '/facebook-routes.php';

// Facebook Chat routes
require __DIR__ . '/facebook-chat-routes.php';
// Authenticated Routes
Route::middleware([
    App\Http\Middleware\Authenticate::class,
])->group(function () {
    /*
    Media Component Routes Start from here
    ------------------------------------------------------------------- */
    Route::group([
        'prefix' => 'media',
    ], function () {
        // Temp Upload
        Route::post('/upload-temp-media/{uploadItem?}', [
            MediaController::class,
            'uploadTempMedia',
        ])->name('media.upload_temp_media');
        // Upload Hero Image
        // Route::post('/upload-hero-image', [
        //     MediaController::class,
        //     'uploadHeroImage',
        // ])->name('media.upload_hero_image');
        // Upload WhatsApp QR Code
        Route::post('/upload-whatsapp-qr', [
            MediaController::class,
            'uploadWhatsAppQR',
        ])->name('media.upload_whatsapp_qr');
    });

    // User consoles
    Route::prefix('user-console')
        ->group(function () {
            // profile form
            Route::get('profile-update', [
                UserController::class,
                'profileEditForm',
            ])->name('user.profile.edit');
            // profile update request
            Route::post('profile-update', [
                UserController::class,
                'updateProfile',
            ])->name('user.profile.update');
        });
    // SuperAdmin Routes
    Route::middleware([
        App\Http\Middleware\CentralAccessCheckpost::class,
    ])->prefix('central-console')
        ->group(function () {
            Route::get('/', [
                DashboardController::class,
                'dashboardView',
            ])->name('central.console');
            // Upload Logo
            Route::post('/upload-logo', [
                MediaController::class,
                'uploadLogo',
            ])->name('media.upload_logo');
            // Upload Small Logo
            Route::post('/upload-small-logo', [
               MediaController::class,
               'uploadSmallLogo'
            ])->name('media.upload_small_logo');
            // Upload Favicon
            Route::post('/upload-favicon', [
                MediaController::class,
                'uploadFavicon',
            ])->name('media.upload_favicon');

            // Add this new route for Hero Image
            Route::post('/upload-hero-image', [
                MediaController::class,
                'uploadHeroImage',
            ])->name('media.upload_hero_image');

            Route::get('/subscription-plans', [
                ConfigurationController::class,
                'subscriptionPlans',
            ])->name('manage.configuration.subscription-plans');

            Route::post('/subscription-plans', [
                ConfigurationController::class,
                'subscriptionPlansProcess',
            ])->name('manage.configuration.subscription-plans.write.update');

            Route::post('/create-stripe-webhook', [
                ConfigurationController::class,
                'createStripeWebhook',
            ])->name('manage.configuration.create_stripe_webhook');

            Route::get('/vendors', function () {
                return view('vendors.list');
            })->name('central.vendors');

            Route::get('/{vendorIdOrUid}/details', [
                vendorController::class,
                'vendorDetails',
            ])->name('central.vendor.details');

            // login as team member
            Route::post("/{vendorUid}/login-as-vendor-admin", [
                VendorController::class,
                'loginAsVendorAdmin'
            ])->name('central.vendors.user.write.login_as');

            Route::post('/dashboard-stats-filter-data/{vendorUid}', [
                DashboardController::class,
                'dashboardStatsDataFilter',
            ])->name('central.read.stat_data_filter');

            Route::get('/subscriptions', function () {
                return view('subscription.list');
            })->name('central.subscriptions');

            // ManualSubscription Routes Group Start
            Route::prefix('/manual-subscriptions')->group(function () {
                // ManualSubscription list view
                Route::get("/", [
                    ManualSubscriptionController::class,
                    'showManualSubscriptionView'
                ])->name('central.subscription.manual_subscription.read.list_view');
                // selected plan details
                Route::post("/selected-plan-details", [
                    ManualSubscriptionController::class,
                    'getSelectedPlanDetails'
                ])->name('central.subscription.manual_subscription.read.selected_plan_details');
                // ManualSubscription list request
                Route::get("/list-data/{vendorUid?}", [
                    ManualSubscriptionController::class,
                    'prepareManualSubscriptionList'
                ])->name('central.subscription.manual_subscription.read.list');

                // ManualSubscription delete process
                Route::post("/{manualSubscriptionIdOrUid}/delete-process", [
                    ManualSubscriptionController::class,
                    'processManualSubscriptionDelete'
                ])->name('central.subscription.manual_subscription.write.delete');

                // ManualSubscription create process
                Route::post("/add-process", [
                    ManualSubscriptionController::class,
                    'processManualSubscriptionCreate'
                ])->name('central.subscription.manual_subscription.write.create');

                // ManualSubscription get the data
                Route::get("/{manualSubscriptionIdOrUid}/get-update-data", [
                    ManualSubscriptionController::class,
                    'updateManualSubscriptionData'
                ])->name('central.subscription.manual_subscription.read.update.data');

                // ManualSubscription update process
                Route::post("/update-process", [
                    ManualSubscriptionController::class,
                    'processManualSubscriptionUpdate'
                ])->name('central.subscription.manual_subscription.write.update');

                // cancel subscription
                Route::post('/cancel-and-discard/{vendorUid}', [
                    SubscriptionController::class,
                    'cancelAndDiscard',
                ])->name('central.subscription.write.cancel');

            });
            // ManualSubscription Routes Group End


            Route::post('/add', [
                VendorController::class,
                'addVendor',
            ])->name('central.vendors.write.add');

            Route::get('/fetch-list', [
                VendorController::class,
                'vendorDataTableList',
            ])->name('central.vendors.read.list');
            /*
            Manage Translations
            ------------------------------------------------------------------- */
            Route::group([
                'namespace' => 'Translation\Controllers',
                'prefix' => 'translations',
            ], function () {
                Route::get('/', [
                    TranslationController::class,
                    'languages',
                ])->name('manage.translations.languages');

                // Store New Language
                Route::post('/process-language-store', [
                    TranslationController::class,
                    'storeLanguage',
                ])->name('manage.translations.write.language_create');

                // Update Language
                Route::post('/process-language-update', [
                    TranslationController::class,
                    'updateLanguage',
                ])->name('manage.translations.write.language_update');
                // Delete Language
                Route::post('/{languageId}/process-language-delete', [
                    TranslationController::class,
                    'deleteLanguage',
                ])->name('manage.translations.write.language_delete');

                Route::get('language/{languageId}', [
                    TranslationController::class,
                    'lists',
                ])->name('manage.translations.lists');

                Route::get('/scan/{languageId}/{preventReload?}', [
                    TranslationController::class,
                    'scan',
                ])->name('manage.translations.scan');

                Route::post('/update', [
                    TranslationController::class,
                    'update',
                ])->name('manage.translations.update');

                Route::get('/export/{languageId}', [
                    TranslationController::class,
                    'export',
                ])->name('manage.translations.export');

                Route::post('/import/{languageId}', [
                    TranslationController::class,
                    'import',
                ])->name('manage.translations.import');

                Route::post('/auto-translate/{serviceId}/{languageId}', [
                    TranslationController::class,
                    'translatePoFile',
                ])->name('manage.translations.auto_translate');

                Route::post('/auto-translate-all/{serviceId}', [
                    TranslationController::class,
                    'translatePoFiles',
                ])->name('manage.translations.auto_translate_all');
            });
            /*
            Configuration Component Routes Start from here
            ------------------------------------------------------------------- */
            Route::group([
                'namespace' => 'Configuration\Controllers',
                'prefix' => 'configuration',
            ], function () {
                // operations
                Route::post('/operations/optimize', [
                    ConfigurationController::class,
                    'optimizeApp',
                ])->name('manage.operations.optimize.write');
                // optimize clear
                Route::post('/operations/optimize-clear', [
                    ConfigurationController::class,
                    'clearOptimize',
                ])->name('manage.operations.clear_optimize.write');

                Route::get('/licence-information', [
                    ConfigurationController::class,
                    'registerProductView',
                ])->name('manage.configuration.product_registration');

                Route::post('/licence-information-process', [
                    ConfigurationController::class,
                    'processProductRegistration',
                ])->name('installation.version.create.registration');

                Route::post('/licence-information-remove-process', [
                    ConfigurationController::class,
                    'processProductRegistrationRemoval',
                ])->name('installation.version.create.remove_registration');
                // View Configuration View
                Route::get('/{pageType}', [
                    ConfigurationController::class,
                    'getConfiguration',
                ])->name('manage.configuration.read');
                // Process Configuration Data
                Route::post('/{pageType}/process-configuration-store', [
                    ConfigurationController::class,
                    'processStoreConfiguration',
                ])->name('manage.configuration.write');
            });
            // manage-vendor Routes Group start
            Route::prefix('/vendors')->group(function () {

                Route::post('/list-data/{vendorIdOrUid}', [
                    vendorController::class,
                    'prepareVendorDelete',
                ])->name('vendor.delete');
                //Vendor permanant delete
                Route::post('/vendor-delete/{vendorIdOrUid}', [
                    vendorController::class,
                    'prepareVendorPermanentDelete',
                ])->name('vendor.permanant.delete');

                // Vendor get the data
                Route::get('/get-update-data/{vendorIdOrUid}', [
                    vendorController::class,
                    'prepareUpdateVendorData',
                ])->name('vendor.read.update.data');
                // Vendor get the data
                Route::post('/update-vendor-data', [
                    vendorController::class,
                    'updateVendorData',
                ])->name('vendor.write.update');
                // route for change password button on author side .
                Route::get('/{vendorIdOrUid}/get-change-password-vendor', [
                    vendorController::class,
                    'changePasswordVendorData',
                ])->name('vendor.change.password.data');

                // route for change password button on super-admin side .
                Route::post('/change-password-vendor', [
                    vendorController::class,
                    'changePasswordVendor',
                ])->name('auth.vendor.change.password');

                // Vendor-dashboard
                Route::get('/{vendorIdOrUid}/dashboard', [
                    vendorController::class,
                    'vendorDashboard',
                ])->name('vendor.dashboard');
            });
            // manage-vendor Routes Group End
            // manage-pages Routes Group start
            Route::prefix('/pages')->group(function () {
                Route::get('/', [
                    PageController::class,
                    'showPageView',
                ])->name('page.list');
                Route::get('/list-data', [
                    PageController::class,
                    'preparePageList',
                ])->name('page.read.list');
                // Page delete process
                Route::post('/{pageIdOrUid}/delete-process', [
                    PageController::class,
                    'processPageDelete',
                ])->name('page.write.delete');

                // Page create process
                Route::post('/add-process', [
                    PageController::class,
                    'processPageCreate',
                ])->name('page.write.create');

                // Page get the data
                Route::get('/{pageIdOrUid}/get-update-data', [
                    PageController::class,
                    'updatePageData',
                ])->name('page.read.update.data');

                // Page update process
                Route::post('/update-process', [
                    PageController::class,
                    'processPageUpdate',
                ])->name('page.write.update');
            });
            // manage-pages Routes Group End
            Route::prefix('/subscription-list')->group(function () {
                Route::get('/', [
                    SubscriptionController::class,
                    'subscriptionList',
                ])->name('central.subscription.read.list');

                Route::post('/delete-subscription-entries', [
                    SubscriptionController::class,
                    'deleteSubscriptionEntries',
                ])->name('central.subscription.write.delete_all_entries');
            });
        });
    // Vendor Routes
    Route::middleware([
        App\Http\Middleware\VendorAccessCheckpost::class,
    ])->prefix('vendor-console')
        ->group(function () {
            Route::get('/', [
                DashboardController::class,
                'vendorDashboardView',
            ])->name('vendor.console');

            Route::post('/dashboard-stats-filter-data', [
                DashboardController::class,
                'dashboardStatsDataFilter',
            ])->name('vendor.read.stat_data_filter');


            // User Routes Group Start

            Route::prefix('/users')->group(function () {
                // User list view
                Route::get("/", [
                    UserController::class,
                    'showUserView'
                ])->name('vendor.user.read.list_view');
                // User list request
                Route::get("/list-data", [
                    UserController::class,
                    'prepareUserList'
                ])->name('vendor.user.read.list');

                // User delete process
                Route::post("/{userIdOrUid}/delete-process", [
                    UserController::class,
                    'processUserDelete'
                ])->name('vendor.user.write.delete');

                // User create process
                Route::post("/add-process", [
                    UserController::class,
                    'processUserCreate'
                ])->name('vendor.user.write.create');

                // User get the data
                Route::get("/{userIdOrUid}/get-update-data", [
                    UserController::class,
                    'updateUserData'
                ])->name('vendor.user.read.update.data');

                // User update process
                Route::post("/update-process", [
                    UserController::class,
                    'processUserUpdate'
                ])->name('vendor.user.write.update');
                // login as team member
                Route::post("/{userIdOrUid}/login-as", [
                    UserController::class,
                    'loginAsUser'
                ])->name('vendor.user.write.login_as');

                Route::post("/logout-as", [
                    UserController::class,
                    'logoutAsUser'
                ])->name('vendor.user.write.logout_as');

                // logged out as vendor admin
                Route::post("/logout-as-vendor-admin", [
                    VendorController::class,
                    'logoutAsVendorAdmin'
                ])->name('central.vendors.user.write.logout_as');

            });
            // User Routes Group End


            Route::prefix('/whatsapp')->group(function () {

                Route::post('/health-status', [
                    WhatsAppServiceController::class,
                    'getHealthStatus',
                ])->name('vendor.whatsapp.health.status');

                Route::post('/sync-phone-numbers', [
                    WhatsAppServiceController::class,
                    'syncPhoneNumbers',
                ])->name('vendor.whatsapp.sync_phone_numbers');

                Route::post('/process-template-change', [
                    WhatsAppServiceController::class,
                    'changeTemplate',
                ])->name('vendor.request.template.view');
                // contact template message view
                Route::get('/contact/send-template-message/{contactUid}', [
                    WhatsAppServiceController::class,
                    'sendTemplateMessageView',
                ])->name('vendor.template_message.contact.view');
                // process template message send
                Route::post('/contact/send-template-message/{contactUid}', [
                    WhatsAppServiceController::class,
                    'sendTemplateMessageProcess',
                ])->name('vendor.template_message.contact.process');

                Route::prefix('/campaign')->group(function () {

                    Route::get('/new', [
                        WhatsAppServiceController::class,
                        'createNewCampaign',
                    ])->name('vendor.campaign.new.view');
                    // campaign schedule
                    Route::post('/schedule', [
                        WhatsAppServiceController::class,
                        'scheduleCampaign',
                    ])->name('vendor.campaign.schedule.process');

                    Route::get('/status/{campaignUid}/view/{pageType?}', [
                        CampaignController::class,
                        'campaignStatusView',
                    ])->name('vendor.campaign.status.view');
                    //campaign queue log list view
                    Route::get('/queue/{campaignUid}', [
                        CampaignController::class,
                        'campaignQueueLogListView',
                    ])->name('vendor.campaign.queue.log.list.view');

                    Route::post('/requeue/{campaignUid}', [
                        WhatsAppServiceController::class,
                        'requeueCampaignFailedMessages',
                    ])->name('vendor.campaign.requeue.log.write.failed');

                    //campaign executed log list view
                    Route::get('/executed/{campaignUid}', [
                       CampaignController::class,
                       'campaignExecutedLogListView',
                    ])->name('vendor.campaign.executed.log.list.view');

                    Route::get('/status/{campaignUid}/data', [
                        CampaignController::class,
                        'campaignStatusData',
                    ])->name('vendor.campaign.status.data');

                    // Campaign list view
                    Route::get('/', [
                        CampaignController::class,
                        'showCampaignView',
                    ])->name('vendor.campaign.read.list_view');
                    // Campaign list request
                    Route::get('/{status}/list-data', [
                        CampaignController::class,
                        'prepareCampaignList',
                    ])->name('vendor.campaign.read.list');

                    // Campaign delete process
                    Route::post('/{campaignIdOrUid}/delete-process', [
                        CampaignController::class,
                        'processCampaignDelete',
                    ])->name('vendor.campaign.write.delete');

                    // Campaign archive process
                    Route::post('/{campaignIdOrUid}/archive-process', [
                        CampaignController::class,
                        'processCampaignArchive',
                    ])->name('vendor.campaign.write.archive');
                    // Campaign unarchive process
                    Route::post('/{campaignIdOrUid}/unarchive-process', [
                      CampaignController::class,
                      'processCampaignUnarchive',
                    ])->name('vendor.campaign.write.unarchive');
                    // Campaign executed report generate
                    Route::get('/campaign-report/{campaignUid}', [
                       CampaignController::class,
                       'processCampaignExecutedReportGenerate',
                     ])->name('vendor.campaign.executed.report.write');
                    // Campaign quelog report generate
                    Route::get('/campaign-queue-log-report/{campaignUid}', [
                       CampaignController::class,
                       'processCampaignQueueLogReportGenerate',
                     ])->name('vendor.campaign.queue.log.report.write');
                    // Campaign failed report generate
                    Route::get('/campaign/failed-messages-report/{campaignUid}', [
                        CampaignController::class,
                        'processGenerateFailedMessagesReport',
                    ])->name('vendor.campaign.failed.messages.report.write');

                });

                // contact chat view
                Route::get('/contact/chat/{contactUid?}', [
                    WhatsAppServiceController::class,
                    'chatView',
                ])->name('vendor.chat_message.contact.view');

                Route::get('/chat/unread-count', [
                    WhatsAppServiceController::class,
                    'unreadCount',
                ])->name('vendor.chat_message.read.unread_count');
                //test route
                Route::get('/contact-chat-report',[
                    WhatsAppServiceController::class,
                    'exportChats',
                ])->name('vendor.chat_message.export');

                

                Route::post('/contact/chat/send', [
                    WhatsAppServiceController::class,
                    'sendChatMessage',
                ])->name('vendor.chat_message.send.process');

                Route::post('/contact/chat/assign-user', [
                    ContactController::class,
                    'assignChatUser',
                ])->name('vendor.chat.assign_user.process');

                Route::post('/contact/chat/assign-labels', [
                    ContactController::class,
                    'assignContactLabels',
                ])->name('vendor.chat.assign_labels.process');

                Route::post('/contact/chat/update-notes', [
                    ContactController::class,
                    'updateNotes',
                ])->name('vendor.chat.update_notes.process');

                Route::get('/contact/labels/{contactUid}', [
                    ContactController::class,
                    'getLabels',
                ])->name('vendor.chat.contact_labels.read');

                Route::post('/contact/create-label', [
                    ContactController::class,
                    'createLabel',
                ])->name('vendor.chat.label.create.write');

                Route::post('/contact/chat/edit-label', [
                    ContactController::class,
                    'updateLabel',
                ])->name('vendor.chat.label.update.write');

                Route::post('/contact/chat/delete-label/{labelUid}', [
                    ContactController::class,
                    'deleteLabelProcess',
                ])->name('vendor.chat.label.delete.write');

                Route::get('/contact/chat/prepare-send-media/{mediaType?}', [
                    WhatsAppServiceController::class,
                    'prepareSendMediaUploader',
                ])->name('vendor.chat_message_media.upload.prepare');

                Route::post('/contact/chat/send-media', [
                    WhatsAppServiceController::class,
                    'sendChatMessageMedia',
                ])->name('vendor.chat_message_media.send.process');

                Route::get('/contact/chat-data/{contactUid}/{way?}', [
                    WhatsAppServiceController::class,
                    'getContactChatData',
                ])->name('vendor.chat_message.data.read');

                Route::get('/contact/contacts-data/{contactUid?}', [
                    WhatsAppServiceController::class,
                    'getContactsData',
                ])->name('vendor.contacts.data.read');

                Route::post('/contact/chat/clear-history/{contactUid}', [
                    WhatsAppServiceController::class,
                    'clearChatHistory',
                ])->name('vendor.chat_message.delete.process');

                Route::prefix('/templates')->group(function () {
                    // WhatsAppService list view
                    Route::get('/', [
                        WhatsAppTemplateController::class,
                        'showTemplatesView',
                    ])->name('vendor.whatsapp_service.templates.read.list_view');
                    // WhatsAppService list request
                    Route::get('/list-data', [
                        WhatsAppTemplateController::class,
                        'prepareTemplatesList',
                    ])->name('vendor.whatsapp_service.templates.read.list');

                    Route::post('/sync', [
                        WhatsAppTemplateController::class,
                        'syncTemplates',
                    ])->name('vendor.whatsapp_service.templates.write.sync');

                    Route::post('/delete/{whatsappTemplateUid}', [
                        WhatsAppTemplateController::class,
                        'deleteTemplate',
                    ])->name('vendor.whatsapp_service.templates.write.delete');

                    Route::get('/create', [
                        WhatsAppTemplateController::class,
                        'createNewTemplate',
                    ])->name('vendor.whatsapp_service.templates.read.new_view');

                    Route::post('/create-process', [
                        WhatsAppTemplateController::class,
                        'createNewTemplateProcess',
                    ])->name('vendor.whatsapp_service.templates.write.create');
                    // update template
                    Route::get('/update/{templateUid}', [
                        WhatsAppTemplateController::class,
                        'updateTemplate',
                    ])->name('vendor.whatsapp_service.templates.read.update_view');

                    Route::post('/update-process', [
                        WhatsAppTemplateController::class,
                        'updateTemplateProcess',
                    ])->name('vendor.whatsapp_service.templates.write.update');

                });
                 Route::prefix('/flows')->group(function () {
                    // WhatsApp Flows routes
                    Route::get('/', [WhatsAppFlowController::class, 'index'])->name('whatsapp-flows.index');
                    Route::get('/create', [WhatsAppFlowController::class, 'create'])->name('whatsapp-flows.create');
                    Route::post('/', [WhatsAppFlowController::class, 'createFlow'])->name('whatsapp.flows.create');
                    Route::get('/{id}', [WhatsAppFlowController::class, 'show'])->name('whatsapp-flows.show');
                    Route::post('/refresh', [WhatsAppFlowController::class, 'refresh'])->name('whatsapp-flows.refresh');
                    Route::get('/{id}/edit', [WhatsAppFlowController::class, 'edit'])->name('whatsapp-flows.edit');
                    Route::delete('/{id}', [WhatsAppFlowController::class, 'delete'])->name('whatsapp-flows.delete');
                    Route::get('/{id}/preview', [WhatsAppFlowController::class, 'preview'])->name('whatsapp-flows.preview');
                    Route::get('/{id}/send', [WhatsAppFlowController::class, 'showSendForm'])->name('whatsapp-flows.send');
                    Route::post('/{id}/send', [WhatsAppFlowController::class, 'send'])->name('whatsapp-flows.send.post');
                    Route::put('/whatsapp-flows/{id}', [WhatsAppFlowController::class, 'update'])->name('whatsapp-flows.update');
    Route::delete('/whatsapp-flows/{id}', [WhatsAppFlowController::class, 'destroy'])->name('whatsapp-flows.destroy');
                });

                // WhatsApp Commerce routes
                Route::prefix('/commerce')->group(function () {
                    // Commerce settings page
                    Route::get('/', [
                        WhatsAppCommerceController::class,
                        'showCommerceSettings'
                    ])->name('vendor.whatsapp.commerce.settings');

                    // Fetch commerce settings from WhatsApp API
                    Route::post('/fetch-settings', [
                        WhatsAppCommerceController::class,
                        'fetchCommerceSettings'
                    ])->name('vendor.whatsapp.commerce.fetch');

                    // Update commerce settings
                    Route::post('/update-settings', [
                        WhatsAppCommerceController::class,
                        'updateCommerceSettings'
                    ])->name('vendor.whatsapp.commerce.update');

                    // Get catalog products
                    Route::get('/products', [
                        WhatsAppCommerceController::class,
                        'getCatalogProducts'
                    ])->name('vendor.whatsapp.commerce.products');

                    // Get commerce settings summary
                    Route::get('/summary', [
                        WhatsAppCommerceController::class,
                        'getCommerceSettingsSummary'
                    ])->name('vendor.whatsapp.commerce.summary');

                    // Auto-sync commerce settings
                    Route::post('/auto-sync', [
                        WhatsAppCommerceController::class,
                        'autoSyncCommerceSettings'
                    ])->name('vendor.whatsapp.commerce.auto_sync');

                    // Test commerce API connection
                    Route::post('/test-connection', [
                        WhatsAppCommerceController::class,
                        'testCommerceConnection'
                    ])->name('vendor.whatsapp.commerce.test');
                });
            });


            // BotReply Routes Group Start
            Route::prefix('/bot-replies')->group(function () {

                // BotReply list view
                Route::get("/", [
                    BotReplyController::class,
                    'showBotReplyView'
                ])->name('vendor.bot_reply.read.list_view');
                // BotReply list request
                Route::get("/list-data", [
                    BotReplyController::class,
                    'prepareBotReplyList'
                ])->name('vendor.bot_reply.read.list');

                // BotReply delete process
                Route::post("/{botReplyIdOrUid}/delete-process", [
                    BotReplyController::class,
                    'processBotReplyDelete'
                ])->name('vendor.bot_reply.write.delete');

                // BotDuplicate process
                Route::post("/{botReplyIdOrUid}/duplicate-process", [
                    BotReplyController::class,
                    'processBotReplyDuplicate'
                ])->name('vendor.bot_reply.write.duplicate');

                // BotReply create process
                Route::post("/add-process", [
                    BotReplyController::class,
                    'processBotReplyCreate'
                ])->name('vendor.bot_reply.write.create');

                // BotReply get the data
                Route::get("/{botReplyIdOrUid}/get-update-data", [
                    BotReplyController::class,
                    'updateBotReplyData'
                ])->name('vendor.bot_reply.read.update.data');

                // BotReply update process
                Route::post("/update-process", [
                    BotReplyController::class,
                    'processBotReplyUpdate'
                ])->name('vendor.bot_reply.write.update');
                // BotFlow Routes Group Start
                Route::prefix('/bot-flows')->group(function () {
                    // BotFlow list view
                    Route::get("/", [
                        BotFlowController::class,
                        'showBotFlowView'
                    ])->name('vendor.bot_reply.bot_flow.read.list_view');
                    // BotFlow list request
                    Route::get("/list-data", [
                        BotFlowController::class,
                        'prepareBotFlowList'
                    ])->name('vendor.bot_reply.bot_flow.read.list');

                    // BotFlow delete process
                    Route::post("/{botFlowIdOrUid}/delete-process", [
                        BotFlowController::class,
                        'processBotFlowDelete'
                    ])->name('vendor.bot_reply.bot_flow.write.delete');

                    // BotFlow create process
                    Route::post("/add-process", [
                        BotFlowController::class,
                        'processBotFlowCreate'
                    ])->name('vendor.bot_reply.bot_flow.write.create');

                    // BotFlow get the data
                    Route::get("/{botFlowIdOrUid}/get-update-data", [
                        BotFlowController::class,
                        'updateBotFlowData'
                    ])->name('vendor.bot_reply.bot_flow.read.update.data');

                    // BotFlow update process
                    Route::post("/update-process", [
                        BotFlowController::class,
                        'processBotFlowUpdate'
                    ])->name('vendor.bot_reply.bot_flow.write.update');
                    // builder
                    Route::prefix('/builder')->group(function () {
                        // flow builder
                        Route::get("/flow/{botFlowIdOrUid}", [
                            BotFlowController::class,
                            'flowBuilderView'
                        ])->name('vendor.bot_reply.bot_flow.builder.read.view');

                        Route::post("/update-flow-data-process", [
                            BotFlowController::class,
                            'botFlowDataUpdate'
                        ])->name('vendor.bot_reply.bot_flow_data.write.update');
                    });
                });
                // BotFlow Routes Group End
            });
            // BotReply Routes Group End


            // Upload
            Route::post('/upload/{uploadItem}', [
                MediaController::class,
                'vendorUpload',
            ])->name('vendor.media.upload');
            //disable message sound notification
            Route::get('/disable-sound-notifications-for-message', [
                VendorSettingsController::class,
                'disableSoundForMessageNotification',
            ])->name('vendor.disable.sound_message_sound_notification.write');

            // Settings page type
            Route::get('/settings/{pageType?}', [
                VendorSettingsController::class,
                'index',
            ])->name('vendor.settings.read');
            // Vendor Settings update
            Route::post('/settings', [
                VendorSettingsController::class,
                'update',
            ])->name('vendor.settings.write.update');

            Route::post('/settings-basic', [
                VendorSettingsController::class,
                'updateBasicSettings',
            ])->name('vendor.settings_basic.write.update');
            
            Route::get('/google-sheet-script', [GoogleSheetScriptController::class, 'index'])
            ->name('google-sheet-script.index');

            Route::post('/google-sheet-script/generate', [GoogleSheetScriptController::class, 'generateScript'])
            ->name('google-sheet-script.generate');

            // WhatsApp Orders Routes
            Route::get('/whatsapp-orders', [
                WhatsAppOrderController::class,
                'showOrdersList',
            ])->name('vendor.whatsapp.orders.list');

            Route::get('/whatsapp-orders/data', [
                WhatsAppOrderController::class,
                'fetchOrders',
            ])->name('vendor.whatsapp.orders.data');

            Route::get('/whatsapp-orders/{orderUid}', [
                WhatsAppOrderController::class,
                'showOrderDetails',
            ])->name('vendor.whatsapp.orders.details');

            Route::patch('/whatsapp-orders/{orderUid}/status', [
                WhatsAppOrderController::class,
                'updateOrderStatus',
            ])->name('vendor.whatsapp.orders.update_status');

            Route::get('/whatsapp-orders/{uid}/pdf', [
                WhatsAppOrderController::class,
                'generatePDF',
            ])->name('vendor.whatsapp.orders.pdf');

            Route::get('/whatsapp-orders/statistics', [
                WhatsAppOrderController::class,
                'getOrderStatistics',
            ])->name('vendor.whatsapp.orders.statistics');

            Route::get('/whatsapp-orders/export', [
                WhatsAppOrderController::class,
                'exportOrders',
            ])->name('vendor.whatsapp.orders.export');

            Route::get('/whatsapp-orders/test-payment-gateway', [
                WhatsAppOrderController::class,
                'testPaymentGateway',
            ])->name('vendor.whatsapp.orders.test_payment_gateway');

            Route::post('/disconnect-webhook', [
                WhatsAppServiceController::class,
                'disconnectWebhook',
            ])->name('vendor.webhook.disconnect.write');

            Route::post('/disconnect-account', [
                WhatsAppServiceController::class,
                'disconnectAccount',
            ])->name('vendor.account.disconnect.write');

            Route::post('/connect-webhook', [
                WhatsAppServiceController::class,
                'connectWebhook',
            ])->name('vendor.webhook.connect.write');

            Route::get('/business-profile/{phoneNumberId}', [
                WhatsAppServiceController::class,
                'getBusinessProfile',
            ])->name('vendor.whatsapp.business_profile.read');

            Route::post('/business-profile/update', [
                WhatsAppServiceController::class,
                'updateBusinessProfile',
            ])->name('vendor.whatsapp.business_profile.write');

            Route::post('/embedded-signup-process', [
                WhatsAppServiceController::class,
                'embeddedSignUpProcess',
            ])->name('vendor.whatsapp_setup.embedded_signup.write');

            // subscriptions
            Route::prefix('/subscription')->group(function () {
                // load subscription page
                Route::get('/', [
                    SubscriptionController::class,
                    'show',
                ])->name('subscription.read.show');
                // cancel subscription
                Route::get('/cancel', [
                    SubscriptionController::class,
                    'cancel',
                ])->name('subscription.write.cancel');
                // resume subscription
                Route::get('/resume', [
                    SubscriptionController::class,
                    'resume',
                ])->name('subscription.write.resume');
                // billing portal
                Route::get('/billing-portal', [
                    SubscriptionController::class,
                    'billingPortal',
                ])->name('subscription.read.billing_portal');
                // Invoice list
                Route::get('/download-invoice/{invoice}', [
                    SubscriptionController::class,
                    'downloadInvoice',
                ])->name('subscription.read.download_invoice');
                // subscribe to plan
                Route::post('/create', [
                    SubscriptionController::class,
                    'create',
                ])->name('subscription.write.create');

                Route::post('/change-plan', [
                    SubscriptionController::class,
                    'changePlan',
                ])->name('subscription.write.change');

                // Offline
                Route::post('/proceed-to-pay', [
                    ManualSubscriptionController::class,
                    'prepareManualPay',
                ])->name('vendor.subscription_manual_pay');

                Route::post('/manual-pay/delete-request', [
                    ManualSubscriptionController::class,
                    'deleteRequest',
                ])->name('vendor.subscription_manual_pay.delete_request');

                Route::post('/manual-pay/enter-payment-details', [
                    ManualSubscriptionController::class,
                    'sendPaymentDetails',
                ])->name('vendor.subscription_manual_pay.send_payment_details');

                Route::get('/manual-pay/upi-payment-request-qr', [
                    HomeController::class,
                    'generateUpiPaymentUrl',
                ])->name('vendor.generate.upi_payment_request');
            });

            //paypal order capture
            Route::post('/paypal/capture-paypal-order', [
               ManualSubscriptionController::class,
               'capturePaypalOrder'
            ])->name('capture.paypal.checkout');

            //razorpay order checkout
            Route::post('/razorpay/checkout', [
               ManualSubscriptionController::class,
               'checkoutRazorpay'
            ])->name('write.razorpay.checkout');

            //payment success page
            Route::get('/{txnId}/payment-success', [
               ManualSubscriptionController::class,
               'paymentSuccess'
            ])->name('payment.success.page');

            // Page Routes Group Start
            // Route::prefix('/pages')->group(function () {

            //     // Page list view
            //     Route::get('/', [
            //         PageController::class,
            //         'showPageView',
            //     ])->name('page.read.list_view');
            //     // Page list request
            //     Route::get('/list-data', [
            //         PageController::class,
            //         'preparePageList',
            //     ])->name('page.read.list');

            //     // Page delete process
            //     Route::post('/{pageIdOrUid}/delete-process', [
            //         PageController::class,
            //         'processPageDelete',
            //     ])->name('page.write.delete');

            //     // Page create process
            //     Route::post('/add-process', [
            //         PageController::class,
            //         'processPageCreate',
            //     ])->name('page.write.create');

            //     // Page get the data
            //     Route::get('/{pageIdOrUid}/get-update-data', [
            //         PageController::class,
            //         'updatePageData',
            //     ])->name('page.read.update.data');

            //     // Page update process
            //     Route::post('/update-process', [
            //         PageController::class,
            //         'processPageUpdate',
            //     ])->name('page.write.update');
            // });
            // Page Routes Group End

            // Contact Routes Group Start

            Route::prefix('/contacts')->group(function () {
                // Contact list view
                Route::get('/list/{groupUid?}', [
                    ContactController::class,
                    'showContactView',
                ])->name('vendor.contact.read.list_view');
                // Contact list request
                Route::get('/list-data/{groupUid?}', [
                    ContactController::class,
                    'prepareContactList',
                ])->name('vendor.contact.read.list');

                // Contact delete process
                Route::post('/{contactIdOrUid}/delete-process', [
                    ContactController::class,
                    'processContactDelete',
                ])->name('vendor.contact.write.delete');
                // Contact remove from group process
                Route::post('/{contactIdOrUid}/{groupUid}/remove-process', [
                   ContactController::class,
                   'processContactRemoveFromGroup',
                ])->name('vendor.contact.write.remove');
                // delete selected contacts
                Route::post('/delete-selected-process', [
                    ContactController::class,
                    'selectedContactsDelete',
                ])->name('vendor.contacts.selected.write.delete');
                // delete all contacts (according to current filter)
                Route::post('/delete-all-process/{groupUid?}', [
                    ContactController::class,
                    'deleteAllContacts',
                ])->name('vendor.contacts.write.delete_all');
                // assign group to selected contacts
                Route::post('/assign-groups-selected-process', [
                    ContactController::class,
                    'assignGroupsToSelectedContacts',
                ])->name('vendor.contacts.selected.write.assign_groups');

                // Contact create process
                Route::post('/add-process', [
                    ContactController::class,
                    'processContactCreate',
                ])->name('vendor.contact.write.create');

                // Contact get the data
                Route::get('/{contactIdOrUid}/get-update-data', [
                    ContactController::class,
                    'updateContactData',
                ])->name('vendor.contact.read.update.data');

                // Contact update process
                Route::post('/update-process', [
                    ContactController::class,
                    'processContactUpdate',
                ])->name('vendor.contact.write.update');

                Route::post('/{contactIdOrUid}/toggle-ai-bot', [
                    ContactController::class,
                    'toggleAiBot',
                ])->name('vendor.contact.write.toggle_ai_bot');

                Route::get('/export/{exportType?}', [
                    ContactController::class,
                    'exportContacts',
                ])->name('vendor.contact.write.export');

                Route::post('/import', [
                    ContactController::class,
                    'importContacts',
                ])->name('vendor.contact.write.import');

                // ContactCustomField Routes Group Start
                Route::prefix('/custom-fields')->group(function () {
                    // ContactCustomField list view
                    Route::get("/", [
                        ContactCustomFieldController::class,
                        'showCustomFieldView'
                    ])->name('vendor.contact.custom_field.read.list_view');
                    // ContactCustomField list request
                    Route::get("/list-data", [
                        ContactCustomFieldController::class,
                        'prepareCustomFieldList'
                    ])->name('vendor.contact.custom_field.read.list');

                    // ContactCustomField delete process
                    Route::post("/{contactCustomFieldIdOrUid}/delete-process", [
                        ContactCustomFieldController::class,
                        'processCustomFieldDelete'
                    ])->name('vendor.contact.custom_field.write.delete');

                    // ContactCustomField create process
                    Route::post("/add-process", [
                        ContactCustomFieldController::class,
                        'processCustomFieldCreate'
                    ])->name('vendor.contact.custom_field.write.create');

                    // ContactCustomField get the data
                    Route::get("/{contactCustomFieldIdOrUid}/get-update-data", [
                        ContactCustomFieldController::class,
                        'updateCustomFieldData'
                    ])->name('vendor.contact.custom_field.read.update.data');

                    // ContactCustomField update process
                    Route::post("/update-process", [
                        ContactCustomFieldController::class,
                        'processCustomFieldUpdate'
                    ])->name('vendor.contact.custom_field.write.update');
                });
                // ContactCustomField Routes Group End

                // ContactGroup Routes Group Start
                Route::prefix('/groups')->group(function () {

                    // ContactGroup list view
                    Route::get('/', [
                        ContactGroupController::class,
                        'showGroupView',
                    ])->name('vendor.contact.group.read.list_view');
                    // ContactGroup list request
                    Route::get('/{status?}/list-data', [
                        ContactGroupController::class,
                        'prepareGroupList',
                    ])->name('vendor.contact.group.read.list');

                    // ContactGroup delete process
                    Route::post('/{contactGroupIdOrUid}/delete-process', [
                        ContactGroupController::class,
                        'processGroupDelete',
                    ])->name('vendor.contact.group.write.delete');
                    // delete selected group
                    Route::post('/delete-selected-process', [
                        ContactGroupController::class,
                        'selectedContactGroupsDelete',
                    ])->name('vendor.contact.group.selected.write.delete');
                    // archive selected group
                    Route::post('/archive-selected-process', [
                      ContactGroupController::class,
                      'selectedContactGroupsArchive',
                ])->name('vendor.contact.group.selected.write.archive');
                    // unarchive selected group
                    Route::post('/unarchive-selected-process', [
                      ContactGroupController::class,
                      'selectedContactGroupsUnarchive',
                ])->name('vendor.contact.group.selected.write.unarchive');
                    // ContactGroup archive process
                    Route::post('/{contactGroupIdOrUid}/archive-process', [
                        ContactGroupController::class,
                        'processGroupArchive',
                    ])->name('vendor.contact.group.write.archive');
                    // ContactGroup archive process
                    Route::post('/{contactGroupIdOrUid}/unarchive-process', [
                        ContactGroupController::class,
                        'processGroupUnarchive',
                    ])->name('vendor.contact.group.write.unarchive');

                    // ContactGroup create process
                    Route::post('/add-process', [
                        ContactGroupController::class,
                        'processGroupCreate',
                    ])->name('vendor.contact.group.write.create');

                    // ContactGroup get the data
                    Route::get('/{contactGroupIdOrUid}/get-update-data', [
                        ContactGroupController::class,
                        'updateGroupData',
                    ])->name('vendor.contact.group.read.update.data');

                    // ContactGroup update process
                    Route::post('/update-process', [
                        ContactGroupController::class,
                        'processGroupUpdate',
                    ])->name('vendor.contact.group.write.update');
                });
                // ContactGroup Routes Group End
            });
            // Contact Routes Group End

            // Integration Routes Group Start
            Route::prefix('/integration')->group(function () {
                
                // Shopify Integration Routes
                Route::prefix('/shopify')->group(function () {
                    
                    // Dashboard
                    Route::get('/dashboard', [
                        ShopifyIntegrationController::class,
                        'dashboard',
                    ])->name('vendor.integration.shopify.dashboard');

                    // Settings
                    Route::get('/settings', [
                        ShopifyIntegrationController::class,
                        'settings',
                    ])->name('vendor.integration.shopify.settings');

                    // Connect integration
                    Route::post('/connect', [
                        ShopifyIntegrationController::class,
                        'connect',
                    ])->name('vendor.integration.shopify.connect');

                    // Disconnect integration
                    Route::post('/disconnect', [
                        ShopifyIntegrationController::class,
                        'disconnect',
                    ])->name('vendor.integration.shopify.disconnect');

                    // Update notification settings
                    Route::post('/update-notification-settings', [
                        ShopifyIntegrationController::class,
                        'updateNotificationSettings',
                    ])->name('vendor.integration.shopify.update_notification_settings');

                    // Get integration status
                    Route::get('/status', [
                        ShopifyIntegrationController::class,
                        'getStatus',
                    ])->name('vendor.integration.shopify.status');

                    // Orders
                    Route::get('/orders', [
                        ShopifyIntegrationController::class,
                        'orders',
                    ])->name('vendor.integration.shopify.orders');

                    // Order details
                    Route::get('/orders/{orderId}', [
                        ShopifyIntegrationController::class,
                        'orderDetails',
                    ])->name('vendor.integration.shopify.order_details');

                    // Notifications
                    Route::get('/notifications', [
                        ShopifyIntegrationController::class,
                        'notifications',
                    ])->name('vendor.integration.shopify.notifications');

                    // Resend notification
                    Route::post('/notifications/{notificationId}/resend', [
                        ShopifyIntegrationController::class,
                        'resendNotification',
                    ])->name('vendor.integration.shopify.resend_notification');

                    // Get statistics
                    Route::get('/statistics', [
                        ShopifyIntegrationController::class,
                        'getStatistics',
                    ])->name('vendor.integration.shopify.statistics');

                    // Test webhook
                    Route::post('/test-webhook', [
                        ShopifyIntegrationController::class,
                        'testWebhook',
                    ])->name('vendor.integration.shopify.test_webhook');

                    // Test method for debugging
                    Route::get('/test', [
                        ShopifyIntegrationController::class,
                        'testMethod',
                    ])->name('vendor.integration.shopify.test');
                    
                    // Test notifications page
                    Route::get('/test-notifications', [
                        ShopifyIntegrationController::class,
                        'testNotifications',
                    ])->name('vendor.integration.shopify.test_notifications');
                    
                    // Test notification sending
                    Route::post('/test-notification', [
                        ShopifyIntegrationController::class,
                        'testNotification',
                    ])->name('vendor.integration.shopify.test_notification');
                    
                    // Test order data
                    Route::get('/test-order-data/{orderId}', [
                        ShopifyIntegrationController::class,
                        'testOrderData',
                    ])->name('vendor.integration.shopify.test_order_data');
                });
                
                // WooCommerce Integration Routes
                Route::prefix('/woocommerce')->group(function () {
                    
                    // Dashboard
                    Route::get('/dashboard', [
                        WooCommerceIntegrationController::class,
                        'dashboard',
                    ])->name('vendor.integration.woocommerce.dashboard');

                    // Settings
                    Route::get('/settings', [
                        WooCommerceIntegrationController::class,
                        'settings',
                    ])->name('vendor.integration.woocommerce.settings');

                    // Connect integration
                    Route::post('/connect', [
                        WooCommerceIntegrationController::class,
                        'connect',
                    ])->name('vendor.integration.woocommerce.connect');

                    // Disconnect integration
                    Route::post('/disconnect', [
                        WooCommerceIntegrationController::class,
                        'disconnect',
                    ])->name('vendor.integration.woocommerce.disconnect');

                    // Update notification settings
                    Route::post('/update-notification-settings', [
                        WooCommerceIntegrationController::class,
                        'updateNotificationSettings',
                    ])->name('vendor.integration.woocommerce.update_notification_settings');

                    // Get integration status
                    Route::get('/status', [
                        WooCommerceIntegrationController::class,
                        'getStatus',
                    ])->name('vendor.integration.woocommerce.status');

                    // Orders
                    Route::get('/orders', [
                        WooCommerceIntegrationController::class,
                        'orders',
                    ])->name('vendor.integration.woocommerce.orders');

                    // Order details
                    Route::get('/orders/{orderId}', [
                        WooCommerceIntegrationController::class,
                        'orderDetails',
                    ])->name('vendor.integration.woocommerce.order_details');

                    // Notifications
                    Route::get('/notifications', [
                        WooCommerceIntegrationController::class,
                        'notifications',
                    ])->name('vendor.integration.woocommerce.notifications');

                    // Resend notification
                    Route::post('/notifications/{notificationId}/resend', [
                        WooCommerceIntegrationController::class,
                        'resendNotification',
                    ])->name('vendor.integration.woocommerce.resend_notification');

                    // Get statistics
                    Route::get('/statistics', [
                        WooCommerceIntegrationController::class,
                        'getStatistics',
                    ])->name('vendor.integration.woocommerce.statistics');



                    // Test method for debugging
                    Route::get('/test', [
                        WooCommerceIntegrationController::class,
                        'testMethod',
                    ])->name('vendor.integration.woocommerce.test');
                    
                    // Test notifications page
                    Route::get('/test-notifications', [
                        WooCommerceIntegrationController::class,
                        'testNotifications',
                    ])->name('vendor.integration.woocommerce.test_notifications');
                    
                    // Test notification sending
                    Route::post('/test-notification', [
                        WooCommerceIntegrationController::class,
                        'testNotification',
                    ])->name('vendor.integration.woocommerce.test_notification');
                });
            });
            // Integration Routes Group End
        });
});
// subscription payment webhook for stripe
Route::post(
    '/stripe/webhook',
    [StripeWebhookController::class, 'handleWebhook']
)->name('cashier.webhook');

Route::get('/change-language/{localeID}', [
    UserController::class,
    'changeLocale',
])->name('locale.change');
//contact page view
Route::get('/contact', [
    HomeController::class,
    'contactForm',
])->name('user.contact.form');
//about us page view
Route::get('/about-us', [
    HomeController::class,
    'aboutUsForm',
])->name('user.about.form');

//thank you page view
Route::get('/thank-you', [
    HomeController::class,
    'thankYouPage',
])->name('user.thank_you.page');

// page preview
Route::get('/page/{pageUId}/{slug}', [
    HomeController::class,
    'previewPage'
])->name('page.preview');
// Contact process
Route::post('/contact-process', [
    HomeController::class,
    'contactProcess',
])->name('user.contact.process');

// Test mail configuration (for debugging)
Route::get('/test-mail-config', function() {
    $useEnvSettings = getAppSettings('use_env_default_email_settings');
    $contactEmail = getAppSettings('contact_email');

    return response()->json([
        'use_env_default_email_settings' => $useEnvSettings,
        'contact_email' => $contactEmail,
        'env_mail_settings' => [
            'MAIL_MAILER' => env('MAIL_MAILER'),
            'MAIL_HOST' => env('MAIL_HOST'),
            'MAIL_PORT' => env('MAIL_PORT'),
            'MAIL_USERNAME' => env('MAIL_USERNAME'),
            'MAIL_PASSWORD' => env('MAIL_PASSWORD') ? '***SET***' : 'NOT_SET',
            'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
            'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
            'MAIL_FROM_NAME' => env('MAIL_FROM_NAME'),
        ],
        'app_mail_settings' => [
            'mail_driver' => getAppSettings('mail_driver'),
            'mail_from_address' => getAppSettings('mail_from_address'),
            'mail_from_name' => getAppSettings('mail_from_name'),
            'smtp_mail_host' => getAppSettings('smtp_mail_host'),
            'smtp_mail_port' => getAppSettings('smtp_mail_port'),
            'smtp_mail_username' => getAppSettings('smtp_mail_username'),
            'smtp_mail_password_or_apikey' => getAppSettings('smtp_mail_password_or_apikey') ? '***SET***' : 'NOT_SET',
            'smtp_mail_encryption' => getAppSettings('smtp_mail_encryption'),
        ],
        'current_config' => [
            'mail.default' => config('mail.default'),
            'mail.from.address' => config('mail.from.address'),
            'mail.from.name' => config('mail.from.name'),
        ]
    ]);
})->name('test.mail.config');

// Test send email (for debugging)
Route::get('/test-send-email', function() {
    try {
        $contactEmail = getAppSettings('contact_email');
        if (empty($contactEmail) || $contactEmail === 'your-contact-email@domain.com') {
            return response()->json(['error' => 'Contact email not configured: ' . $contactEmail]);
        }

        $emailData = [
            'userName' => 'Test User',
            'senderEmail' => 'test@example.com',
            'toEmail' => $contactEmail,
            'subject' => 'Test Email',
            'messageText' => 'This is a test email to verify mail configuration.',
        ];

        $baseMailer = app(\App\Yantrana\Base\BaseMailer::class);
        $result = $baseMailer->notifyAdmin('Test Email', 'contact', $emailData, 2);

        return response()->json([
            'success' => $result,
            'message' => $result ? 'Email sent successfully!' : 'Failed to send email',
            'contact_email' => $contactEmail
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Exception: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->name('test.send.email');

// compiled js code serverside to make translations ready strings etc
Route::get('/server-compiled.js', [
    HomeController::class,
    'serverCompiledJs',
])->name('vendor.load_server_compiled_js');

Route::get('/terms-and-policies/{contentName?}', [
    HomeController::class,
    'viewTermsAndPolicies',
])->name('app.terms_and_policies');
// whatsapp qr code
Route::get('/whatsapp-qr/{vendorUid}/{phoneNumber}', [
    HomeController::class,
    'generateWhatsAppQR',
])->name('vendor.whatsapp_qr');

// Vendor specific styles like store colors etc
Route::get('/custom-styles.css', [
    HomeController::class,
    'customStyles'
])->name('app.load_custom_style');

// whatsapp webhook
Route::any('whatsapp-webhook/{vendorUid}', [
    WhatsAppServiceController::class,
    'webhook',
])->name('vendor.whatsapp_webhook');

// Shopify webhook (without any middleware)
Route::any('shopify-webhook/{vendorId?}', [
    ShopifyWebhookController::class,
    'handleWebhook',
])->name('shopify.webhook')->withoutMiddleware(['web', 'auth', 'session']);

// Test route for debugging
Route::get('/test-shopify-route', function() {
    return response()->json(['success' => true, 'message' => 'Shopify route test successful']);
})->name('test.shopify.route');

// Test Shopify connect route (outside vendor middleware)
Route::post('/test-shopify-connect', [
    App\Yantrana\Components\Integration\Shopify\Controllers\ShopifyIntegrationController::class,
    'connect',
])->name('test.shopify.connect');

// Shopify webhook with vendor ID (without any middleware)
Route::any('shopify-webhook/vendor/{vendorId}', [
    ShopifyWebhookController::class,
    'handleWebhookWithVendor',
])->name('shopify.webhook.vendor')->withoutMiddleware(['web', 'auth', 'session']);

// Shopify webhook verification (without any middleware)
Route::any('shopify-webhook/verify', [
    ShopifyWebhookController::class,
    'verifyWebhook',
])->name('shopify.webhook.verify')->withoutMiddleware(['web', 'auth', 'session']);

// Shopify webhook test endpoint (without any middleware)
Route::any('shopify-webhook/test/{vendorId?}', [
    ShopifyWebhookController::class,
    'testWebhook',
])->name('shopify.webhook.test')->withoutMiddleware(['web', 'auth', 'session']);

// Shopify webhook debug endpoint (without any middleware)
Route::any('shopify-webhook/debug/{vendorId?}', [
    ShopifyWebhookController::class,
    'debugCustomerData',
])->name('shopify.webhook.debug')->withoutMiddleware(['web', 'auth', 'session']);

// WooCommerce webhook (without any middleware)
Route::any('woocommerce-webhook/{vendorId?}', [
    App\Yantrana\Components\Integration\WooCommerce\Controllers\WooCommerceWebhookController::class,
    'handleWebhook',
])->name('webhook.woocommerce')->withoutMiddleware(['web', 'auth', 'session']);

// WooCommerce webhook with vendor ID (without any middleware)
Route::any('woocommerce-webhook/vendor/{vendorId}', [
    App\Yantrana\Components\Integration\WooCommerce\Controllers\WooCommerceWebhookController::class,
    'handleWebhookWithVendor',
])->name('webhook.woocommerce.vendor')->withoutMiddleware(['web', 'auth', 'session']);

// WooCommerce webhook verification (without any middleware)
Route::any('woocommerce-webhook/verify', [
    App\Yantrana\Components\Integration\WooCommerce\Controllers\WooCommerceWebhookController::class,
    'verifyWebhook',
])->name('webhook.woocommerce.verify')->withoutMiddleware(['web', 'auth', 'session']);



// Payment webhooks (outside vendor middleware)
Route::post('/whatsapp/payment/webhook/razorpay', [
    WhatsAppOrderController::class,
    'handleRazorpayWebhook',
])->name('whatsapp.payment.webhook.razorpay');

Route::post('/whatsapp/payment/webhook/phonepe', [
    WhatsAppOrderController::class,
    'handlePhonePeWebhook',
])->name('whatsapp.payment.webhook.phonepe');

// Payment success page
Route::get('/whatsapp/payment/success', [
    WhatsAppOrderController::class,
    'paymentSuccess',
])->name('whatsapp.payment.success');

// for cron job to run schedule
Route::get('/run-cron-schedule/{token?}', [
    WhatsAppServiceController::class,
    'runCampaignSchedule',
])->name('campaign.run_schedule.process');

Route::get('/licence-information-remove-process-remote', [
    ConfigurationController::class,
    'processProductRegistrationRemoval',
])->name('installation.version.create.remove_registration_remote');


// RazorPay Webhook
Route::post('/razorpay/order-payment-razorpay-webhook', [
    ManualSubscriptionController::class,
    'handleOrderPaymentRazorpayWebhook'
])->name('razorpay-webhook');
