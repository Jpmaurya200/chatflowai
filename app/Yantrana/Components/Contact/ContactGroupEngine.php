<?php
/**
* ContactGroupEngine.php - Main component file
*
* This file is part of the Contact component.
*-----------------------------------------------------------------------------*/

namespace App\Yantrana\Components\Contact;

use App\Yantrana\Base\BaseEngine;
use App\Yantrana\Components\Contact\Interfaces\ContactGroupEngineInterface;
use App\Yantrana\Components\Contact\Repositories\ContactGroupRepository;

class ContactGroupEngine extends BaseEngine implements ContactGroupEngineInterface
{
    /**
     * @var ContactGroupRepository - ContactGroup Repository
     */
    protected $contactGroupRepository;

    /**
     * Constructor
     *
     * @param  ContactGroupRepository  $contactGroupRepository  - ContactGroup Repository
     * @return void
     *-----------------------------------------------------------------------*/
    public function __construct(ContactGroupRepository $contactGroupRepository)
    {
        $this->contactGroupRepository = $contactGroupRepository;
    }

    /**
     * Group datatable source
     *
     * @return array
     *---------------------------------------------------------------- */
    public function prepareGroupDataTableSource($status)
    {
        $groupCollection = $this->contactGroupRepository->fetchGroupDataTableSource($status);
        // required columns for DataTables
        $requireColumns = [
            '_id',
            '_uid',
            'title',
            'description',
            'status',
        ];

        // prepare data for the DataTables
        return $this->dataTableResponse($groupCollection, $requireColumns);
    }

    /**
     * Group delete process
     *
     * @param  mix  $contactGroupIdOrUid
     * @return array
     *---------------------------------------------------------------- */
    public function processGroupDelete($contactGroupIdOrUid)
    {
        // fetch the record
        $group = $this->contactGroupRepository->fetchIt($contactGroupIdOrUid);
        // check if the record found
        if (__isEmpty($group)) {
            // if not found
            return $this->engineResponse(18, null, __tr('Group not found'));
        }
        
        \Log::info("=== SMART GROUP DELETE STARTED ===");
        \Log::info("Group: {$group->title} (ID: {$group->_id})");
        
        // STEP 1: Find all contacts in this group
        $contactsInThisGroup = \DB::table('group_contacts')
            ->where('contact_groups__id', $group->_id)
            ->pluck('contacts__id')
            ->toArray();
        
        \Log::info("Total contacts in group: " . count($contactsInThisGroup));
        
        // STEP 2: Identify which contacts are ONLY in this group
        $contactsToDelete = [];
        $contactsToKeep = [];
        
        foreach ($contactsInThisGroup as $contactId) {
            // Count how many groups this contact is in
            $groupCount = \DB::table('group_contacts')
                ->where('contacts__id', $contactId)
                ->count();
            
            if ($groupCount == 1) {
                // Contact is ONLY in this group → will be deleted from contacts table
                $contactsToDelete[] = $contactId;
            } else {
                // Contact is in multiple groups → will be kept in contacts table
                $contactsToKeep[] = $contactId;
            }
        }
        
        \Log::info("Contacts ONLY in this group (will be deleted): " . count($contactsToDelete));
        \Log::info("Contacts in other groups (will be kept): " . count($contactsToKeep));
        
        try {
            // STEP 3: Delete all group_contacts relationships for this group
            \DB::table('group_contacts')
                ->where('contact_groups__id', $group->_id)
                ->delete();
            
            \Log::info("Removed all group relationships");
            
            // STEP 4: Delete contacts that were ONLY in this group
            if (!empty($contactsToDelete)) {
                $deletedCount = \DB::table('contacts')
                    ->whereIn('_id', $contactsToDelete)
                    ->delete();
                
                \Log::info("Deleted {$deletedCount} contacts from contacts table");
            }
            
            // STEP 5: Delete the group itself
            if ($this->contactGroupRepository->deleteIt($group)) {
                \Log::info("=== SMART GROUP DELETE COMPLETED ===");
                
                // Build success message
                $message = __tr('Group deleted successfully.');
                
                if (count($contactsToDelete) > 0) {
                    $message .= ' ' . __tr('__count__ contacts that were only in this group were also deleted from the system.', [
                        '__count__' => count($contactsToDelete)
                    ]);
                }
                
                if (count($contactsToKeep) > 0) {
                    $message .= ' ' . __tr('__count__ contacts remain in the system as they are in other groups.', [
                        '__count__' => count($contactsToKeep)
                    ]);
                }
                
                return $this->engineSuccessResponse([
                    'deleted_contacts' => count($contactsToDelete),
                    'kept_contacts' => count($contactsToKeep),
                ], $message);
            }
            
        } catch (\Exception $e) {
            \Log::error("=== SMART GROUP DELETE FAILED ===");
            \Log::error("Error: " . $e->getMessage());
            return $this->engineFailedResponse([], __tr('Failed to delete Group'));
        }

        // if failed to delete
        return $this->engineFailedResponse([], __tr('Failed to delete Group'));
    }

