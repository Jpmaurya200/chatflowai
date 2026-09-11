<?php
/**
* ContactEngine.php - Main component file
*
* This file is part of the Contact component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Contact;

use XLSXWriter;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Yantrana\Base\BaseEngine;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use App\Yantrana\Components\User\Repositories\UserRepository;
use App\Yantrana\Support\Country\Repositories\CountryRepository;
use App\Yantrana\Components\Contact\Repositories\LabelRepository;
use App\Yantrana\Components\Contact\Repositories\ContactRepository;
use App\Yantrana\Components\Contact\Interfaces\ContactEngineInterface;
use App\Yantrana\Components\Contact\Repositories\ContactGroupRepository;
use App\Yantrana\Components\Contact\Repositories\ContactLabelRepository;
use App\Yantrana\Components\Contact\Repositories\GroupContactRepository;
use App\Yantrana\Components\Contact\Repositories\ContactCustomFieldRepository;

class ContactEngine extends BaseEngine implements ContactEngineInterface
{
    /**
     * @var ContactRepository - Contact Repository
     */
    protected $contactRepository;

    /**
     * @var ContactGroupRepository - ContactGroup Repository
     */
    protected $contactGroupRepository;

    /**
     * @var GroupContactRepository - ContactGroup Repository
     */
    protected $groupContactRepository;

    /**
     * @var ContactCustomFieldRepository - ContactGroup Repository
     */
    protected $contactCustomFieldRepository;
    /**
     * @var UserRepository - User Repository
     */
    protected $userRepository;
    /**
     * @var LabelRepository - Label Repository
     */
    protected $labelRepository;
    /**
     * @var ContactLabelRepository - Contact Label Repository
     */
    protected $contactLabelRepository;

    /**
     * Constructor
     *
     * @param  ContactRepository  $contactRepository  - Contact Repository
     * @param  ContactGroupRepository  $contactGroupRepository  - ContactGroup Repository
     * @param  GroupContactRepository  $groupContactRepository  - Group Contacts Repository
     * @param  ContactCustomFieldRepository  $contactCustomFieldRepository  - Contacts Custom  Fields Repository
     * @param  UserRepository  $userRepository  - User Fields Repository
     * @param  LabelRepository  $labelRepository  - Labels Repository
     * @param  ContactLabelRepository  $contactLabelRepository  - Contact Labels Repository
     *
     * @return void
     *-----------------------------------------------------------------------*/
    public function __construct(
        ContactRepository $contactRepository,
        ContactGroupRepository $contactGroupRepository,
        GroupContactRepository $groupContactRepository,
        ContactCustomFieldRepository $contactCustomFieldRepository,
        UserRepository $userRepository,
        LabelRepository $labelRepository,
        ContactLabelRepository $contactLabelRepository,
    ) {
        $this->contactRepository = $contactRepository;
        $this->contactGroupRepository = $contactGroupRepository;
        $this->groupContactRepository = $groupContactRepository;
        $this->contactCustomFieldRepository = $contactCustomFieldRepository;
        $this->userRepository = $userRepository;
        $this->labelRepository = $labelRepository;
        $this->contactLabelRepository = $contactLabelRepository;
    }

    /**
     * Contact datatable source
     *
     * @return array
     *---------------------------------------------------------------- */
    public function prepareContactDataTableSource($contactGroupUid = null)
    {
        $contactGroupId = null;
        // if for specific group
        if ($contactGroupUid) {
            $vendorId = getVendorId();
            $contactGroup = $this->contactGroupRepository->fetchIt([
                '_uid' => $contactGroupUid,
                'vendors__id' => $vendorId,
            ]);
            if (!__isEmpty($contactGroup)) {
                // OPTIMIZED: Pass group ID instead of loading all contact IDs
                // This prevents loading 300K+ IDs into memory
                $contactGroupId = $contactGroup->_id;
            }
        }
        $contactCollection = $this->contactRepository->fetchContactDataTableSource($contactGroupId, $contactGroupUid);
        $listOfCountries = getCountryPhoneCodes();
        // required columns for DataTables
        $requireColumns = [
            '_id',
            '_uid',
            'first_name',
            'last_name',
            'language_code',
            'whatsapp_opt_out' => function ($rowData) {
                if ($rowData['whatsapp_opt_out']) {
                    return '<span class="badge px-2.5 py-1 rounded-pill" style="background-color:#fee2e2; color:#b91c1c; font-size:11px;"><i class="fas fa-ban me-1"></i> ' . __tr('Opted Out') . '</span>';
                }
                return '<span class="badge px-2.5 py-1 rounded-pill" style="background-color:#ecfdf5; color:#059669; font-size:11px;"><i class="fas fa-check-circle me-1"></i> ' . __tr('Subscribed') . '</span>';
            },
            'disable_ai_bot' => function ($rowData) {
                if ($rowData['disable_ai_bot']) {
                    return '<span class="badge bg-light text-muted px-2.5 py-1 rounded-pill" style="font-size:11px;"><i class="fas fa-pause-circle me-1"></i> ' . __tr('Disabled') . '</span>';
                }
                return '<span class="badge px-2.5 py-1 rounded-pill" style="background-color:#e0e7ff; color:#4338ca; font-size:11px;"><i class="fas fa-robot me-1"></i> ' . __tr('Active') . '</span>';
            },
            'country_name' => function ($rowData) use (&$listOfCountries) {
                return Arr::get($listOfCountries, $rowData['countries__id'] . '.name');
            },
            'phone_number' => function ($rowData) {
                return $rowData['wa_id'];
            },
            'email',
            'created_at' => function ($rowData) {
                return formatDateTime($rowData['created_at']);
            },
        ];

        // prepare data for the DataTables
        return $this->dataTableResponse($contactCollection, $requireColumns);
    }

    /**
     * Contact delete process
     *
     * @param  mix  $contactIdOrUid
     * @return array
     *---------------------------------------------------------------- */
    public function processContactDelete($contactIdOrUid)
    {
        // fetch the record
        $contact = $this->contactRepository->fetchIt($contactIdOrUid);
        // check if the record found
        if (__isEmpty($contact)) {
            // if not found
            return $this->engineResponse(18, null, __tr('Contact not found'));
        }

        if (getVendorSettings('test_recipient_contact') == $contact->_uid) {
            return $this->engineFailedResponse([], __tr('Record set as Test Contact for Campaign, Set another contact for test before deleting it.'));
        }

        \Log::info("Deleting contact: {$contact->_uid} (ID: {$contact->_id})");
        
        try {
            // STEP 1: Remove contact from ALL groups first
            $removedGroups = \DB::table('group_contacts')
                ->where('contacts__id', $contact->_id)
                ->delete();
            
            if ($removedGroups > 0) {
                \Log::info("Removed contact from {$removedGroups} group(s)");
            }
            
            // STEP 2: Delete the contact
            if ($this->contactRepository->deleteIt($contact)) {
                // if successful
                return $this->engineSuccessResponse([], __tr('Contact deleted successfully'));
            }
            
        } catch (\Exception $e) {
            \Log::error("Failed to delete contact: " . $e->getMessage());
            return $this->engineFailedResponse([], __tr('Failed to delete Contact'));
        }

        // if failed to delete
        return $this->engineFailedResponse([], __tr('Failed to delete Contact'));
    }
    /**
     * Contact remove process
     *
     * @param  mix  $contactIdOrUid
     * @return array
     *---------------------------------------------------------------- */
    public function processContactRemove($contactIdOrUid, $groupUid)
    {
        $currentGroup = $this->contactGroupRepository->fetchIt($groupUid);
        // fetch the record
        $contact = $this->contactRepository->fetchIt($contactIdOrUid);
        // check if the record found
        if (__isEmpty($contact)) {
            // if not found
            return $this->engineResponse(18, null, __tr('Contact not found'));
        }

        \Log::info("=== SMART CONTACT REMOVE FROM GROUP ===");
        \Log::info("Contact: {$contact->_uid}, Group: {$currentGroup->title}");
        
        try {
            // STEP 1: Check how many groups this contact is in BEFORE removing
            $groupCount = \DB::table('group_contacts')
                ->where('contacts__id', $contact->_id)
                ->count();
            
            \Log::info("Contact is currently in {$groupCount} group(s)");
            
            // STEP 2: Remove contact from this specific group
            if ($this->groupContactRepository->removeFromAssignedGroup($contact['_id'], $currentGroup->_id)) {
                \Log::info("Removed contact from group");
                
                // STEP 3: If contact was ONLY in this group, delete from contacts table
                if ($groupCount == 1) {
                    \Log::info("Contact was only in this group - deleting from contacts table");
                    if ($this->contactRepository->deleteIt($contact)) {
                        return $this->engineSuccessResponse([], __tr('Contact removed from group and deleted from system as it was not in any other group.'));
                    } else {
                        \Log::error("Failed to delete contact from contacts table");
                        return $this->engineFailedResponse([], __tr('Contact removed from group but failed to delete from system'));
                    }
                } else {
                    // Contact is in other groups, so keep it
                    \Log::info("Contact remains in other groups - kept in contacts table");
                    return $this->engineSuccessResponse([], __tr('Contact removed from group successfully. Contact remains in system as it is in other groups.'));
                }
            }
        } catch (\Exception $e) {
            \Log::error("Failed to remove contact from group: " . $e->getMessage());
            return $this->engineFailedResponse([], __tr('Failed to remove Contact'));
        }

        // if failed to delete
        return $this->engineFailedResponse([], __tr('Failed to remove Contact'));
    }
    /**
     * Contact delete process
     *
     * @param  BaseRequest  $request
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processSelectedContactsDelete($request)
    {
        $selectedContactUids = $request->get('selected_contacts');
        $message = '';
        // check for test number
        if (in_array(getVendorSettings('test_recipient_contact'), $selectedContactUids)) {
            $message .= __tr(' However one of these contact is set as Test Contact, which can not be deleted.');
            if (($key = array_search(getVendorSettings('test_recipient_contact'), $selectedContactUids)) !== false) {
                unset($selectedContactUids[$key]);
            }
            if (empty($selectedContactUids)) {
                return $this->engineFailedResponse([], __tr('As selected is test contact it can not be deleted.'));
            }
        }
        if (empty($selectedContactUids)) {
            return $this->engineFailedResponse([], __tr('Nothing to delete'));
        }
        
        try {
            // STEP 1: Get contact IDs from UIDs
            $contactIds = \DB::table('contacts')
                ->whereIn('_uid', $selectedContactUids)
                ->pluck('_id')
                ->toArray();
            
            // STEP 2: Remove all contacts from ALL groups first
            if (!empty($contactIds)) {
                $removedRelationships = \DB::table('group_contacts')
                    ->whereIn('contacts__id', $contactIds)
                    ->delete();
                
                \Log::info("Removed {$removedRelationships} group relationships before deleting contacts");
            }
            
            // STEP 3: Delete the contacts
            if ($this->contactRepository->deleteSelectedContacts($selectedContactUids)) {
                // if successful
                return $this->engineSuccessResponse([
                    'reloadDatatableId' => '#lwContactList'
                ], __tr('Contacts deleted successfully.') . $message);
            }
            
        } catch (\Exception $e) {
            \Log::error("Failed to delete selected contacts: " . $e->getMessage());
            return $this->engineFailedResponse([], __tr('Failed to delete Contacts'));
        }
        
        // if failed to delete
        return $this->engineFailedResponse([], __tr('Failed to delete Contacts'));
    }

    /**
     * Delete all contacts according to current filter (search) and optional group
     *
     * @param string|null $contactGroupUid
     * @param string|null $search
     * @return array
     */
    public function processDeleteAllContacts(?string $contactGroupUid = null, ?string $search = null)
    {
        $vendorId = getVendorId();

        // Determine group scope
        $groupContactIds = [];
        if ($contactGroupUid) {
            $contactGroup = $this->contactGroupRepository->fetchIt([
                '_uid' => $contactGroupUid,
                'vendors__id' => $vendorId,
            ]);
            if (!__isEmpty($contactGroup)) {
                $groupContacts = $this->groupContactRepository->fetchItAll([
                    'contact_groups__id' => $contactGroup->_id
                ]);
                $groupContactIds = $groupContacts->pluck('contacts__id')->toArray();
            }
        }

        $deleted = $this->contactRepository->deleteAllForVendorByFilter($vendorId, $groupContactIds, (string) $search);

        if ($deleted === false) {
            return $this->engineFailedResponse([], __tr('Failed to delete Contacts'));
        }

        // if successful
        return $this->engineSuccessResponse([
            'reloadDatatableId' => '#lwContactList'
        ], __tr('All matching contacts deleted successfully.'));
    }

    /**
     * Contact create
     *
     * @param  array  $inputData
     * @return EngineResponse
     *---------------------------------------------------------------- */
    public function processContactCreate($inputData)
    {
        $vendorId = getVendorId();
        // check the feature limit
        $vendorPlanDetails = vendorPlanDetails('contacts', $this->contactRepository->countIt([
            'vendors__id' => $vendorId
        ]), $vendorId);
        if (!$vendorPlanDetails['is_limit_available']) {
            return $this->engineResponse(22, null, $vendorPlanDetails['message']);
        }

        $customInputFields = isset($inputData['custom_input_fields']) ? $inputData['custom_input_fields'] : [];
        $customInputFieldUidsAndValues = [];
        // ask to add record
        if ($contactCreated = $this->contactRepository->storeContact($inputData)) {
            // if external api request
            if ($contactCreated and isExternalApiRequest()) {
                // prepare group ids needs to be assign to the contact
                $contactGroupsTitles = array_filter(array_unique(explode(',', $inputData['groups'] ?? '') ?? []));
                if (!empty($contactGroupsTitles)) {
                    // prepare group titles needs to be assign to the contact
                    $groupsToBeAdded = $this->contactGroupRepository->fetchItAll($contactGroupsTitles, [], 'title', [
                        'where' => [
                            'vendors__id' => $vendorId
                        ]
                    ]);
                    $groupsToBeCreatedTitles = array_diff($contactGroupsTitles, $groupsToBeAdded->pluck('title')->toArray());
                    $groupsToBeCreated = [];
                    if (!empty($groupsToBeCreatedTitles)) {
                        foreach ($groupsToBeCreatedTitles as $groupsToBeCreatedTitle) {
                            if (strlen($groupsToBeCreatedTitle) > 255) {
                                abortIf(strlen($groupsToBeCreatedTitle) > 1, null, __tr('Group title should not be greater than 255 characters'));
                            }
                            $groupsToBeCreated[] = [
                                'title' => $groupsToBeCreatedTitle,
                                'vendors__id' => $vendorId,
                                'status' => 1,
                            ];
                        }
                        if (!empty($groupsToBeCreated)) {
                            $newlyCreatedGroupIds = $this->contactGroupRepository->storeItAll($groupsToBeCreated, true);
                            if (!empty($newlyCreatedGroupIds)) {
                                $newlyCreatedGroups = $this->contactGroupRepository->fetchItAll(array_values($newlyCreatedGroupIds));
                                if (!__isEmpty($groupsToBeAdded)) {
                                    $groupsToBeAdded->merge($newlyCreatedGroups);
                                }
                            }
                        }
                    }
                    $assignGroups = [];
                    // prepare to assign if needed
                    if (! empty($groupsToBeAdded)) {
                        foreach ($groupsToBeAdded as $groupToBeAdded) {
                            if ($groupToBeAdded->vendors__id != $vendorId) {
                                continue;
                            }
                            $assignGroups[] = [
                                'contact_groups__id' => $groupToBeAdded->_id,
                                'contacts__id' => $contactCreated->_id,
                            ];
                        }
                        $this->groupContactRepository->storeItAll($assignGroups);
                    }
                }
                return $this->engineSuccessResponse([
                    'contact' => $contactCreated
                ], __tr('Contact created'));
            }
            if (!empty($inputData['contact_groups'])) {
                // prepare group ids needs to be assign to the contact
                $groupsToBeAdded = $this->contactGroupRepository->fetchItAll($inputData['contact_groups'], [], '_id');
                $assignGroups = [];
                // prepare to assign if needed
                if (! empty($groupsToBeAdded)) {
                    foreach ($groupsToBeAdded as $groupToBeAdded) {
                        if ($groupToBeAdded->vendors__id != $vendorId) {
                            continue;
                        }
                        $assignGroups[] = [
                            'contact_groups__id' => $groupToBeAdded->_id,
                            'contacts__id' => $contactCreated->_id,
                        ];
                    }
                    $this->groupContactRepository->storeItAll($assignGroups);
                }
            }
            // check if custom fields
            if (!empty($customInputFields)) {
                $customInputFieldsFromDb = $this->contactCustomFieldRepository->fetchItAll(array_keys(
                    $customInputFields
                ), [], '_uid')->keyBy('_uid');
                // loop though items
                foreach ($inputData['custom_input_fields'] as $customInputFieldKey => $customInputFieldValue) {
                    $customInputFieldFromDb = null;
                    if (isset($customInputFieldsFromDb[$customInputFieldKey])) {
                        $customInputFieldFromDb = $customInputFieldsFromDb[$customInputFieldKey];
                    }
                    // if invalid
                    if (!$customInputFieldFromDb or ($customInputFieldFromDb->vendors__id != $vendorId)) {
                        continue;
                    }
                    // if data verified
                    $customInputFieldUidsAndValues[] = [
                        'contact_custom_fields__id' => $customInputFieldFromDb->_id,
                        'contacts__id' => $contactCreated->_id,
                        'field_value' => $customInputFieldValue,
                    ];
                }
            }
            if (!empty($customInputFieldUidsAndValues)) {
                $this->contactCustomFieldRepository->storeCustomValues($customInputFieldUidsAndValues);
            }
            return $this->engineSuccessResponse([], __tr('Contact added.'));
        }
        return $this->engineFailedResponse([], __tr('Contact not added.'));
    }

    /**
     * Contact prepare update data
     *
     * @param  mix  $contactIdOrUid
     * @return EngineResponse
     *---------------------------------------------------------------- */
    public function prepareContactUpdateData($contactIdOrUid)
    {
        $contact = $this->contactRepository->with(['groups', 'customFieldValues', 'country'])->fetchIt($contactIdOrUid);
        // Check if $contact not exist then throw not found
        if (__isEmpty($contact)) {
            return $this->engineResponse(18, null, __tr('Contact not found.'));
        }
        $existingGroupIds = $contact->groups->pluck('_id')->toArray();
        $contactArray = $contact->toArray();
        return $this->engineSuccessResponse(array_merge($contactArray, [
            'existingGroupIds' => json_encode($existingGroupIds),
        ]));
    }

    /**
     * Process toggle ai bot for contact
     *
     * @param  mixed  $contactIdOrUid
     * @return array
     *---------------------------------------------------------------- */
    public function processToggleAiBot($contactIdOrUid)
    {
        $vendorId = getVendorId();
        $contact = $this->contactRepository->with('groups')->fetchIt([
            '_uid' => $contactIdOrUid,
            'vendors__id' => $vendorId,
        ]);
        // Check if $contact not exist then throw not found
        // exception
        if (__isEmpty($contact)) {
            return $this->engineResponse(18, null, __tr('Contact not found.'));
        }
        $isAiBotDisabled = $contact->disable_ai_bot ? 0 : 1;
        if ($this->contactRepository->updateIt($contact, [
            'disable_ai_bot' => $isAiBotDisabled
        ])) {
            updateClientModels([
                'isAiChatBotEnabled' => !$isAiBotDisabled
            ]);
            if (!$isAiBotDisabled) {
                return $this->engineSuccessResponse([], __tr('AI bot enabled for this contact.'));
            }
            return $this->engineSuccessResponse([], __tr('AI bot disabled for this contact.'));
        }
        return $this->engineResponse(14, [], __tr('AI bot disabled for this contact.'));
    }
    /**
     * Contact process update
     *
     * @param  mixed  $contactIdOrUid
     * @param  array  $inputData
     * @return EngineResponse
     *---------------------------------------------------------------- */
    public function processContactUpdate($contactIdOrUid, $inputData)
    {
        $vendorId = getVendorId();
        $contactWhereClause = [
            'vendors__id' => $vendorId,
        ];
        // if api request
        if (isExternalApiRequest()) {
            $contactWhereClause['wa_id'] = $contactIdOrUid;
        } else {
            $contactWhereClause['_uid'] = $contactIdOrUid;
        }
        $contact = $this->contactRepository->with('groups')->fetchIt($contactWhereClause);
        // Check if $contact not exist then throw not found
        // exception
        if (__isEmpty($contact)) {
            return $this->engineResponse(18, null, __tr('Contact not found.'));
        }
        $contactIdOrUid = $contact->_uid;
        $updateData = [
            'whatsapp_opt_out' => (array_key_exists('whatsapp_opt_out', $inputData) and $inputData['whatsapp_opt_out']) ? 1 : null,
            'disable_ai_bot' => (array_key_exists('enable_ai_bot', $inputData) and $inputData['enable_ai_bot']) ? 0 : 1,
        ];
        if(array_key_exists('first_name', $inputData)) {
            $updateData['first_name'] = $inputData['first_name'];
        }
        if(array_key_exists('last_name', $inputData)) {
            $updateData['last_name'] = $inputData['last_name'];
        }
        if(array_key_exists('language_code', $inputData)) {
            $updateData['language_code'] = $inputData['language_code'];
        }
        if(array_key_exists('email', $inputData)) {
            $updateData['email'] = $inputData['email'];
        }
        if(array_key_exists('country', $inputData)) {
            $updateData['countries__id'] = isExternalApiRequest() ? findRequestedCountryId($inputData['country']) : $inputData['country'];
        }
        $customInputFields = array_key_exists('custom_input_fields', $inputData) ? $inputData['custom_input_fields'] : [];
        $customInputFieldUidsAndValues = [];
        // if external api request
        if (isExternalApiRequest() and isset($inputData['groups']) and $inputData['groups']) {
            // prepare group ids needs to be assign to the contact
            $contactGroupsTitles = array_filter(array_unique(explode(',', $inputData['groups'] ?? '') ?? []));
            if (!empty($contactGroupsTitles)) {
                // prepare group titles needs to be assign to the contact
                $groupsToBeAdded = $this->contactGroupRepository->fetchItAll($contactGroupsTitles, [], 'title', [
                    'where' => [
                        'vendors__id' => $vendorId
                    ]
                ]);

                $groupsToBeCreatedTitles = array_diff($contactGroupsTitles, $groupsToBeAdded->pluck('title')->toArray());
                $groupsToBeCreated = [];
                if (!empty($groupsToBeCreatedTitles)) {
                    foreach ($groupsToBeCreatedTitles as $groupsToBeCreatedTitle) {
                        if (strlen($groupsToBeCreatedTitle) > 255) {
                            abortIf(strlen($groupsToBeCreatedTitle) > 1, null, __tr('Group title should not be greater than 255 characters'));
                        }
                        $groupsToBeCreated[] = [
                            'title' => $groupsToBeCreatedTitle,
                            'vendors__id' => $vendorId,
                            'status' => 1,
                        ];
                    }
                    if (!empty($groupsToBeCreated)) {
                        $newlyCreatedGroupIds = $this->contactGroupRepository->storeItAll($groupsToBeCreated, true);
                        if (!empty($newlyCreatedGroupIds)) {
                            $newlyCreatedGroups = $this->contactGroupRepository->fetchItAll(array_values($newlyCreatedGroupIds), [], '_id');
                            if (!__isEmpty($groupsToBeAdded)) {
                                $groupsToBeAdded->merge($newlyCreatedGroups);
                                $isUpdated = true;
                            }
                        }
                    }
                }
            }
            $inputData['contact_groups'] = !__isEmpty($groupsToBeAdded) ? $groupsToBeAdded->pluck('_id')->toArray() : [];
        }
        // extract exiting group ids
        $existingGroupIds = $contact->groups->pluck('_id')->toArray();
        // prepare group ids needs to be assign to the contact
        $groupsToBeAddedIds = array_diff($inputData['contact_groups'] ?? [], $existingGroupIds);
        // prepare group ids needs to be remove from the contact
        $groupsToBeDeleted = array_diff($existingGroupIds, $inputData['contact_groups'] ?? []);
        $isUpdated = false;
        // process to delete if needed
        if (! empty($groupsToBeDeleted)) {
            if ($this->groupContactRepository->deleteAssignedGroups($groupsToBeDeleted, $contact->_id)) {
                $isUpdated = true;
            }
        }
        // prepare to assign if needed
        if (! empty($groupsToBeAddedIds)) {
            // prepare group ids needs to be assign to the contact
            $groupsToBeAdded = $this->contactGroupRepository->fetchItAll($groupsToBeAddedIds, [], '_id');
            $assignGroups = [];
            foreach ($groupsToBeAdded as $groupToBeAdded) {
                if ($groupToBeAdded->vendors__id != $vendorId) {
                    continue;
                }
                $assignGroups[] = [
                    'contact_groups__id' => $groupToBeAdded->_id,
                    'contacts__id' => $contact->_id,
                ];
            }
            if ($this->groupContactRepository->storeItAll($assignGroups)) {
                $isUpdated = true;
            }
        }
        // Check if Contact updated
        if ($this->contactRepository->updateIt($contact, $updateData)) {
            $isUpdated = true;
        }

        // check if custom fields
        if (!empty($customInputFields)) {
            $customInputFieldsFromDb = $this->contactCustomFieldRepository->fetchItAll(array_keys($customInputFields), [], '_uid')->keyBy('_uid');
            // loop though items
            foreach ($inputData['custom_input_fields'] as $customInputFieldKey => $customInputFieldValue) {
                $customInputFieldFromDb = null;
                if (isset($customInputFieldsFromDb[$customInputFieldKey])) {
                    $customInputFieldFromDb = $customInputFieldsFromDb[$customInputFieldKey];
                }
                // if invalid
                if (!$customInputFieldFromDb or ($customInputFieldFromDb->vendors__id != $vendorId)) {
                    continue;
                }
                // if data verified
                $customInputFieldUidsAndValues[] = [
                    'contact_custom_fields__id' => $customInputFieldFromDb->_id,
                    'contacts__id' => $contact->_id,
                    'field_value' => $customInputFieldValue,
                ];
            }
        }
        if (!empty($customInputFieldUidsAndValues)) {
            if ($customFieldsUpdated = $this->contactCustomFieldRepository->storeCustomValues($customInputFieldUidsAndValues, 'contact_custom_fields__id', [
                'key' => 'contacts__id',
                'value' => $contact->_id,
            ])) {
                $isUpdated = true;
            }
        }

        if ($isUpdated) {
            return $this->engineSuccessResponse(
                [
                    'contactIdOrUid' => $contactIdOrUid,
                    'contact' => $contact->fresh(),
                ],
                __tr('Contact details updated.')
            );
        }

        return $this->engineResponse(14, [
            'contactIdOrUid' => $contactIdOrUid,
        ], __tr('Nothing to update contact information.'));
    }

    /**
     * Prepare Contact Required data
     *
     * @param string|null $groupUid
     * @return EnginResponse
     */
    public function prepareContactRequiredData($groupUid = null)
    {
        $vendorId = getVendorId();

        if ($groupUid) {
            $group = $this->contactGroupRepository->fetchIt([
                '_uid' => $groupUid,
                'vendors__id' => $vendorId,
            ]);
            abortIf(__isEmpty($group));
        }

        $vendorContactCustomFields = $this->contactCustomFieldRepository->fetchItAll([
            'vendors__id' => $vendorId,
        ]);
        // contact groups with contact counts
        $vendorContactGroups = $this->contactGroupRepository->getActiveGroupsWithContactCounts($vendorId);
        // total contacts count for "All Contacts" option
        $totalContactsCount = $this->contactRepository->countIt([
            'vendors__id' => $vendorId
        ]);

        return $this->engineSuccessResponse([
            'groupUid' => $groupUid,
            'vendorContactGroups' => $vendorContactGroups,
            'vendorContactCustomFields' => $vendorContactCustomFields,
            'totalContactsCount' => $totalContactsCount,
        ]);
    }

    /**
     * Export Template with or without Data
     *
     * @param string $exportType
     * @return Download File
     */
    public function processExportContacts($exportType = 'blank')
    {
        $header = [];
        $vendorId = getVendorId();
        
        // SIMPLIFIED: Only essential fields for blank template
        $header = array_merge($header, [
            'First Name' => 'string',
            'Last Name' => 'string',
            'Mobile Number' => 'string',
            'Groups' => 'string',
        ]);
        
        // For data export, include additional fields
        if ($exportType == 'data') {
            // Add extra fields only when exporting existing data
            $header['Language Code'] = 'string';
            $header['Country'] = 'string';
            $header['Email'] = 'string';
            
            // required data like fields and groups
            $contactsRequiredData = $this->prepareContactRequiredData();
            // get vendor custom fields
            $vendorContactCustomFields = $contactsRequiredData->data('vendorContactCustomFields');
            // create header array for custom fields
            foreach ($vendorContactCustomFields as $vendorContactCustomField) {
                $header[$vendorContactCustomField->input_name] = 'string';
            }
        }
        
        $data = [];
        // create temp path for store excel file
        $tempFile = tempnam(sys_get_temp_dir(), "exported_contacts_{$vendorId}.xlsx");
        $writer = new XLSXWriter();
        $writer->writeSheetHeader('Contacts', $header);
        
        if ($exportType == 'data') {
            if (isDemo() and isDemoVendorAccount()) {
                abort(403, __tr('Exporting Contacts data has been disabled for demo'));
            }
            // country repository
            $countryRepository = new CountryRepository();
            $countries = $countryRepository->fetchItAll([
                [
                    'phone_code', '!=', 0
                ],
                [
                    'phone_code', '!=', null
                ],
            ], ['_id','name'])->keyBy('_id')->toArray();
            // contacts
            $this->contactRepository->getAllContactsForTheVendorLazily($vendorId, function (object $contact) use (&$countries, &$writer) {
                $dataItem = [
                    $contact->first_name,
                    $contact->last_name,
                    // phone number
                    $contact->wa_id,
                ];
                // group
                if ($contact->groups) {
                    $groupItems = [];
                    foreach ($contact->groups as $group) {
                        $groupItems[] = $group->title;
                    }
                    $dataItem[] = implode(',', $groupItems);
                    unset($groupItems);
                } else {
                    $dataItem[] = ''; // Empty groups column
                }
                
                // Add extra fields for data export (after groups)
                $dataItem[] = $contact->language_code;
                $dataItem[] = $countries[$contact->countries__id]['name'] ?? null;
                $dataItem[] = $contact->email;
                
                // custom fields
                if ($contact->customFieldValues) {
                    foreach ($contact->customFieldValues as $customFieldValue) {
                        $dataItem[] = $customFieldValue->field_value;
                    }
                }
                // write to sheet
                $writer->writeSheetRow('Contacts', $dataItem);
                unset($dataItem);
            });
        }
        // write to file
        $writer->writeToFile($tempFile);
        // file name
        $dateTime = str_slug(now()->format('Y-m-d-H-i-s'));
        // get back with response
        return response()->download($tempFile, "contacts-{$exportType}-{$dateTime}.xlsx", [
            'Content-Transfer-Encoding: binary',
            'Content-Type: application/octet-stream',
        ])->deleteFileAfterSend();
    }

    /**
     * OPTIMIZED: Import contacts using Excel sheet - Handles 150K+ contacts efficiently
     *
     * @param BaseRequest $request
     * @return EngineResponse
     */
    public function processImportContactsOptimized($request)
    {
        $vendorId = getVendorId();
        $startTime = microtime(true);
        
        // Generate unique import ID for progress tracking
        $importId = 'import_' . $vendorId . '_' . time();
        
        // Initialize progress tracking
        $this->updateImportProgress($importId, [
            'status' => 'starting',
            'stage' => 'Initializing import...',
            'current' => 0,
            'total' => 0,
            'percentage' => 0,
            'started_at' => now()->toDateTimeString(),
        ]);
        
        // check if vendor has active plan
        $vendorPlanDetails = vendorPlanDetails(null, null, $vendorId);
        if (!$vendorPlanDetails->hasActivePlan()) {
            $this->updateImportProgress($importId, ['status' => 'failed', 'stage' => 'Plan check failed']);
            return $this->engineResponse(22, null, $vendorPlanDetails['message']);
        }
        
        // PERFORMANCE OPTIMIZATION: Aggressive memory and time settings for 150K+
        \DB::connection()->disableQueryLog();
        ini_set('memory_limit', '2048M'); // Increased from 1024M
        set_time_limit(1200); // 20 minutes for 150K contacts
        gc_enable(); // Enable garbage collection
        
        $filePath = getTempUploadedFile($request->get('document_name'));
        
        // Pre-load all required data ONCE
        \Log::info("=== OPTIMIZED CONTACT IMPORT STARTED ===");
        \Log::info("Vendor ID: {$vendorId}");
        \Log::info("Import ID: {$importId}");
        \Log::info("Pre-loading reference data...");
        
        $this->updateImportProgress($importId, [
            'status' => 'preparing',
            'stage' => 'Loading reference data...',
        ]);
        
        $countryRepository = new CountryRepository();
        $countries = $countryRepository->fetchItAll([], [
            '_id', 'name', 'iso_code', 'name_capitalized', 'iso3_code', 'phone_code'
        ])->keyBy('name')->toArray();
        
        $contactsRequiredData = $this->prepareContactRequiredData();
        $vendorContactGroups = $contactsRequiredData->data('vendorContactGroups')?->keyBy('title')?->toArray() ?: [];
        $vendorContactCustomFields = $contactsRequiredData->data('vendorContactCustomFields')?->keyBy('input_name')?->toArray() ?: [];
        
        // CRITICAL OPTIMIZATION: Pre-load ALL existing contacts for this vendor into memory
        \Log::info("Pre-loading existing contacts into memory...");
        $existingContacts = $this->contactRepository
            ->fetchItAll(['vendors__id' => $vendorId], ['_id', '_uid', 'wa_id'])
            ->keyBy('wa_id')
            ->toArray();
        \Log::info("Loaded " . count($existingContacts) . " existing contacts");
        
        $botSettingsForNewContacts = getVendorSettings('default_enable_flowise_ai_bot_for_users', null, null, $vendorId) ? 0 : 1;
        $vendorAllContactsCount = count($existingContacts);
        
        // Check plan limits
        $contactPlanDetails = vendorPlanDetails('contacts', null, $vendorId);
        $hasUnlimitedContacts = isset($contactPlanDetails['plan_feature_limit']) && $contactPlanDetails['plan_feature_limit'] == -1;
        $contactsPerRequest = getAppSettings('contacts_import_limit_per_request') ?: 200000;
        
        // OPTIMIZATION: Larger chunk size for 150K imports
        $chuckSize = 5000; // Increased from 2000
        
        // Disable database keys for faster bulk inserts
        try {
            \DB::statement('SET FOREIGN_KEY_CHECKS=0');
            \DB::statement('ALTER TABLE contacts DISABLE KEYS');
            \DB::statement('ALTER TABLE group_contacts DISABLE KEYS');
            \DB::statement('ALTER TABLE contact_custom_field_values DISABLE KEYS');
        } catch (\Exception $e) {
            \Log::warning("Could not disable keys: " . $e->getMessage());
        }
        
        $reader = ReaderEntityFactory::createReaderFromFile($filePath);
        $reader->open($filePath);
        
        // SIMPLIFIED: Support both old (7 columns) and new (4 columns) format
        // New format: first_name, last_name, wa_id, groups
        // Old format: first_name, last_name, wa_id, language_code, countries__id, email, groups
        $dataStructure = ['first_name', 'last_name', 'wa_id', 'groups'];
        $customFieldStructure = [];
        
        $contactsToInsert = [];
        $contactsToUpdate = [];
        $customFieldsToUpdate = [];
        $contactGroupsToUpdate = [];
        
        // Track processed phone numbers to handle duplicates (same contact in multiple groups)
        $processedPhoneNumbers = [];
        $rejectedRows = [];
        $newContactsCount = 0;
        $updatedContactsCount = 0;
        $numberOfRows = 0;
        
        // Map to store new contact UUIDs for relationship building
        $contactUidByPhone = [];
        
        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                // First pass: Count rows
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    if ($rowIndex != 1) $numberOfRows++;
                }
                
                \Log::info("Total data rows: {$numberOfRows}");
                
                // Update progress with total count
                $this->updateImportProgress($importId, [
                    'status' => 'processing',
                    'stage' => 'Processing contacts...',
                    'total' => $numberOfRows,
                ]);
                
                // Check row limit
                if (!$hasUnlimitedContacts && $numberOfRows > $contactsPerRequest) {
                    $reader->close();
                    $this->updateImportProgress($importId, ['status' => 'failed', 'stage' => 'Row limit exceeded']);
                    return $this->engineFailedResponse([], __tr('Please upload maximum of __contactsPerRequest__ records in single upload', [
                        '__contactsPerRequest__' => $contactsPerRequest
                    ]));
                }
                
                // Second pass: Process contacts
                \Log::info("Processing contacts...");
                $rowCounter = 0;
                
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    $cells = $row->getCells();
                    $totalColumns = count($cells);
                    
                    // Skip header row
                    if ($rowIndex == 1) {
                        // Detect format: 4 columns (new) or 7+ columns (old)
                        if ($totalColumns <= 4) {
                            // New simplified format: first_name, last_name, wa_id, groups
                            $dataStructure = ['first_name', 'last_name', 'wa_id', 'groups'];
                            \Log::info("Detected NEW simplified import format (4 columns)");
                        } else {
                            // Old format: first_name, last_name, wa_id, language_code, countries__id, email, groups, custom...
                            $dataStructure = ['first_name', 'last_name', 'wa_id', 'language_code', 'countries__id', 'email'];
                            \Log::info("Detected OLD import format ({$totalColumns} columns)");
                            
                            // Capture custom field headers
                            foreach ($cells as $cellIndex => $cell) {
                                if ($cellIndex >= 7) {
                                    $customFieldStructure[$cellIndex] = $cell->getValue();
                                }
                            }
                        }
                        continue;
                    }
                    
                    $rowCounter++;
                    if ($rowCounter % 10000 == 0) {
                        \Log::info("Processed {$rowCounter} / {$numberOfRows} rows");
                        $this->updateImportProgress($importId, [
                            'current' => $rowCounter,
                            'percentage' => round(($rowCounter / $numberOfRows) * 70), // 70% for contact processing
                            'stage' => "Processing contacts... ({$rowCounter} / {$numberOfRows})",
                        ]);
                        gc_collect_cycles(); // Force garbage collection every 10K rows
                    }
                    
                    $rowData = [
                        'first_name' => null,
                        'last_name' => null,
                        'language_code' => null,
                        'countries__id' => null,
                        'email' => null,
                        'vendors__id' => $vendorId
                    ];
                    
                    $phoneNumber = null;
                    $groupNames = [];
                    $customFields = [];
                    $skipRow = false;
                    
                    // NEW SIMPLIFIED FORMAT (4 columns)
                    if ($totalColumns <= 4) {
                        foreach ($cells as $cellIndex => $cell) {
                            $cellValue = e($cell->getValue());
                            
                            if ($cellIndex == 0) {
                                // First Name
                                $rowData['first_name'] = $cellValue;
                            } elseif ($cellIndex == 1) {
                                // Last Name
                                $rowData['last_name'] = $cellValue;
                            } elseif ($cellIndex == 2) {
                                // Mobile Number
                                if (!$cellValue) {
                                    \Log::warning("Missing phone number on row {$rowIndex}");
                                    $skipRow = true;
                                    break;
                                }
                                
                                // REJECT if phone starts with "+" or "0"
                                if (str_starts_with($cellValue, '0') || str_starts_with($cellValue, '+')) {
                                    \Log::warning("Rejected phone number starting with 0 or + on row {$rowIndex}: {$cellValue}");
                                    $rejectedRows[] = $rowIndex;
                                    $skipRow = true;
                                    break;
                                }
                                
                                if (!is_numeric($cellValue)) {
                                    \Log::warning("Rejected non-numeric phone number on row {$rowIndex}: {$cellValue}");
                                    $rejectedRows[] = $rowIndex;
                                    $skipRow = true;
                                    break;
                                }
                                
                                // Track phone numbers - same phone can be in multiple groups
                                $phoneNumber = $cellValue;
                                $rowData['wa_id'] = $cellValue;
                            } elseif ($cellIndex == 3) {
                                // Groups
                                $groupNames = trim($cellValue) ? explode(',', $cellValue) : [];
                            }
                        }
                    }
                    // OLD FORMAT (6+ columns)
                    else {
                        // Parse all cells in this row
                        foreach ($cells as $cellIndex => $cell) {
                            $cellValue = e($cell->getValue());
                            
                            // Basic contact fields (0-5)
                            if ($cellIndex <= 5) {
                                if ($dataStructure[$cellIndex] == 'wa_id') {
                                    if (!$cellValue) {
                                        \Log::warning("Missing phone number on row {$rowIndex}");
                                        $skipRow = true;
                                        break;
                                    }
                                    
                                    // REJECT if phone starts with "+" or "0"
                                    if (str_starts_with($cellValue, '0') || str_starts_with($cellValue, '+')) {
                                        \Log::warning("Rejected phone number starting with 0 or + on row {$rowIndex}: {$cellValue}");
                                        $rejectedRows[] = $rowIndex;
                                        $skipRow = true;
                                        break;
                                    }
                                    
                                    if (!is_numeric($cellValue)) {
                                        \Log::warning("Rejected non-numeric phone number on row {$rowIndex}: {$cellValue}");
                                        $rejectedRows[] = $rowIndex;
                                        $skipRow = true;
                                        break;
                                    }
                                    
                                    // Track phone numbers - same phone can be in multiple groups
                                    $phoneNumber = $cellValue;
                                    $rowData[$dataStructure[$cellIndex]] = $cellValue;
                                    
                                } elseif ($dataStructure[$cellIndex] == 'countries__id') {
                                    $getCountry = Arr::first($countries, function ($value) use ($cellValue) {
                                        return in_array(strtolower($cellValue), array_map('strtolower', array_values(Arr::only($value, [
                                            'name', 'iso_code', 'name_capitalized', 'iso3_code', 'phone_code',
                                        ]))));
                                    });
                                    $rowData[$dataStructure[$cellIndex]] = Arr::get($getCountry, '_id');
                                } else {
                                    $rowData[$dataStructure[$cellIndex]] = $cellValue;
                                }
                            }
                            // Groups column (6)
                            elseif ($cellIndex == 6) {
                                $groupNames = trim($cellValue) ? explode(',', $cellValue) : [];
                            }
                            // Custom fields (7+)
                            elseif ($cellIndex >= 7 && isset($customFieldStructure[$cellIndex])) {
                                $customFields[$customFieldStructure[$cellIndex]] = $cellValue;
                            }
                        }
                    }
                    
                    if ($skipRow || !$phoneNumber) {
                        continue;
                    }
                    
                    // Check if we've already processed this phone number in THIS import
                    $alreadyProcessedInThisImport = isset($processedPhoneNumbers[$phoneNumber]);
                    
                    // Check if contact exists in database (pre-loaded)
                    $existingContact = $existingContacts[$phoneNumber] ?? null;
                    
                    if ($existingContact) {
                        // Contact exists in DB - mark for group assignment only
                        $contactUidByPhone[$phoneNumber] = [
                            '_id' => $existingContact['_id'],
                            '_uid' => $existingContact['_uid']
                        ];
                        $processedPhoneNumbers[$phoneNumber] = true;
                        $updatedContactsCount++;
                    } elseif ($alreadyProcessedInThisImport) {
                        // Already created in this import - just use for group assignment
                        // contactUidByPhone already has this entry
                    } else {
                        // New contact - will insert
                        $uuid = (string) Str::uuid();
                        $rowData['_uid'] = $uuid;
                        $rowData['disable_ai_bot'] = $botSettingsForNewContacts;
                        $rowData['created_at'] = now();
                        $rowData['updated_at'] = now();
                        $contactsToInsert[] = $rowData;
                        $contactUidByPhone[$phoneNumber] = ['_uid' => $uuid, '_id' => null]; // ID will be set after insert
                        $processedPhoneNumbers[$phoneNumber] = true;
                        $newContactsCount++;
                    }
                    
                    // Store groups and custom fields for later processing
                    if (!empty($groupNames)) {
                        foreach ($groupNames as $groupName) {
                            $groupName = Str::limit(trim($groupName), 250, '');
                            if ($groupName) {
                                $contactGroupsToUpdate[] = [
                                    'phone' => $phoneNumber,
                                    'group_name' => $groupName,
                                ];
                            }
                        }
                    }
                    
                    if (!empty($customFields)) {
                        foreach ($customFields as $fieldName => $fieldValue) {
                            $customFieldsToUpdate[] = [
                                'phone' => $phoneNumber,
                                'field_name' => $fieldName,
                                'field_value' => $fieldValue,
                            ];
                        }
                    }
                    
                    // Batch insert new contacts when chunk size reached
                    if (count($contactsToInsert) >= $chuckSize) {
                        if (!$hasUnlimitedContacts) {
                            $vendorPlanDetails = vendorPlanDetails('contacts', ($vendorAllContactsCount + $newContactsCount), $vendorId);
                            if (!$vendorPlanDetails['is_limit_available']) {
                                $reader->close();
                                return $this->engineResponse(22, null, $vendorPlanDetails['message']);
                            }
                        }
                        
                        \Log::info("Inserting batch of " . count($contactsToInsert) . " new contacts");
                        $this->contactRepository->bunchInsertOrUpdate($contactsToInsert, '_uid');
                        
                        // IMMEDIATE: Fetch IDs for this batch right after insert
                        $batchPhones = array_column($contactsToInsert, 'wa_id');
                        $this->fetchAndUpdateContactIds($vendorId, $batchPhones, $contactUidByPhone);
                        
                        $contactsToInsert = [];
                    }
                    
                }
                
                // Insert remaining contacts
                if (!empty($contactsToInsert)) {
                    \Log::info("Inserting final batch of " . count($contactsToInsert) . " new contacts");
                    $this->contactRepository->bunchInsertOrUpdate($contactsToInsert, '_uid');
                    
                    // IMMEDIATE: Fetch IDs for final batch
                    $batchPhones = array_column($contactsToInsert, 'wa_id');
                    $this->fetchAndUpdateContactIds($vendorId, $batchPhones, $contactUidByPhone);
                }
                
                // Now process relationships (groups and custom fields)
                \Log::info("Processing groups and custom fields...");
                $this->updateImportProgress($importId, [
                    'percentage' => 75,
                    'stage' => 'Processing group assignments...',
                ]);
                
                // All contact IDs have been fetched immediately after each batch
                // Count how many contacts have IDs
                $contactsWithIds = 0;
                $contactsWithoutIds = 0;
                foreach ($contactUidByPhone as $phone => $data) {
                    if (!empty($data['_id'])) {
                        $contactsWithIds++;
                    } else {
                        $contactsWithoutIds++;
                    }
                }
                
                \Log::info("Contact IDs ready: {$contactsWithIds} with IDs, {$contactsWithoutIds} without IDs");
                
                // Process group assignments
                \Log::info("Processing " . count($contactGroupsToUpdate) . " group assignment entries...");
                
                $groupAssignmentsBatch = [];
                $skippedCount = 0;
                $processedCount = 0;
                
                foreach ($contactGroupsToUpdate as $groupAssignment) {
                    $phone = $groupAssignment['phone'];
                    $groupName = $groupAssignment['group_name'];
                    
                    // Check if contact ID exists
                    if (!isset($contactUidByPhone[$phone])) {
                        \Log::warning("Contact not found in mapping for phone: {$phone}");
                        $skippedCount++;
                        continue;
                    }
                    
                    if (!$contactUidByPhone[$phone]['_id']) {
                        \Log::warning("Contact ID is null for phone: {$phone}");
                        $skippedCount++;
                        continue;
                    }
                    
                    $contactId = $contactUidByPhone[$phone]['_id'];
                    
                    // Get or create group
                    $contactGroupId = Arr::get($vendorContactGroups, $groupName . '._id');
                    if (!$contactGroupId) {
                        \Log::info("Creating new group: {$groupName}");
                        if ($newGroup = $this->contactGroupRepository->storeIt([
                            'title' => $groupName,
                            'vendors__id' => $vendorId,
                        ])) {
                            $vendorContactGroups[$groupName] = $newGroup->toArray();
                            $contactGroupId = $newGroup->_id;
                            \Log::info("Created group '{$groupName}' with ID: {$contactGroupId}");
                        } else {
                            \Log::error("Failed to create group: {$groupName}");
                            $skippedCount++;
                            continue;
                        }
                    }
                    
                    if ($contactGroupId) {
                        $groupAssignmentsBatch[] = [
                            '_uid' => (string) Str::uuid(), // Generate UUID for _uid field
                            'contact_groups__id' => $contactGroupId,
                            'contacts__id' => $contactId,
                            'created_at' => now(),
                            'updated_at' => now(),
                            // Note: vendors__id is NOT in group_contacts table structure
                            // Note: status is nullable, so we don't set it
                        ];
                        $processedCount++;
                    }
                    
                    // Batch insert group assignments
                    if (count($groupAssignmentsBatch) >= $chuckSize) {
                        \Log::info("Inserting batch of " . count($groupAssignmentsBatch) . " group assignments");
                        try {
                            // Use regular insert - will show errors if structure is wrong
                            $inserted = \DB::table('group_contacts')->insert($groupAssignmentsBatch);
                            \Log::info("Successfully inserted batch of " . count($groupAssignmentsBatch) . " group assignments");
                        } catch (\Exception $e) {
                            \Log::error("Failed to insert group assignments batch: " . $e->getMessage());
                            \Log::error("Error details: " . $e->getTraceAsString());
                            // Fallback to repository method
                            try {
                                $this->groupContactRepository->bunchInsertOrUpdate($groupAssignmentsBatch, 'contacts__id');
                            } catch (\Exception $e2) {
                                \Log::error("Fallback also failed: " . $e2->getMessage());
                            }
                        }
                        $groupAssignmentsBatch = [];
                    }
                }
                
                if (!empty($groupAssignmentsBatch)) {
                    \Log::info("Inserting final batch of " . count($groupAssignmentsBatch) . " group assignments");
                    try {
                        // Use regular insert - will show errors if structure is wrong
                        $inserted = \DB::table('group_contacts')->insert($groupAssignmentsBatch);
                        \Log::info("Successfully inserted final batch of " . count($groupAssignmentsBatch) . " group assignments");
                    } catch (\Exception $e) {
                        \Log::error("Failed to insert final group assignments batch: " . $e->getMessage());
                        \Log::error("Error details: " . $e->getTraceAsString());
                        // Fallback to repository method
                        try {
                            $this->groupContactRepository->bunchInsertOrUpdate($groupAssignmentsBatch, 'contacts__id');
                        } catch (\Exception $e2) {
                            \Log::error("Fallback also failed: " . $e2->getMessage());
                        }
                    }
                }
                
                \Log::info("Group assignment complete: {$processedCount} processed, {$skippedCount} skipped");
                
                // Process custom field values
                $customFieldValuesBatch = [];
                foreach ($customFieldsToUpdate as $customFieldData) {
                    $phone = $customFieldData['phone'];
                    $fieldName = $customFieldData['field_name'];
                    $fieldValue = $customFieldData['field_value'];
                    
                    if (!isset($contactUidByPhone[$phone]) || !$contactUidByPhone[$phone]['_id']) {
                        continue;
                    }
                    
                    $contactId = $contactUidByPhone[$phone]['_id'];
                    $customFieldItem = $vendorContactCustomFields[$fieldName] ?? null;
                    
                    if ($customFieldItem) {
                        $customFieldValuesBatch[] = [
                            '_uid' => (string) Str::uuid(),
                            'contact_custom_fields__id' => Arr::get($customFieldItem, '_id'),
                            'contacts__id' => $contactId,
                            'field_value' => $fieldValue,
                        ];
                    }
                    
                    // Batch insert custom field values
                    if (count($customFieldValuesBatch) >= $chuckSize) {
                        \Log::info("Inserting batch of " . count($customFieldValuesBatch) . " custom field values");
                        $this->contactCustomFieldRepository->storeCustomValues($customFieldValuesBatch, '_uid');
                        $customFieldValuesBatch = [];
                    }
                }
                
                if (!empty($customFieldValuesBatch)) {
                    \Log::info("Inserting final batch of " . count($customFieldValuesBatch) . " custom field values");
                    $this->contactCustomFieldRepository->storeCustomValues($customFieldValuesBatch, '_uid');
                }
            }
            
            $reader->close();
            
            // Re-enable database keys
            try {
                \DB::statement('ALTER TABLE contacts ENABLE KEYS');
                \DB::statement('ALTER TABLE group_contacts ENABLE KEYS');
                \DB::statement('ALTER TABLE contact_custom_field_values ENABLE KEYS');
                \DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Exception $e) {
                \Log::warning("Could not re-enable keys: " . $e->getMessage());
            }
            
            \DB::connection()->enableQueryLog();
            
            // Calculate execution time
            $executionTime = round(microtime(true) - $startTime, 2);
            $recordsPerSecond = $executionTime > 0 ? round($numberOfRows / $executionTime, 2) : 0;
            
            // Final import statistics
            \Log::info("=== OPTIMIZED IMPORT COMPLETED ===");
            \Log::info("New contacts created: {$newContactsCount}");
            \Log::info("Existing contacts updated: {$updatedContactsCount}");
            \Log::info("Total rows processed: {$numberOfRows}");
            \Log::info("Rejected rows (invalid phone): " . count($rejectedRows));
            \Log::info("Execution time: {$executionTime} seconds");
            \Log::info("Processing speed: {$recordsPerSecond} records/second");
            
            $totalContactsProcessed = $newContactsCount + $updatedContactsCount;
            
            // Update final progress
            $this->updateImportProgress($importId, [
                'status' => 'completed',
                'stage' => 'Import completed successfully!',
                'current' => $numberOfRows,
                'percentage' => 100,
                'new_contacts' => $newContactsCount,
                'updated_contacts' => $updatedContactsCount,
                'rejected_rows' => count($rejectedRows),
                'execution_time' => $executionTime,
                'speed' => $recordsPerSecond,
                'completed_at' => now()->toDateTimeString(),
            ]);
            
            if (!empty($rejectedRows)) {
                return $this->engineSuccessResponse([
                    'import_id' => $importId
                ], __tr('Successfully imported __totalContactsProcessed__ contacts in __executionTime__s (__recordsPerSecond__ records/s). __rejectedRows__ rows were rejected (phone numbers starting with + or 0).', [
                    '__totalContactsProcessed__' => $totalContactsProcessed,
                    '__executionTime__' => $executionTime,
                    '__recordsPerSecond__' => $recordsPerSecond,
                    '__rejectedRows__' => count($rejectedRows),
                ]));
            }
            
            $response = $this->engineSuccessResponse([
                'import_id' => $importId  // Return import ID so frontend knows which to track
            ], __tr('Successfully imported __totalContactsProcessed__ contacts in __executionTime__s (__recordsPerSecond__ records/s)', [
                '__totalContactsProcessed__' => $totalContactsProcessed,
                '__executionTime__' => $executionTime,
                '__recordsPerSecond__' => $recordsPerSecond,
            ]));
            
            return $response;
            
        } catch (\Throwable $th) {
            // Re-enable database keys on error
            try {
                \DB::statement('ALTER TABLE contacts ENABLE KEYS');
                \DB::statement('ALTER TABLE group_contacts ENABLE KEYS');
                \DB::statement('ALTER TABLE contact_custom_field_values ENABLE KEYS');
                \DB::statement('SET FOREIGN_KEY_CHECKS=1');
            } catch (\Exception $e) {
                // Ignore
            }
            
            \DB::connection()->enableQueryLog();
            
            // Update progress to show error
            $this->updateImportProgress($importId, [
                'status' => 'failed',
                'stage' => 'Import failed: ' . $th->getMessage(),
                'error' => $th->getMessage(),
                'failed_at' => now()->toDateTimeString(),
            ]);
            
            \Log::error("=== OPTIMIZED IMPORT FAILED ===");
            \Log::error("Error: " . $th->getMessage());
            \Log::error("File: " . $th->getFile());
            \Log::error("Line: " . $th->getLine());
            \Log::error("Stack trace: " . $th->getTraceAsString());
            
            if (config('app.debug')) {
                throw $th;
            }
            return $this->engineFailedResponse([], __tr('Error occurred while importing data, please check and correct data and re-upload.'));
        }
    }

    /**
     * Import contacts using Excel sheet
     *
     * @param BaseRequest $request
     * @return EngineResponse
     */
    public function processImportContacts($request)
    {
        $vendorId = getVendorId();
        $startTime = microtime(true);
        
        // check if vendor has active plan
        $vendorPlanDetails = vendorPlanDetails(null, null, $vendorId);
        if (!$vendorPlanDetails->hasActivePlan()) {
            return $this->engineResponse(22, null, $vendorPlanDetails['message']);
        }
        
        // PERFORMANCE OPTIMIZATION: Disable query logging to save memory
        \DB::connection()->disableQueryLog();
        
        // PERFORMANCE OPTIMIZATION: Increase memory limit and execution time
        ini_set('memory_limit', '1024M');
        set_time_limit(600); // 10 minutes
        
        $filePath = getTempUploadedFile($request->get('document_name'));
        $countryRepository = new CountryRepository();
        $countries = $countryRepository->fetchItAll([], [
            '_id',
            'name',
            'iso_code',
            'name_capitalized',
            'iso3_code',
            'phone_code',
            ])->keyBy('name')->toArray();
        $contactsRequiredData = $this->prepareContactRequiredData();
        $vendorContactGroups = $contactsRequiredData->data('vendorContactGroups')?->keyBy('title')?->toArray() ?: [];
        $vendorContactCustomFields = $contactsRequiredData->data('vendorContactCustomFields')?->keyBy('input_name')?->toArray() ?: [];
        $duplicateEntries = [];
        $botSettingsForNewContacts = getVendorSettings('default_enable_flowise_ai_bot_for_users', null, null, $vendorId) ? 0 : 1;
        $reader = ReaderEntityFactory::createReaderFromFile($filePath);
        $reader->open($filePath);
        $data = [];
        $dataStructure = [
            'first_name',
            'last_name',
            'wa_id',
            'language_code',
            'countries__id',
            'email',
        ];
        $customFieldStructure = [];
        $contactsToUpdate = [];
        $customFieldsToUpdate = [];
        $contactGroupsToUpdate = [];
        $vendorAllContactsCount = $this->contactRepository->countIt([
                'vendors__id' => $vendorId
            ]);
        $phoneNumbers = [];
        $ignoreRow = false;
        $newContactsCount = 0;
        $numberOfRows = 0;
        
        // PERFORMANCE OPTIMIZATION: Increased limits for large imports
        $contactsPerRequest = getAppSettings('contacts_import_limit_per_request') ?: 200000;
        // PERFORMANCE OPTIMIZATION: Increased chunk size from 500 to 2000 for faster processing
        $chuckSize = 2000;
        
        // Check if vendor has unlimited contacts in their plan
        $contactPlanDetails = vendorPlanDetails('contacts', null, $vendorId);
        $hasUnlimitedContacts = isset($contactPlanDetails['plan_feature_limit']) && $contactPlanDetails['plan_feature_limit'] == -1;
        
        // Debug logging
        \Log::info("=== CONTACT IMPORT STARTED ===");
        \Log::info("Vendor ID: {$vendorId}");
        \Log::info("Has Unlimited Contacts: " . ($hasUnlimitedContacts ? 'YES' : 'NO'));
        \Log::info("Contact Plan Limit: " . ($contactPlanDetails['plan_feature_limit'] ?? 'N/A'));
        \Log::info("Contacts Per Request Setting: {$contactsPerRequest}");
        \Log::info("Existing Contacts Count: {$vendorAllContactsCount}");
        \Log::info("Chunk Size: {$chuckSize}");
        
        try {
            // loop through the sheets
            foreach ($reader->getSheetIterator() as $sheet) {
                // loop though each row
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    $numberOfRows++;
                }
                
                \Log::info("Total rows in file: {$numberOfRows}");
                
                // rows limitation - skip check if plan has unlimited contacts
                if (!$hasUnlimitedContacts && $numberOfRows > $contactsPerRequest) {
                    \Log::warning("Import stopped: Row count {$numberOfRows} exceeds limit {$contactsPerRequest}");
                    return $this->engineFailedResponse([], __tr('Please upload maximum of __contactsPerRequest__ records in single upload', [
                        '__contactsPerRequest__' => $contactsPerRequest
                    ]));
                }
                
                // For unlimited plans, show progress message for large imports
                if ($hasUnlimitedContacts && $numberOfRows > $contactsPerRequest) {
                    \Log::info("Processing large import for vendor {$vendorId}: {$numberOfRows} contacts (Unlimited Plan)");
                }
                // loop though each row to process data
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    if ($ignoreRow) {
                        $ignoreRow = false;
                    }
                    // do stuff with the row
                    $cells = $row->getCells();
                    $contact = null;
                    $contactId = null;
                    if ($rowIndex != 1) {
                        $contactsToUpdate[$rowIndex] = [
                            'first_name' => null,
                            'last_name' => null,
                            'language_code' => null,
                            'countries__id' => null,
                            'email' => null,
                            'vendors__id' => $vendorId
                        ];
                    }
                    // loop through each cell of row
                    foreach ($cells as $cellIndex => $cell) {
                        if ($ignoreRow) {
                            continue;
                        }
                        $cellValue = e($cell->getValue());
                        // if its not header row and upto 5 cells its contact basic fields
                        if (($rowIndex != 1) and ($cellIndex <= 5)) {
                            if ($dataStructure[$cellIndex] == 'wa_id') {
                                if (!$cellValue) {
                                    return $this->engineFailedResponse([], __tr('Missing phone number on row __rowNumber__ ', [
                                        '__rowNumber__' => $rowIndex
                                    ]));
                                }
                                // check if mobile number is valid
                                if (!is_numeric($cellValue)) {
                                    $ignoreRow = true;
                                    $duplicateEntries[] = $cellValue;
                                    unset($contactsToUpdate[$rowIndex]);
                                    continue;
                                }
                                if (str_starts_with($cellValue, '0') or str_starts_with($cellValue, '+')) {
                                    $cellValue = cleanDisplayPhoneNumber($cellValue);
                                }
                                // check if number is already processed then skip and continue
                                if (in_array($cellValue, $phoneNumbers)) {
                                    $ignoreRow = true;
                                    $duplicateEntries[] = $cellValue;
                                    unset($contactsToUpdate[$rowIndex]);
                                    continue;
                                }
                                $contact = $this->contactRepository->with(['groups', 'customFieldValues'])->fetchIt([
                                    'vendors__id' => $vendorId,
                                    'wa_id' => $cellValue,
                                ], [
                                    '_id',
                                    '_uid',
                                    'wa_id'
                                    ])?->toArray() ?: [];
                                $contactsToUpdate[$rowIndex]['_uid'] = Arr::get($contact, '_uid') ?: (string) Str::uuid();
                                if (!__isEmpty($contact)) {
                                    $contactId = Arr::get($contact, '_id');
                                } else {
                                    $contactsToUpdate[$rowIndex]['disable_ai_bot'] = $botSettingsForNewContacts;
                                    $newContactsCount++;
                                    $contactsToUpdate[$rowIndex][$dataStructure[$cellIndex]] = $cellValue;
                                    $phoneNumbers[] = $cellValue;
                                }
                            }
                            // if its country column
                            elseif ($dataStructure[$cellIndex] == 'countries__id') {
                                $getCountry = Arr::first($countries, function ($value, $key) use (&$cellValue) {
                                    return in_array(strtolower($cellValue), array_map(function ($item) {
                                        return strtolower($item);
                                    }, array_values(Arr::only($value, [
                                        'name',
                                        'iso_code',
                                        'name_capitalized',
                                        'iso3_code',
                                        'phone_code',
                                    ])))) == true;
                                });
                                $contactsToUpdate[$rowIndex][$dataStructure[$cellIndex]] = Arr::get($getCountry, '_id');
                            } else {
                                $contactsToUpdate[$rowIndex][$dataStructure[$cellIndex]] = $cellValue;
                            }
                        }
                    }
                    // store and make memory free
                    if (count($contactsToUpdate) >= $chuckSize) {
                        // check the feature limit - skip if unlimited plan
                        if (!$hasUnlimitedContacts) {
                            $vendorPlanDetails = vendorPlanDetails('contacts', ($vendorAllContactsCount + $newContactsCount), $vendorId);
                            if (!$vendorPlanDetails['is_limit_available']) {
                                return $this->engineResponse(22, null, $vendorPlanDetails['message']);
                            }
                        }
                        $this->contactRepository->bunchInsertOrUpdate($contactsToUpdate, '_uid');
                        $contactsToUpdate = [];
                    }
                }
                // if remaining records
                if (!empty($contactsToUpdate)) {
                    // check the feature limit - skip if unlimited plan
                    if (!$hasUnlimitedContacts) {
                        $vendorPlanDetails = vendorPlanDetails('contacts', ($vendorAllContactsCount + $newContactsCount), $vendorId);
                        if (!$vendorPlanDetails['is_limit_available']) {
                            return $this->engineResponse(22, null, $vendorPlanDetails['message']);
                        }
                    }
                    $this->contactRepository->bunchInsertOrUpdate($contactsToUpdate, '_uid');
                    $contactsToUpdate = [];
                }
                $totalContactsProcessed = $newContactsCount;
                // next procedure to update related to models
                // loop though each row
                foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                    // do stuff with the row
                    $cells = $row->getCells();
                    if ($rowIndex != 1) {
                        $contactsToUpdate[$rowIndex]['vendors__id'] = $vendorId;
                    }
                    // loop through each cell of row
                    foreach ($cells as $cellIndex => $cell) {
                        $cellValue = $cell->getValue();
                        // if its not header row and upto 4 cells its contact basic fields
                        if (($rowIndex != 1) and ($cellIndex <= 5)) {
                            if ($dataStructure[$cellIndex] == 'wa_id') {
                                $contact = $this->contactRepository->with(['groups', 'customFieldValues'])->fetchIt([
                                    'vendors__id' => $vendorId,
                                    'wa_id' => $cellValue,
                                ], [
                                    '_id',
                                    '_uid',
                                    'wa_id'
                                    ])?->toArray() ?: [];
                                // check if contact found
                                if (!__isEmpty($contact)) {
                                    $contactId = Arr::get($contact, '_id');
                                } else {
                                    // collect wa_id which is the phone numbers
                                    $phoneNumbers[] = $cellValue;
                                }
                            }
                        } elseif (($cellIndex == 6) and ($rowIndex != 1)) { // groups
                            // get the group names and explode it from comma separated names
                            $extractedGroups = trim($cellValue) ? explode(',', $cellValue) : [];
                            $contactGroups = collect($contact['groups'] ?? [])->keyBy('_id');
                            // loop through the groups
                            foreach ($extractedGroups as $extractedGroup) {
                                $extractedGroup = Str::limit(trim($extractedGroup), 250, '');
                                // get group id
                                $contactGroupId = Arr::get($vendorContactGroups, $extractedGroup . '._id');
                                if (!$contactGroupId) {
                                    // create new group when needed
                                    if ($newGroupCreate = $this->contactGroupRepository->storeIt([
                                        'title' => $extractedGroup,
                                        'vendors__id' => $vendorId,
                                    ])) {
                                        $vendorContactGroups[$extractedGroup] = $newGroupCreate->toArray();
                                        $contactGroupId = $newGroupCreate->_id;
                                    }
                                }
                                if ($contactId and $contactGroupId and !isset($contactGroups[$contactGroupId])) {
                                    // set it for update
                                    $contactGroupsToUpdate[] = [
                                        'contact_groups__id' => $contactGroupId,
                                        'contacts__id' => $contactId,
                                    ];
                                }
                            }
                        } elseif ($cellIndex >= 7) { // custom field
                            // custom field values
                            if ($rowIndex == 1) {
                                $customFieldStructure[$cellIndex] = $cellValue;
                            } else {
                                // get custom item field data based on column head
                                $customFieldItem = $vendorContactCustomFields[$customFieldStructure[ $cellIndex ?? null ]] ?? null;
                                if ($customFieldItem and $contactId) {
                                    // extract the item from contact db custom field value
                                    $customFieldDbItem = Arr::first($contact['custom_field_values'] ?? [], function ($value, $key) use ($customFieldItem) {
                                        return $value['contact_custom_fields__id'] == Arr::get($customFieldItem, '_id');
                                    });
                                    $customFieldsToUpdate[] = [
                                        // get or set uuid
                                        '_uid' => Arr::get($customFieldDbItem, '_uid') ?: (string) Str::uuid(),
                                        'contact_custom_fields__id' => Arr::get($customFieldItem, '_id'),
                                        'contacts__id' => $contactId,
                                        'field_value' => $cellValue,
                                    ];
                                }
                            }
                        }
                    }

                    // store and make memory free
                    // create or custom field values
                    if (count($customFieldsToUpdate) >= $chuckSize) {
                        $this->contactCustomFieldRepository->storeCustomValues($customFieldsToUpdate, '_uid');
                        $customFieldsToUpdate = [];
                    }
                    // assign groups update
                    if (count($contactGroupsToUpdate) >= $chuckSize) {
                        $this->groupContactRepository->bunchInsertOrUpdate($contactGroupsToUpdate, '_uid');
                        $contactGroupsToUpdate = [];
                    }
                }
                // store and make memory free
                // remaining
                // create or custom field values
                if (!empty($customFieldsToUpdate)) {
                    $this->contactCustomFieldRepository->storeCustomValues($customFieldsToUpdate, '_uid');
                    $customFieldsToUpdate = [];
                }
                // assign groups update
                if (!empty($contactGroupsToUpdate)) {
                    $this->groupContactRepository->bunchInsertOrUpdate($contactGroupsToUpdate, '_uid');
                    $contactGroupsToUpdate = [];
                }
            }
            // close the sheet
            $reader->close();
            
            // PERFORMANCE OPTIMIZATION: Re-enable query logging
            \DB::connection()->enableQueryLog();
            
            // Calculate execution time
            $executionTime = round(microtime(true) - $startTime, 2);
            $recordsPerSecond = $executionTime > 0 ? round($numberOfRows / $executionTime, 2) : 0;
            
            // Final import statistics
            \Log::info("=== IMPORT COMPLETED ===");
            \Log::info("New contacts imported: {$newContactsCount}");
            \Log::info("Total rows processed: {$numberOfRows}");
            \Log::info("Duplicate entries: " . count($duplicateEntries));
            \Log::info("Execution time: {$executionTime} seconds");
            \Log::info("Processing speed: {$recordsPerSecond} records/second");
            
            // create or custom field values
            /* if(!empty($customFieldsToUpdate)) {
                foreach (array_chunk($customFieldsToUpdate, 500) as $customFieldsDataChunk) {
                    $this->contactCustomFieldRepository->storeCustomValues($customFieldsDataChunk, '_uid');
                }
            } */
            // groups update
            /*  if(!empty($contactGroupsToUpdate)) {
                 foreach (array_chunk($contactGroupsToUpdate, 500) as $contactGroupsFieldsDataChunk) {
                     $this->groupContactRepository->bunchInsertOrUpdate($contactGroupsFieldsDataChunk, '_uid');
                 }
             } */
            if (!empty($duplicateEntries)) {
                return $this->engineSuccessResponse([], __tr('Total __totalContactsProcessed__ contacts imported in __executionTime__s (__recordsPerSecond__ records/s). __duplicateEntries__ phone numbers were duplicate or invalid.', [
                    '__totalContactsProcessed__' => $totalContactsProcessed,
                    '__executionTime__' => $executionTime,
                    '__recordsPerSecond__' => $recordsPerSecond,
                    '__duplicateEntries__' => count($duplicateEntries),
                ]));
            }
            return $this->engineSuccessResponse([], __tr('Successfully imported __totalContactsProcessed__ contacts in __executionTime__s (__recordsPerSecond__ records/s)', [
                '__totalContactsProcessed__' => $totalContactsProcessed,
                '__executionTime__' => $executionTime,
                '__recordsPerSecond__' => $recordsPerSecond,
            ]));
        } catch (\Throwable $th) {
            // PERFORMANCE OPTIMIZATION: Re-enable query logging on error
            \DB::connection()->enableQueryLog();
            
            \Log::error("=== IMPORT FAILED ===");
            \Log::error("Error: " . $th->getMessage());
            \Log::error("File: " . $th->getFile());
            \Log::error("Line: " . $th->getLine());
            
            if (config('app.debug')) {
                throw $th;
            }
            return $this->engineFailedResponse([], __tr('Error occurred while importing data, please check and correct data and re-upload.'));
        }
    }

    /**
     * Assign User to Contact for chat
     *
     * @param BaseRequest $request
     * @return EngineResponse
     */
    public function processAssignChatUser($request)
    {
        $vendorId = getVendorId();
        if (!$request->assigned_users_uid or ($request->assigned_users_uid == 'no_one')) {
            if ($this->contactRepository->updateIt([
                '_uid' => $request->contactIdOrUid,
                'vendors__id' => $vendorId,
            ], [
                'assigned_users__id' => null,
            ])) {
                return $this->engineSuccessResponse([], __tr('Unassigned user'));
            }
            return $this->engineFailedResponse([], __tr('Already unsigned'));
        }
        // get all the messaging vendor users
        $vendorMessagingUserUids = $this->userRepository->getVendorMessagingUsers($vendorId)->pluck('_uid')->toArray();
        // validate the vendor user
        if (!in_array($request->assigned_users_uid, $vendorMessagingUserUids)) {
            return $this->engineFailedResponse([], __tr('Invalid user'));
        }
        // get the user details
        $user = $this->userRepository->fetchIt([
            '_uid' => $request->assigned_users_uid,
        ]);
        if (__isEmpty($user)) {
            return $this->engineFailedResponse([], __tr('Failed to assign user'));
        }
        if ($this->contactRepository->updateIt([
            '_uid' => $request->contactIdOrUid,
            'vendors__id' => $vendorId,
        ], [
            'assigned_users__id' => $user->_id,
        ])) {
            return $this->engineSuccessResponse([], __tr('__userFullName__ Assigned', [
                '__userFullName__' => $user->full_name
            ]));
        }
        return $this->engineResponse(14, [], __tr('No changes'));
    }

    /**
     * Assign Groups to selected contacts
     *
     * @param BaseRequest $request
     * @return void
     */
    public function processAssignGroupsToSelectedContacts($request)
    {
        $groups = $this->contactGroupRepository->fetchItAll($request->get('selected_groups'), [], '_id');
        $contacts = $this->contactRepository->with(['groups'])->fetchItAll($request->get('selected_contacts'), [], '_uid');
        $contactGroupsToUpdate = [];
        foreach ($contacts as $contact) {
            $contactGroups = collect($contact['groups'] ?? [])->pluck('_id');
            $newGroupIds = array_diff($groups->pluck('_id')->toArray(), $contactGroups->toArray());
            foreach ($newGroupIds as $newGroupId) {
                $contactGroupsToUpdate[] = [
                    'contact_groups__id' => $newGroupId,
                    'contacts__id' => $contact->_id,
                ];
            }
        }
        if (!empty($contactGroupsToUpdate)) {
            $this->groupContactRepository->bunchInsertOrUpdate($contactGroupsToUpdate, '_uid');
            return $this->engineSuccessResponse([
                'reloadDatatableId' => '#lwContactList',
                'modalId' => '#lwAssignGroups',
            ], __tr('Groups assigned successfully.'));
        }
        return $this->engineResponse(14, [], __tr('No changes'));
    }
    /**
     * Contact notes process update
     *
     * @param  BaseRequest  $request
     * @return EngineResponse
     *---------------------------------------------------------------- */
    public function processUpdateNotes($request)
    {
        $vendorId = getVendorId();
        $contact = $this->contactRepository->fetchIt([
            '_uid' => $request->contactIdOrUid,
            'vendors__id' => $vendorId,
        ]);
        // Check if $contact not exist then throw not found
        // exception
        if (__isEmpty($contact)) {
            return $this->engineResponse(18, null, __tr('Contact not found.'));
        }

        if ($this->contactRepository->updateIt($contact, [
            '__data' => [
                'contact_notes' => $request->contact_notes ?: '',
            ]
        ])) {
            return $this->engineSuccessResponse([], __tr('Notes updated'));
        }
        return $this->engineFailedResponse([], __tr('Notes does not updated'));
    }

    /**
     * Get all the labels
     *
     * @param string $contactUid
     * @return EngineResponse
     */
    public function getLabelsData($contactUid)
    {
        // $this->labelRepository = $labelRepository;
        // $this->contactLabelRepository = $contactLabelRepository;
        $vendorId = getVendorId();
        $listOfAllLabels = $this->labelRepository->fetchItAll([
            'vendors__id' => $vendorId
        ]);
        return$this->engineSuccessResponse([
            'contact_uid' => $contactUid,
            'listOfAllLabels' => $listOfAllLabels
        ]);
    }
    /**
     * Create new label for the vendor
     *
     * @param BaseRequestTwo $request
     * @return EngineResponse
     */
    public function createLabelProcess($request)
    {
        $vendorId = getVendorId();
        if ($createdLabel = $this->labelRepository->storeIt([
            'vendors__id' => $vendorId,
            'title' => $request->title,
            'text_color' => $request->text_color,
            'bg_color' => $request->bg_color,
            'status' => 1,
        ])) {
            // get all the labels
            $allLabels = $this->labelRepository->fetchItAll([
                'vendors__id' => $vendorId
            ]);

            updateClientModels([
                'allLabels' => $allLabels
            ]);

            return$this->engineSuccessResponse([
                'createdLabel' => $createdLabel
            ], __tr('Label created'));
        }
        return$this->engineFailedResponse([], __tr('Failed to create label'));
    }

    /**
     * Assign contact lables
     *
     * @param BaseRequestTwo $request
     * @return EngineResponse
     */
    public function assignContactLabelsProcess($request)
    {
        $vendorId = getVendorId();
        $contact = $this->contactRepository->with('groups')->fetchIt([
            '_uid' => $request->contactUid,
            'vendors__id' => $vendorId,
        ]);
        // Check if $contact not exist then throw not found
        // exception
        if (__isEmpty($contact)) {
            return $this->engineResponse(18, null, __tr('Contact not found.'));
        }
        $inputData = $request->all();
        // extract exiting label ids
        $existingLabelIds = $contact->labels->pluck('_id')->toArray();
        // prepare group ids needs to be assign to the contact
        $labelsToBeAddedIds = array_diff($inputData['contact_labels'] ?? [], $existingLabelIds);
        // prepare group ids needs to be remove from the contact
        $labelsToBeDeleted = array_diff($existingLabelIds, $inputData['contact_labels'] ?? []);
        $isUpdated = false;
        // process to delete if needed
        if (! empty($labelsToBeDeleted)) {
            if ($this->contactLabelRepository->deleteAssignedLabels($labelsToBeDeleted, $contact->_id)) {
                $isUpdated = true;
            }
        }
        // prepare to assign if needed
        if (! empty($labelsToBeAddedIds)) {
            // prepare group ids needs to be assign to the contact
            $labelsToBeAdded = $this->labelRepository->fetchItAll($labelsToBeAddedIds, [], '_id');
            $assignLabels = [];
            foreach ($labelsToBeAdded as $labelToBeAdded) {
                if ($labelToBeAdded->vendors__id != $vendorId) {
                    continue;
                }
                $assignLabels[] = [
                    'labels__id' => $labelToBeAdded->_id,
                    'contacts__id' => $contact->_id,
                ];
            }
            if ($this->contactLabelRepository->storeItAll($assignLabels)) {
                $isUpdated = true;
            }
        }
        if ($isUpdated) {
            return $this->engineSuccessResponse([], __tr('Labels updated'));
        }
        return $this->engineResponse(14, null, __tr('Nothing to update'));
    }

    /**
     * Delete label
     *
     * @param string $labelUid
     * @return EngineResponse
     */
    public function processDeleteLabel($labelUid)
    {
        $vendorId = getVendorId();
        if ($this->labelRepository->deleteIt([
            '_uid' => $labelUid,
            'vendors__id' => $vendorId
        ])) {
            // get all the labels
            $allLabels = $this->labelRepository->fetchItAll([
                'vendors__id' => $vendorId
            ]);

            updateClientModels([
                'allLabels' => $allLabels
            ]);

            return $this->engineSuccessResponse([
                'labelUid' => $labelUid
            ], __tr('Label deleted'));
        }
        return $this->engineResponse(14, null, __tr('nothing deleted'));
    }
    /**
     * Update Label
     *
     * @param BaseRequestTwo $labelUid
     * @return EngineResponse
     */
    public function processUpdateLabel($request)
    {
        $vendorId = getVendorId();
        $labelItem = $this->labelRepository->fetchIt([
            '_uid' => $request->labelUid,
            'vendors__id' => $vendorId,
        ]);
        if (__isEmpty($labelItem)) {
            return $this->engineResponse(2, null, __tr('Invalid label'));
        }
        if ($this->labelRepository->updateIt($labelItem, [
            'title' => $request->title,
            'text_color' => $request->text_color,
            'bg_color' => $request->bg_color,
        ])) {

            // get all the labels
            $allLabels = $this->labelRepository->fetchItAll([
               'vendors__id' => $vendorId
            ]);

            updateClientModels([
                'allLabels' => $allLabels
            ]);

            return $this->engineSuccessResponse([
                'labelUid' => $request->labelUid
            ], __tr('Label updated'));
        }
        return $this->engineResponse(14, null, __tr('nothing updated'));
    }

    /**
     * Update import progress in cache for real-time tracking
     *
     * @param string $importId Unique import identifier
     * @param array $data Progress data to update
     * @return void
     */
    protected function updateImportProgress($importId, $data)
    {
        try {
            // Get existing progress
            $progress = \Cache::get($importId, []);
            
            // Merge new data
            $progress = array_merge($progress, $data);
            
            // Store in cache for 1 hour (import should complete before then)
            \Cache::put($importId, $progress, now()->addHour());
            
        } catch (\Exception $e) {
            \Log::error("Failed to update import progress: " . $e->getMessage());
            // Don't fail the import if progress tracking fails
        }
    }

    /**
     * Get import progress for tracking
     *
     * @param string $importId Unique import identifier
     * @return array Progress data
     */
    public function getImportProgress($importId)
    {
        return \Cache::get($importId, [
            'status' => 'not_found',
            'stage' => 'Import not found or expired',
            'percentage' => 0,
        ]);
    }

    /**
     * Clear import progress from cache
     *
     * @param string $importId Unique import identifier
     * @return void
     */
    protected function clearImportProgress($importId)
    {
        try {
            \Cache::forget($importId);
        } catch (\Exception $e) {
            \Log::error("Failed to clear import progress: " . $e->getMessage());
        }
    }

    /**
     * Fetch contact IDs from database and update the mapping
     * This is called immediately after each batch insert to ensure IDs are available
     *
     * @param int $vendorId Vendor ID
     * @param array $phoneNumbers Array of phone numbers to fetch IDs for
     * @param array &$contactUidByPhone Reference to the mapping array
     * @return void
     */
    protected function fetchAndUpdateContactIds($vendorId, $phoneNumbers, &$contactUidByPhone)
    {
        if (empty($phoneNumbers)) {
            return;
        }
        
        try {
            // Give database a moment to commit (especially important for large batches)
            usleep(100000); // 100ms delay
            
            // Fetch IDs for this batch
            $contactIds = \DB::table('contacts')
                ->where('vendors__id', $vendorId)
                ->whereIn('wa_id', $phoneNumbers)
                ->select('_id', 'wa_id')
                ->get();
            
            // Update the mapping
            $mappedCount = 0;
            foreach ($contactIds as $contact) {
                if (isset($contactUidByPhone[$contact->wa_id])) {
                    $contactUidByPhone[$contact->wa_id]['_id'] = $contact->_id;
                    $mappedCount++;
                }
            }
            
            \Log::info("Batch: Mapped {$mappedCount} IDs out of " . count($phoneNumbers) . " phones");
            
        } catch (\Exception $e) {
            \Log::error("Failed to fetch contact IDs for batch: " . $e->getMessage());
        }
    }
    
    /**
     * Fetch contact IDs by UUID (for new import logic that creates contact for every row)
     */
    protected function fetchAndUpdateContactIdsByUuid($vendorId, $uuids, &$contactUidByPhone)
    {
        if (empty($uuids)) {
            return;
        }
        
        try {
            // Give database a moment to commit (especially important for large batches)
            usleep(100000); // 100ms delay
            
            // Fetch IDs for this batch using UUIDs
            $contactIds = \DB::table('contacts')
                ->where('vendors__id', $vendorId)
                ->whereIn('_uid', $uuids)
                ->select('_id', '_uid')
                ->get();
            
            // Update the mapping - iterate through all entries to find matching UUIDs
            $mappedCount = 0;
            foreach ($contactIds as $contact) {
                // Find the entry in contactUidByPhone with matching UUID
                foreach ($contactUidByPhone as $key => &$data) {
                    if (isset($data['_uid']) && $data['_uid'] === $contact->_uid) {
                        $data['_id'] = $contact->_id;
                        $mappedCount++;
                        break;
                    }
                }
            }
            
            \Log::info("Batch: Mapped {$mappedCount} IDs out of " . count($uuids) . " UUIDs");
            
        } catch (\Exception $e) {
            \Log::error("Failed to fetch contact IDs by UUID for batch: " . $e->getMessage());
        }
    }
}
