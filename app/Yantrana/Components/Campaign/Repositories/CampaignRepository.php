<?php
/**
* CampaignRepository.php - Repository file
*
* This file is part of the Campaign component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Campaign\Repositories;

use App\Yantrana\Base\BaseRepository;
use App\Yantrana\Components\Campaign\Interfaces\CampaignRepositoryInterface;
use App\Yantrana\Components\Campaign\Models\CampaignModel;
use App\Yantrana\Components\WhatsAppService\Models\WhatsAppMessageLogModel;
use App\Yantrana\Components\WhatsAppService\Models\WhatsAppMessageQueueModel;
use Illuminate\Support\Facades\DB;

class CampaignRepository extends BaseRepository implements CampaignRepositoryInterface
{
    /**
     * primary model instance
     *
     * @var object
     */
    protected $primaryModel = CampaignModel::class;

    /**
     * Fetch campaign datatable source
     *
     * @return mixed
     *---------------------------------------------------------------- */
    public function fetchCampaignDataTableSource($status)
    {
        if($status == "archived") {
            $status = 5;
        } else {
            $status = 1;
        }

        // basic configurations for dataTables data
        $dataTableConfig = [
            // searchable columns
            'searchable' => [
                'campaigns.title',
                'campaigns.whatsapp_templates__id',
                'campaigns.scheduled_at',
                'whatsapp_templates.template_name',
                'whatsapp_templates.language',
            ],
            'fieldAlias' => [
                'contacts_count' => '__data->total_contacts'
            ]
        ];
        // get Model result for dataTables with template join
        return $this->primaryModel::where([
            'campaigns.vendors__id' => getVendorId()
            ])
            ->where('campaigns.status', '=', $status)
            ->leftJoin('whatsapp_templates', 'campaigns.whatsapp_templates__id', '=', 'whatsapp_templates._id')
            ->select('campaigns.*', 'whatsapp_templates.template_name as template_name_from_table', 'whatsapp_templates.language as template_language_from_table')
            ->withCount([
            'messageLog',
            'queuePendingMessages',
            'queueProcessingMessages',
        ])->dataTables($dataTableConfig)->toArray();
    }

    /**
     * Get the campaign data
     *
     * @param int $campaignId
     * @return Eloquent
     */
    public function getCampaignData($campaignId)
    {
        return $this->primaryModel::where([
            'vendors__id' => getVendorId(),
            '_uid' => $campaignId,
        ])
        ->withCount('messageLog')->withCount([
            'queuePendingMessages',
            'queueProcessingMessages',
        ])->with(['messageLog', 'queueMessages', 'whatsappTemplate'])->first();
    }

    /**
     * Delete $campaign record and return response
     *
     * @param  object  $inputData
     * @return mixed
     *---------------------------------------------------------------- */
    public function deleteCampaign($campaign)
    {
        // Check if $campaign deleted
        if ($campaign->deleteIt()) {
            // if deleted
            return true;
        }
        // if failed to delete
        return false;
    }

    /**
     * Store new campaign record and return response
     *
     * @param  array  $inputData
     * @return mixed
     *---------------------------------------------------------------- */
    public function storeCampaign($inputData)
    {
        // prepare data to store
        $keyValues = [
            'title',
            'template_name',
            'whatsapp_templates__id' => $inputData['whatsapp_template'],
            'scheduled_at' => $inputData['schedule_at'],
        ];
        return $this->storeIt($inputData, $keyValues);
    }

    /**
     * Fetch campaign queue log datatable source
     *
     * @return mixed
     *---------------------------------------------------------------- */
    public function fetchCampaignQueueLogTableSource($campaignId)
    {
        // basic configurations for dataTables data
        $dataTableConfig = [
            // searchable columns
            'searchable' => [
                'fullName' => DB::raw("LOWER(CONCAT(
                    JSON_UNQUOTE(JSON_EXTRACT(__data, '$.contact_data.first_name')), ' ',
                    JSON_UNQUOTE(JSON_EXTRACT(__data, '$.contact_data.last_name'))
                ))"),
                'updated_at',
                'status',
                'phone_with_country_code',
            ],
        ];
        // search name in json
        request()->merge([
            'search' => [
                'value' => strtolower(request()->search['value'] ?? ''),
                'regex' => request()->search['regex'] ?? null
            ]
        ]);
        // Get Model result for dataTables
        return WhatsAppMessageQueueModel::where('campaigns__id', $campaignId)
        ->select(
            DB::raw(
                "*,
            JSON_UNQUOTE(JSON_EXTRACT(__data, '$.contact_data.first_name')) as first_name,
            JSON_UNQUOTE(JSON_EXTRACT(__data, '$.contact_data.last_name')) as last_name,
            CONCAT(
                JSON_UNQUOTE(JSON_EXTRACT(__data, '$.contact_data.first_name')), ' ',
                JSON_UNQUOTE(JSON_EXTRACT(__data, '$.contact_data.last_name'))
            ) as full_name"
            )
        )
            ->dataTables($dataTableConfig)
            ->toArray();
    }
    /**
    * Fetch campaign datatable source
    *
    * @return mixed
    *---------------------------------------------------------------- */
    public function fetchCampaignExecutedLogTableSource($campaignId)
    {
        // basic configurations for dataTables data
        $dataTableConfig = [
            // searchable columns
            'searchable' => [
                'fullName' => DB::raw("LOWER(CONCAT(
                    JSON_UNQUOTE(JSON_EXTRACT(__data, '$.contact_data.first_name')), ' ',
                    JSON_UNQUOTE(JSON_EXTRACT(__data, '$.contact_data.last_name'))
                ))"),
                'contact_wa_id',
                'messaged_at',
                'updated_at',
                'status',
            ],
        ];
        // search name in json
        request()->merge([
            'search' => [
                'value' => strtolower(request()->search['value'] ?? ''),
                'regex' => request()->search['regex'] ?? null
            ]
        ]);
        // get Model result for dataTables
        return WhatsAppMessageLogModel::where('campaigns__id', $campaignId)
        ->select(
            DB::raw(
                "*,
            JSON_UNQUOTE(JSON_EXTRACT(__data, '$.contact_data.first_name')) as first_name,
            JSON_UNQUOTE(JSON_EXTRACT(__data, '$.contact_data.last_name')) as last_name,
            CONCAT(
                JSON_UNQUOTE(JSON_EXTRACT(__data, '$.contact_data.first_name')), ' ',
                JSON_UNQUOTE(JSON_EXTRACT(__data, '$.contact_data.last_name'))
            ) as full_name"
            )
        )
        ->dataTables($dataTableConfig)
        ->toArray();
    }
    /**
     * Get the campaign  Executed data
     *
     * @param int  $campaignId
     *
     * @return LazyCollection
     */
    public function fetchCampaignExecutedDataLazily($campaignId, $callback)
    {
        return WhatsAppMessageLogModel::where('campaigns__id', $campaignId)->lazy()->each($callback);
    }
    /**
    * Get the campaign queue log data
    *
    * @param int $campaignId

    * @return LazyCollection
    */
    public function fetchCampaignQueueLogDataLazily($campaignId, $callback)
    {
        return WhatsAppMessageQueueModel::where('campaigns__id', $campaignId)->lazy()->each($callback);
    }
}