    /**
     * Get group delete preview - shows what will be deleted
     *
     * @param  mix  $contactGroupIdOrUid
     * @return array
     */
    public function getGroupDeletePreview($contactGroupIdOrUid)
    {
        // fetch the record
        $group = $this->contactGroupRepository->fetchIt($contactGroupIdOrUid);
        
        if (__isEmpty($group)) {
            return $this->engineResponse(18, null, __tr('Group not found'));
        }
        
        // Find all contacts in this group
        $contactsInThisGroup = \DB::table('group_contacts')
            ->where('contact_groups__id', $group->_id)
            ->pluck('contacts__id')
            ->toArray();
        
        // Identify exclusive contacts (only in this group)
        $exclusiveContacts = 0;
        $sharedContacts = 0;
        
        foreach ($contactsInThisGroup as $contactId) {
            $groupCount = \DB::table('group_contacts')
                ->where('contacts__id', $contactId)
                ->count();
            
            if ($groupCount == 1) {
                $exclusiveContacts++;
            } else {
                $sharedContacts++;
            }
        }
        
        return $this->engineSuccessResponse([
            'group_name' => $group->title,
            'total_contacts' => count($contactsInThisGroup),
            'exclusive_contacts' => $exclusiveContacts,  // Will be DELETED from contacts
            'shared_contacts' => $sharedContacts,        // Will be KEPT in contacts
            'has_exclusive' => $exclusiveContacts > 0,
        ]);
    }
    
    /**
     * Group archive process
     *
     * @param  mix  $contactGroupIdOrUid
     * @return array
     *---------------------------------------------------------------- */
    public function processGroupArchive($contactGroupIdOrUid)
    {
        // fetch the record
        $group = $this->contactGroupRepository->fetchIt($contactGroupIdOrUid);
        // check if the record found
        if (__isEmpty($group)) {
            // if not found
            return $this->engineResponse(18, null, __tr('Group not found'));
        }
         // Prepare Update Package data
         $updateData = [
            'status' => 5,
        ];
        //Check if package archive
        if ($this->contactGroupRepository->updateIt($group,$updateData)) {
            return $this->engineSuccessResponse([], __tr('Group Archived successfully'));
        }

        // if failed to archive
        return $this->engineFailedResponse([], __tr('Failed to Archive Group'));
    }
 /**
     * Group archive process
     *
     * @param  mix  $contactGroupIdOrUid
     * @return array
     *---------------------------------------------------------------- */
    public function processGroupUnarchive($contactGroupIdOrUid)
    {
        // fetch the record
        $group = $this->contactGroupRepository->fetchIt($contactGroupIdOrUid);
        // check if the record found
        if (__isEmpty($group)) {
            // if not found
            return $this->engineResponse(18, null, __tr('Group not found'));
        }
         // Prepare Update Package data
         $updateData = [
            'status' => 1,
        ];
        //Check if package unarchive
        if ($this->contactGroupRepository->updateIt($group,$updateData)) {
            return $this->engineSuccessResponse([], __tr('Group Unarchived successfully'));
        }

        // if failed to unarchive
        return $this->engineFailedResponse([], __tr('Failed to Unarchive Group'));
    }

    /**
     * Group create
     *
     * @param  array  $inputData
     * @return array
     *---------------------------------------------------------------- */
    public function processGroupCreate($inputData)
    {
        // ask to add record
        if ($this->contactGroupRepository
            ->storeGroup($inputData)) {

            return $this->engineSuccessResponse([], __tr('Group added.'));
        }

        return $this->engineFailedResponse([], __tr('Group not added.'));
    }

    /**
     * Group prepare update data
     *
     * @param  mix  $contactGroupIdOrUid
     * @return array
     *---------------------------------------------------------------- */
    public function prepareGroupUpdateData($contactGroupIdOrUid)
    {
        $group = $this->contactGroupRepository->fetchIt($contactGroupIdOrUid);

        // Check if $group not exist then throw not found
        // exception
        if (__isEmpty($group)) {
            return $this->engineResponse(18, null, __tr('Group not found.'));
        }

        return $this->engineSuccessResponse($group->toArray());
    }

