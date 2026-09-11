<?php
/**
* CampaignController.php - Controller file
*
* This file is part of the Campaign component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Campaign\Controllers;

use App\Yantrana\Base\BaseController;
use App\Yantrana\Base\BaseRequest;
use App\Yantrana\Components\Campaign\CampaignEngine;
use App\Yantrana\Components\Campaign\Models\CampaignModel;
use App\Yantrana\Components\WhatsAppService\Controllers\WhatsAppServiceController;
use App\Yantrana\Components\WhatsAppService\WhatsAppServiceEngine;
use Illuminate\Support\Arr;

class CampaignController extends BaseController
{
    /**
     * @var CampaignEngine - Campaign Engine
     */
    protected $campaignEngine;

    /**
     * Constructor
     *
     * @param  CampaignEngine  $campaignEngine  - Campaign Engine
     * @return void
     *-----------------------------------------------------------------------*/
    public function __construct(CampaignEngine $campaignEngine)
    {
        $this->campaignEngine = $campaignEngine;
    }

    /**
     * list of Campaign
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function showCampaignView()
    {
        validateVendorAccess('manage_campaigns');
        // load the view
        return $this->loadView('campaign.list');
    }

    /**
     * Campaign process delete
     *
     * @param  mix  $campaignUid
     * @return json object
     *---------------------------------------------------------------- */
    public function campaignStatusData($campaignUid, BaseRequest $request)
    {
        validateVendorAccess('manage_campaigns');
        // ask engine to process the request
        $processReaction = $this->campaignEngine->prepareCampaignData($campaignUid);
        // get back to controller with engine response
        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * list of Campaign
     *
     * @return json object
     *---------------------------------------------------------------- */
    public function prepareCampaignList($status)
    {
        validateVendorAccess('manage_campaigns');
        // respond with dataTables preparations
        return $this->campaignEngine->prepareCampaignDataTableSource($status);
    }

    /**
     * Campaign process delete
     *
     * @param  mix  $campaignIdOrUid
     * @return json object
     *---------------------------------------------------------------- */
    public function processCampaignDelete($campaignIdOrUid, BaseRequest $request)
    {
        validateVendorAccess('manage_campaigns');
        // ask engine to process the request
        $processReaction = $this->campaignEngine->processCampaignDelete($campaignIdOrUid);
        // get back to controller with engine response
        return $this->processResponse($processReaction, [], [], true);
    }
    /**
    * Campaign process archive
    *
    * @param  mix  $campaignIdOrUid
    * @return json object
    *---------------------------------------------------------------- */
    public function processCampaignArchive($campaignIdOrUid, BaseRequest $request)
    {
        validateVendorAccess('manage_campaigns');
        // ask engine to process the request
        $processReaction = $this->campaignEngine->processCampaignArchive($campaignIdOrUid);
        // get back to controller with engine response
        return $this->processResponse($processReaction, [], [], true);
    }
    /**
    * Campaign process unarchive
    *
    * @param  mix  $campaignIdOrUid
    * @return json object
    *---------------------------------------------------------------- */
    public function processCampaignUnarchive($campaignIdOrUid, BaseRequest $request)
    {
        validateVendorAccess('manage_campaigns');
        // ask engine to process the request
        $processReaction = $this->campaignEngine->processCampaignUnarchive($campaignIdOrUid);
        // get back to controller with engine response
        return $this->processResponse($processReaction, [], [], true);
    }

    /**
     * Campaign get update data
     *
     * @param  mix  $campaignIdOrUid
     * @return json object
     *---------------------------------------------------------------- */
    public function updateCampaignData($campaignIdOrUid)
    {
        validateVendorAccess('manage_campaigns');
        $processReaction = $this->campaignEngine->prepareCampaignUpdateData($campaignIdOrUid);
        // get back with response
        return $this->processResponse($processReaction, [], [], true);
    }
    /**
    * Campaign get status view
    *
    * @param  mix  $campaignIdOrUid
    * @return json object
    *---------------------------------------------------------------- */
    public function campaignStatusView($campaignUid, $pageType = null)
    {
        validateVendorAccess('manage_campaigns');
        $campaignDataResponse = $this->campaignEngine->prepareCampaignData($campaignUid);
        $gotoPage = 'queue';
        if(!$pageType and ($campaignDataResponse->data('campaignStatus') == 'executed') or ($pageType == 'executed')) {
            $gotoPage = 'executed';
        }

        $campaignDataResponse->updateData(
            'pageType',
            $gotoPage
        );
        return $this->loadView('whatsapp.campaign-status', $campaignDataResponse->data());
    }

    /**
      * list of campaign queue log
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function campaignQueueLogListView($campaignIdOrUid)
    {
        validateVendorAccess('manage_campaigns');
        // respond with dataTables preparations
        return $this->campaignEngine->prepareCampaignQueueLogList($campaignIdOrUid);
    }

    /**
      * list of executed queue log
      *
      * @return  json object
      *---------------------------------------------------------------- */

    public function campaignExecutedLogListView($campaignIdOrUid)
    {
        validateVendorAccess('manage_campaigns');
        // respond with dataTables preparations
        return $this->campaignEngine->prepareCampaignExecutedLogList($campaignIdOrUid);
    }

    /**
         * campaign Executed report
         *
         * @param string|null $exportType
         * @param string|null $campaignUid
         * @return file
         */
    public function processCampaignExecutedReportGenerate($campaignUid)
    {
        validateVendorAccess('manage_campaigns');
        return $this->campaignEngine->processGenerateCampaignExecutedReport($campaignUid);
    }

    /**
     * campaign Executed report
     *
     * @param string $exportType
     * @return file
     */
    public function processCampaignQueueLogReportGenerate($campaignUid = null)
    {
        return $this->campaignEngine->processGenerateQueueLogCampaignReport($campaignUid);
    }

    /**
     * Duplicate campaign based on stored recreate payload and redirect to dashboard
     */
    public function processCampaignDuplicate($campaignIdOrUid, BaseRequest $request, WhatsAppServiceEngine $whatsAppServiceEngine)
    {
        validateVendorAccess('manage_campaigns');

        $campaign = CampaignModel::where([
            '_uid' => $campaignIdOrUid,
            'vendors__id' => getVendorId(),
        ])->first();

        abortIf(__isEmpty($campaign), 404, __tr('Campaign not found'));

        $payload = Arr::get($campaign->__data ?? [], 'campaign_recreate_payload');
        abortIf(__isEmpty($payload), 422, __tr('Nothing to duplicate from this campaign.'));

        // Build a request-like array for scheduleCampaign -> processCampaignCreate
        $duplicateRequest = [
            // template identity
            'template_uid' => Arr::get($payload, 'template.template_uid'),
            // sender number
            'from_phone_number_id' => Arr::get($payload, 'sender.from_phone_number_id'),
            // audience
            'contact_group' => Arr::get($payload, 'audience.contact_group'),
            'phone_numbers' => implode(',', (array) Arr::get($payload, 'audience.phone_numbers', [])),
            // schedule
            'timezone' => Arr::get($payload, 'schedule.timezone') ?: getVendorSettings('timezone'),
            'schedule_at' => Arr::get($payload, 'schedule.scheduled_at'),
            // options
            'restrict_by_templated_contact_language' => Arr::get($payload, 'options.restrict_by_templated_contact_language') ? 'on' : null,
            // title
            'title' => rtrim((string) Arr::get($payload, 'title')) . ' - clone'
        ];

        // Merge inputs back so engine can rebuild components
        $duplicateRequest = array_merge($duplicateRequest, (array) Arr::get($payload, 'inputs', []));

        // If schedule_now is true, skip schedule_at
        if (Arr::get($payload, 'schedule.schedule_now') === true) {
            $duplicateRequest['schedule_now'] = 'on';
            unset($duplicateRequest['schedule_at']);
        }

        $processReaction = $whatsAppServiceEngine->processCampaignCreate($duplicateRequest);
        if ($processReaction->failed()) {
            return $this->processResponse($processReaction);
        }

        // Redirect to campaign dashboard like original flow
        return $this->responseAction(
            $this->processResponse($processReaction),
            $this->redirectTo('vendor.campaign.status.view', [
                'campaignUid' => $processReaction->data('campaignUid'),
            ], [
                $processReaction->message(),
                'success',
            ])
        );
    }
}