    /**
     * Group process update
     *
     * @param  mixed  $contactGroupIdOrUid
     * @param  array  $inputData
     * @return array
     *---------------------------------------------------------------- */
    public function processGroupUpdate($contactGroupIdOrUid, $inputData)
    {
        $group = $this->contactGroupRepository->fetchIt($contactGroupIdOrUid);

        // Check if $group not exist then throw not found
        // exception
        if (__isEmpty($group)) {
            return $this->engineResponse(18, null, __tr('Group not found.'));
        }

        $updateData = [
            'title' => $inputData['title'],
            'description' => $inputData['description'],

        ];

        // Check if Group updated
        if ($this->contactGroupRepository->updateIt($group, $updateData)) {

            return $this->engineSuccessResponse([], __tr('Group updated.'));
        }

        return $this->engineResponse(14, null, __tr('Group not updated.'));
    }
     /**
     * Contact group delete process
     *
     * @param  BaseRequest  $request
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processSelectedContactGroupsDelete($request)
    {
        $selectedContactGroupsUids = $request->get('selected_groups');

        $message = '';

        if(empty($selectedContactGroupsUids)) {
            return $this->engineFailedResponse([], __tr('Nothing to delete'));
        }
        // ask to delete the record
        if ($this->contactGroupRepository->deleteSelectedContactGroups($selectedContactGroupsUids)) {
            // if successful
            return $this->engineSuccessResponse([
                'reloadDatatableId' => '#lwGroupList'
            ], __tr('Groups deleted successfully.') . $message);
        }
        // if failed to delete
        return $this->engineFailedResponse([], __tr('Failed to delete Groups'));
    }

     /**
     * Contact group archive process
     *
     * @param  BaseRequest  $request
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processSelectedContactGroupsArchive($request)
    {
        $selectedContactGroupsUids = $request->get('selected_groups');
        $contactGroups = $this->contactGroupRepository->fetchItAll($request->get('selected_groups'), [], '_uid');

        $message = '';
        if(empty($selectedContactGroupsUids)) {
            return $this->engineFailedResponse([], __tr('Nothing to archive'));
        }
        $contactGroupsToUpdate = [];
        // Prepare Update Package data
        foreach ($contactGroups as $newGroup) {
            $contactGroupsToUpdate[] = [
                '_uid' => $newGroup['_uid'],
                'title'=> $newGroup['title'],
                'status' => 5,
            ];
        }
        //process to archived groups
        if(!empty($contactGroupsToUpdate)) {
            $this->contactGroupRepository->bunchInsertOrUpdate($contactGroupsToUpdate, '_uid');
            return $this->engineSuccessResponse([
                'reloadDatatableId' => '#lwGroupList'
            ], __tr('Groups archived successfully.'). $message);
        }
        // if failed to delete
        return $this->engineFailedResponse([], __tr('Failed to archive Groups'));
    }
    /**
     * Contact group unarchive process
     *
     * @param  BaseRequest  $request
     *
     * @return array
     *---------------------------------------------------------------- */
    public function processSelectedContactGroupsUnarchive($request)
    {
        $selectedContactGroupsUids = $request->get('selected_groups');
        $contactGroups = $this->contactGroupRepository->fetchItAll($request->get('selected_groups'), [], '_uid');
        $message = '';
        if(empty($selectedContactGroupsUids)) {
            return $this->engineFailedResponse([], __tr('Nothing to unarchive'));
        }
        $contactGroupsToUpdate = [];
       // Prepare Update Package data
       foreach ($contactGroups as $newGroup) {
        $contactGroupsToUpdate[] = [
            '_uid' => $newGroup['_uid'],
            'title'=> $newGroup['title'],
            'status' => 1,
        ];
    }
         
        //process to archived groups
        if(!empty($contactGroupsToUpdate)) {
            $this->contactGroupRepository->bunchInsertOrUpdate($contactGroupsToUpdate, '_uid');
            return $this->engineSuccessResponse([
                'reloadDatatableId' => '#lwGroupList'
            ], __tr('Groups unarchived successfully.'). $message);
        }
        // if failed to delete
        return $this->engineFailedResponse([], __tr('Failed to unarchive Groups'));
    }
}
